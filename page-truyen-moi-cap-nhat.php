<?php
/**
 * Template Name: Truyện Mới Cập Nhật
 * 
 * 
 * @package TruyenQQ
 * @version 1.0.1
 */

get_header();

require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$posts_per_page = 42;

$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$country = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';

$args = array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'orderby' => 'modified',
    'order' => 'DESC'
);

if ($status !== '') {
    $args['meta_query'] = array(
        array(
            'key' => '_nettruyen_status',
            'value' => $status,
            'compare' => '='
        )
    );
}

if ($country !== '') {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'nettruyen_country',
            'field' => 'slug',
            'terms' => $country
        )
    );
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

$total_pages = $comics_query->max_num_pages;
$current_page = max(1, $paged);
?>

<div id="main_homepage" data-ajax-enabled="true">
    <!-- Section Header -->
    <div class="homepage_tags">
        <h1>
            <p class="text_list_update">
                <i class="fa fa-font-awesome" aria-hidden="true"></i> Truyện Mới Cập Nhật
            </p>
        </h1>
        <div class="clear"></div>
    </div>

    <!-- Filter Box -->
    <div class="story-list-bl01 box">
        <table>
            <tbody>
                <tr>
                    <th>Tình trạng</th>
                    <td>
                        <ul class="choose">
                            <li>
                                <a class="<?php echo ($status === '') ? 'active' : ''; ?>"
                                    href="<?php echo get_permalink(); ?>">
                                    Tất cả
                                </a>
                            </li>
                            <li>
                                <a class="<?php echo ($status === 'ongoing') ? 'active' : ''; ?>"
                                    href="<?php echo add_query_arg(array('status' => 'ongoing', 'country' => $country)); ?>">
                                    Đang tiến hành
                                </a>
                            </li>
                            <li>
                                <a class="<?php echo ($status === 'completed') ? 'active' : ''; ?>"
                                    href="<?php echo add_query_arg(array('status' => 'completed', 'country' => $country)); ?>">
                                    Hoàn thành
                                </a>
                            </li>
                            <li>
                                <a class="<?php echo ($status === 'coming_soon') ? 'active' : ''; ?>"
                                    href="<?php echo add_query_arg(array('status' => 'coming_soon', 'country' => $country)); ?>">
                                    Sắp ra mắt
                                </a>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>Quốc gia</th>
                    <td>
                        <ul class="choose">
                            <li>
                                <a class="<?php echo ($country === '') ? 'active' : ''; ?>"
                                    href="<?php echo get_permalink(); ?>">
                                    Tất cả
                                </a>
                            </li>
                            <li>
                                <a class="<?php echo ($country === 'China') ? 'active' : ''; ?>"
                                    title="Truyện Trung Quốc"
                                    href="<?php echo add_query_arg(array('status' => $status, 'country' => 'China')); ?>">
                                    Trung Quốc
                                </a>
                            </li>
                            <li>
                                <a class="<?php echo ($country === 'Vietnam') ? 'active' : ''; ?>"
                                    title="Truyện Việt Nam"
                                    href="<?php echo add_query_arg(array('status' => $status, 'country' => 'Vietnam')); ?>">
                                    Việt Nam
                                </a>
                            </li>
                            <li>
                                <a class="<?php echo ($country === 'Korea') ? 'active' : ''; ?>" title="Truyện Hàn Quốc"
                                    href="<?php echo add_query_arg(array('status' => $status, 'country' => 'Korea')); ?>">
                                    Hàn Quốc
                                </a>
                            </li>
                            <li>
                                <a class="<?php echo ($country === 'Japan') ? 'active' : ''; ?>" title="Truyện Nhật Bản"
                                    href="<?php echo add_query_arg(array('status' => $status, 'country' => 'Japan')); ?>">
                                    Nhật Bản
                                </a>
                            </li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
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

                    // Lấy thời gian cập nhật
                    $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_the_modified_date('Y-m-d H:i:s');

                    // Hiển thị time ago bằng tiếng Việt
                    $time_ago = truyenqq_time_ago_vietnamese($updated_at);

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
        <a href="<?php echo get_pagenum_link($current_page - 1); ?>">
            <p><span aria-hidden="true">‹</span></p>
        </a>
        <?php endif; ?>

        <?php
            $range = 2;
            $start = max(1, $current_page - $range);
            $end = min($total_pages, $current_page + $range);

            if ($start > 1):
                ?>
        <a href="<?php echo get_pagenum_link(1); ?>">
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
        <a href="<?php echo get_pagenum_link($i); ?>">
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
        <a href="<?php echo get_pagenum_link($total_pages); ?>">
            <p><?php echo $total_pages; ?></p>
        </a>
        <?php endif; ?>

        <?php
            if ($current_page < $total_pages):
                ?>
        <a href="<?php echo get_pagenum_link($current_page + 1); ?>">
            <p><span aria-hidden="true">›</span></p>
        </a>
        <a href="<?php echo get_pagenum_link($total_pages); ?>">
            <p><span aria-hidden="true">»</span></p>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php get_footer(); ?>