<?php
/**
 * Template Name: Tìm Kiếm Nâng Cao
 * 
 * Advanced search page with genre filters, status, country, etc.
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

get_header();

require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$posts_per_page = 42;

$genres_include = isset($_GET['genres']) ? array_map('intval', explode(',', $_GET['genres'])) : array();
$genres_exclude = isset($_GET['exclude']) ? array_map('intval', explode(',', $_GET['exclude'])) : array();
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$country = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
$minchapter = isset($_GET['minchapter']) ? absint($_GET['minchapter']) : 0;
$sort = isset($_GET['sort']) ? absint($_GET['sort']) : 2;

$sort_options = array(
    0 => array('orderby' => 'date', 'order' => 'DESC'),
    1 => array('orderby' => 'date', 'order' => 'ASC'),
    2 => array('orderby' => 'modified', 'order' => 'DESC'),
    3 => array('orderby' => 'modified', 'order' => 'ASC'),
    4 => array('orderby' => 'meta_value_num', 'order' => 'DESC'),
    5 => array('orderby' => 'meta_value_num', 'order' => 'ASC'),
);

$sort_config = isset($sort_options[$sort]) ? $sort_options[$sort] : $sort_options[2];

$args = array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'orderby' => $sort_config['orderby'],
    'order' => $sort_config['order']
);

$tax_query = array('relation' => 'AND');

if (!empty($genres_include)) {
    $tax_query[] = array(
        'taxonomy' => 'nettruyen_genre',
        'field' => 'term_id',
        'terms' => $genres_include,
        'operator' => 'AND'
    );
}

if (!empty($genres_exclude)) {
    $tax_query[] = array(
        'taxonomy' => 'nettruyen_genre',
        'field' => 'term_id',
        'terms' => $genres_exclude,
        'operator' => 'NOT IN'
    );
}

if ($country !== '' && $country !== '0') {
    $tax_query[] = array(
        'taxonomy' => 'nettruyen_country',
        'field' => 'slug',
        'terms' => $country
    );
}

if (count($tax_query) > 1) {
    $args['tax_query'] = $tax_query;
}

$meta_query = array();

if ($status !== '' && $status !== '-1') {
    $meta_query[] = array(
        'key' => '_nettruyen_status',
        'value' => $status,
        'compare' => '='
    );
}

if ($minchapter > 0) {
    $meta_query[] = array(
        'key' => '_nettruyen_chapter_count',
        'value' => $minchapter,
        'compare' => '>=',
        'type' => 'NUMERIC'
    );
}

if (!empty($meta_query)) {
    $args['meta_query'] = $meta_query;
}

if ($sort == 4 || $sort == 5) {
    $args['meta_key'] = '_nettruyen_view_count';
}

$comics_query = new WP_Query($args);

global $wpdb;
$stats_table = $wpdb->prefix . 'nettruyen_view_stats';
$hot_comic_ids = $wpdb->get_col(
    "SELECT post_id 
    FROM {$stats_table} 
    WHERE total_display_views > 0 
    ORDER BY total_display_views DESC 
    LIMIT 20"
);

$all_genres = get_terms(array(
    'taxonomy' => 'nettruyen_genre',
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
));

$all_countries = get_terms(array(
    'taxonomy' => 'nettruyen_country',
    'hide_empty' => true,
    'orderby' => 'name',
    'order' => 'ASC'
));

$total_pages = $comics_query->max_num_pages;
$current_page = max(1, $paged);
?>

<div id="main_homepage" data-ajax-enabled="true">
    <!-- Section Header -->
    <div class="homepage_tags">
        <h1>
            <p class="text_list_update">
                <i class="fa fa-font-awesome" aria-hidden="true"></i> Tìm kiếm nâng cao
            </p>
        </h1>
        <div class="clear"></div>
    </div>

    <!-- Filter Box -->
    <div class="story-list-bl01 box">
        <!-- Toggle Button -->
        <div class="text-center">
            <button type="button" class="btn btn-info btn-collapse">
                <span class="show-text hidden">Hiện </span>
                <span class="hide-text">Ẩn </span>khung tìm kiếm
            </button>
        </div>

        <!-- Advanced Search Form -->
        <div class="advsearch-form">
            <!-- Legend -->
            <div class="form-group clearfix">
                <p><span class="icon-tick"></span> Tìm trong những thể loại này</p>
                <p><span class="icon-cross"></span> Loại trừ những thể loại này</p>
                <p><span class="icon-checkbox"></span> Truyện có thể thuộc hoặc không thuộc thể loại này</p>
            </div>

            <!-- Reset Button -->
            <div class="form-group row text-center">
                <a class="btn btn-primary btn-sm btn-reset" href="<?php echo get_permalink(); ?>">
                    <i class="fa fa-repeat"></i> Reset
                </a>
            </div>

            <!-- Genre Selection -->
            <div class="form-group row">
                <div class="label-search">Thể loại truyện</div>
                <div class="genre-container">
                    <?php foreach ($all_genres as $genre): ?>
                    <div class="genre-item" title="<?php echo esc_attr($genre->description); ?>">
                        <span class="icon-checkbox" data-id="<?php echo $genre->term_id; ?>">
                        </span><?php echo esc_html($genre->name); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Country & Status Dropdowns -->
            <div class="form-group row">
                <div class="label-search">Quốc gia</div>
                <div class="select select-search is-warning">
                    <select class="custom-select" id="country">
                        <option value="">Tất cả</option>
                        <?php foreach ($all_countries as $country_term): ?>
                        <option value="<?php echo esc_attr($country_term->slug); ?>"
                            <?php selected($country, $country_term->slug); ?>>
                            <?php echo esc_html($country_term->name); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="label-search">Tình Trạng</div>
                <div class="select select-search is-warning">
                    <select class="custom-select" id="status">
                        <option value="">Tất cả</option>
                        <option value="ongoing" <?php selected($status, 'ongoing'); ?>>Đang tiến hành</option>
                        <option value="completed" <?php selected($status, 'completed'); ?>>Hoàn thành</option>
                        <option value="coming_soon" <?php selected($status, 'coming_soon'); ?>>Sắp ra mắt</option>
                    </select>
                </div>
            </div>

            <!-- Min Chapter & Sort Dropdowns -->
            <div class="form-group row">
                <div class="label-search">Số lượng chương</div>
                <div class="select select-search is-warning">
                    <select class="custom-select" id="minchapter">
                        <option value="0" <?php selected($minchapter, 0); ?>>&gt; 0</option>
                        <option value="50" <?php selected($minchapter, 50); ?>>&gt;= 50</option>
                        <option value="100" <?php selected($minchapter, 100); ?>>&gt;= 100</option>
                        <option value="200" <?php selected($minchapter, 200); ?>>&gt;= 200</option>
                        <option value="300" <?php selected($minchapter, 300); ?>>&gt;= 300</option>
                        <option value="400" <?php selected($minchapter, 400); ?>>&gt;= 400</option>
                        <option value="500" <?php selected($minchapter, 500); ?>>&gt;= 500</option>
                    </select>
                </div>

                <div class="label-search">Sắp xếp</div>
                <div class="select select-search is-warning">
                    <select class="custom-select" id="sort">
                        <option value="0" <?php selected($sort, 0); ?>>Ngày đăng giảm dần</option>
                        <option value="1" <?php selected($sort, 1); ?>>Ngày đăng tăng dần</option>
                        <option value="2" <?php selected($sort, 2); ?>>Ngày cập nhật giảm dần</option>
                        <option value="3" <?php selected($sort, 3); ?>>Ngày cập nhật tăng dần</option>
                        <option value="4" <?php selected($sort, 4); ?>>Lượt xem giảm dần</option>
                        <option value="5" <?php selected($sort, 5); ?>>Lượt xem tăng dần</option>
                    </select>
                </div>
            </div>

            <!-- Search Button -->
            <div class="form-group clearfix">
                <div class="text-center">
                    <button type="button" class="btn btn-success btn-search is-danger">Tìm kiếm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Comics Grid -->
    <div class="list_grid_out">
        <ul class="list_grid grid">
            <?php
            if ($comics_query->have_posts()):
                while ($comics_query->have_posts()):
                    $comics_query->the_post();
                    $post_id = get_the_ID();

                                        $thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);
                    if (empty($thumbnail)) {
                        $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
                    }
                    if (empty($thumbnail)) {
                        $thumbnail = 'https://via.placeholder.com/190x247?text=No+Image';
                    }

                                        $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
                    $manifest = !empty($manifest_json) ? json_decode($manifest_json, true) : null;

                                        $latest_chapter = 'Đang cập nhật';
                    if (!empty($manifest['chapters'])) {
                        $chapters = $manifest['chapters'];
                        $latest = end($chapters);
                        $latest_chapter = 'Chapter ' . $latest['name'];
                    }

                                        $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_the_modified_time('U');
                    $time_ago = human_time_diff(strtotime($updated_at), current_time('timestamp')) . ' trước';

                                        $follow_count = get_post_meta($post_id, '_nettruyen_follow_count', true);
                    if (empty($follow_count)) {
                        $follow_count = 0;
                    }

                    $view_count = 0;
                    $view_stats = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT total_display_views FROM {$stats_table} WHERE post_id = %d",
                            $post_id
                        )
                    );
                    if ($view_stats) {
                        $view_count = $view_stats->total_display_views;
                    }

                                        $follow_count_formatted = number_format($follow_count);
                    $view_count_formatted = number_format($view_count);

                                        $is_hot = in_array($post_id, $hot_comic_ids);
                    $is_new = (current_time('timestamp') - strtotime($updated_at)) <= (7 * 24 * 60 * 60);

                    $badge_type = '';
                    $badge_text = '';
                    if ($is_hot) {
                        $badge_type = 'hot';
                        $badge_text = 'Hot';
                    } elseif ($is_new) {
                        $badge_type = 'new';
                        $badge_text = 'New';
                    }
                    ?>

            <li>
                <div class="book_avatar">
                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                        <img class="center" src="<?php echo esc_url($thumbnail); ?>"
                            alt="<?php the_title_attribute(); ?>" loading="lazy">
                    </a>

                    <!-- ✅ FIXED: Sử dụng global bookmark badge -->
                    <?php truyenqq_render_bookmark_badge($post_id); ?>

                    <div class="top-notice">
                        <span class="time-ago"><?php echo esc_html($time_ago); ?></span>
                        <?php if ($badge_type): ?>
                        <span class="type-label <?php echo esc_attr($badge_type); ?>">
                            <?php echo esc_html($badge_text); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="book_info">
                    <div class="book_name">
                        <h3>
                            <a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h3>
                    </div>
                    <div class="clear"></div>

                    <div class="text_detail">
                        <span>
                            <i class="fa fa-bookmark"></i> <?php echo esc_html($follow_count_formatted); ?>
                        </span>
                        <span>
                            <i class="fa fa-eye"></i> <?php echo esc_html($view_count_formatted); ?>
                        </span>
                    </div>

                    <div class="last_chapter">
                        <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr($latest_chapter); ?>">
                            <?php echo esc_html($latest_chapter); ?>
                        </a>
                    </div>
                </div>

                <div class="clear"></div>
            </li>

            <?php
                endwhile;
                wp_reset_postdata();
            else:
                echo '<li class="no-results"><p>Không tìm thấy truyện nào.</p></li>';
            endif;
            ?>
        </ul>
    </div>

    <div class="clear"></div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="page_redirect">
        <?php
                        if ($current_page > 1):
                ?>
        <a href="javascript:void(0)" data-page="<?php echo $current_page - 1; ?>">
            <p><span aria-hidden="true">‹</span></p>
        </a>
        <?php endif; ?>

        <?php
                        $range = 2;
            $start = max(1, $current_page - $range);
            $end = min($total_pages, $current_page + $range);

                        if ($start > 1):
                ?>
        <a href="javascript:void(0)" data-page="1">
            <p>1</p>
        </a>
        <?php if ($start > 2): ?>
        <span class="dots">...</span>
        <?php endif; ?>
        <?php endif; ?>

        <?php
                        for ($i = $start; $i <= $end; $i++):
                if ($i == $current_page):
                    ?>
        <a href="javascript:void(0)">
            <p class="active"><?php echo $i; ?></p>
        </a>
        <?php else: ?>
        <a href="javascript:void(0)" data-page="<?php echo $i; ?>">
            <p><?php echo $i; ?></p>
        </a>
        <?php
                endif;
            endfor;
            ?>

        <?php
                        if ($end < $total_pages):
                if ($end < $total_pages - 1):
                    ?>
        <span class="dots">...</span>
        <?php endif; ?>
        <a href="javascript:void(0)" data-page="<?php echo $total_pages; ?>">
            <p><?php echo $total_pages; ?></p>
        </a>
        <?php endif; ?>

        <?php
                        if ($current_page < $total_pages):
                ?>
        <a href="javascript:void(0)" data-page="<?php echo $current_page + 1; ?>">
            <p><span aria-hidden="true">›</span></p>
        </a>
        <a href="javascript:void(0)" data-page="<?php echo $total_pages; ?>">
            <p><span aria-hidden="true">»</span></p>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php get_footer(); ?>