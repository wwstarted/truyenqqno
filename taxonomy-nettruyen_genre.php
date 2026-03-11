<?php
/**
 * Template Name: Truyện Theo Thể Loại
 * Template for nettruyen_genre taxonomy
 *
 * @package TruyenQQ
 * @version 1.1.2 - FIX: og_image không còn tiêu thụ post đầu tiên
 */

require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

$current_genre     = get_queried_object();
$genre_slug        = $current_genre->slug;
$genre_name        = $current_genre->name;
$genre_description = $current_genre->description;

$paged          = (get_query_var('paged')) ? get_query_var('paged') : 1;
$posts_per_page = 42;

$status  = isset($_GET['status'])  ? sanitize_text_field($_GET['status'])  : '';
$country = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
$sort    = isset($_GET['sort'])     ? absint($_GET['sort'])                 : 2;

$sort_options = array(
    0 => array('orderby' => 'date',           'order' => 'DESC'),
    1 => array('orderby' => 'date',           'order' => 'ASC'),
    2 => array('orderby' => 'modified',       'order' => 'DESC'),
    3 => array('orderby' => 'modified',       'order' => 'ASC'),
    4 => array('orderby' => 'meta_value_num', 'order' => 'DESC'),
    5 => array('orderby' => 'meta_value_num', 'order' => 'ASC'),
);

$sort_config = isset($sort_options[$sort]) ? $sort_options[$sort] : $sort_options[2];

$args = array(
    'post_type'      => 'nettruyen_comic',
    'post_status'    => 'publish',
    'posts_per_page' => $posts_per_page,
    'paged'          => $paged,
    'tax_query'      => array(
        array(
            'taxonomy' => 'nettruyen_genre',
            'field'    => 'slug',
            'terms'    => $genre_slug,
        ),
    ),
    'orderby' => $sort_config['orderby'],
    'order'   => $sort_config['order'],
);

if ($status !== '') {
    $args['meta_query'][] = array(
        'key'     => '_nettruyen_status',
        'value'   => $status,
        'compare' => '=',
    );
}

if ($country !== '') {
    $args['tax_query'][] = array(
        'taxonomy' => 'nettruyen_country',
        'field'    => 'slug',
        'terms'    => $country,
    );
}

if ($sort == 4 || $sort == 5) {
    $args['meta_key'] = '_nettruyen_view_count';
}

$comics_query = new WP_Query($args);

global $wpdb;
$stats_table   = $wpdb->prefix . 'nettruyen_view_stats';
$hot_comic_ids = $wpdb->get_col(
    "SELECT post_id
     FROM {$stats_table}
     WHERE total_display_views > 0
     ORDER BY total_display_views DESC
     LIMIT 20"
);

$all_genres = get_terms(array(
    'taxonomy'   => 'nettruyen_genre',
    'hide_empty' => true,
    'orderby'    => 'name',
    'order'      => 'ASC',
));

$total_pages  = $comics_query->max_num_pages;
$current_page = max(1, $paged);
$genre_url    = get_term_link($current_genre);

// ── OG Image ────────────────────────────────────────────────────────────────
// ✅ FIX: Lấy trực tiếp từ $comics_query->posts[0]->ID thay vì gọi the_post()
// Cách cũ: $comics_query->the_post() dịch chuyển con trỏ nội bộ của query
// → vòng loop chính bên dưới bắt đầu từ post thứ 2 → chỉ hiển thị 41 truyện
// thay vì 42 ở lần load trang đầu tiên (page 1 load lần đầu).
$og_image = get_template_directory_uri() . '/images/default-og.jpg';
if (!empty($comics_query->posts)) {
    $first_id    = (int) $comics_query->posts[0]->ID;
    $first_thumb = get_post_meta($first_id, '_nettruyen_thumbnail', true);
    if (!empty($first_thumb)) {
        $og_image = $first_thumb;
    }
}
// ────────────────────────────────────────────────────────────────────────────

$status_labels = array(
    'ongoing'     => 'Đang tiến hành',
    'completed'   => 'Hoàn thành',
    'coming_soon' => 'Sắp ra mắt',
);
$country_labels = array(
    'China'   => 'Trung Quốc',
    'Vietnam' => 'Việt Nam',
    'Korea'   => 'Hàn Quốc',
    'Japan'   => 'Nhật Bản',
);

$title_parts = array('Truyện ' . $genre_name);
if ($status)    $title_parts[] = $status_labels[$status]   ?? $status;
if ($country)   $title_parts[] = $country_labels[$country] ?? $country;
if ($paged > 1) $title_parts[] = 'Trang ' . $paged;

$meta_description  = 'Đọc truyện tranh thể loại ' . $genre_name;
if ($status)  $meta_description .= ' ' . ($status_labels[$status]   ?? $status);
if ($country) $meta_description .= ' ' . ($country_labels[$country] ?? $country);
$meta_description .= ' mới nhất, cập nhật liên tục tại TruyenQQ.';
if (!empty($genre_description)) {
    $meta_description .= ' ' . wp_trim_words(wp_strip_all_tags($genre_description), 20, '...');
}
if ($paged > 1) $meta_description .= ' - Trang ' . $paged;

$canonical_url = get_term_link($current_genre);
if ($paged > 1) {
    $canonical_url = trailingslashit($canonical_url) . 'page/' . $paged . '/';
}

$prev_url = '';
$next_url = '';
if ($paged > 1) {
    $prev_url = ($paged == 2)
        ? get_term_link($current_genre)
        : trailingslashit(get_term_link($current_genre)) . 'page/' . ($paged - 1) . '/';
}
if ($paged < $total_pages) {
    $next_url = trailingslashit(get_term_link($current_genre)) . 'page/' . ($paged + 1) . '/';
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <?php wp_head();  ?>
</head>

<body <?php body_class(); ?>>
    <?php get_header(); ?>

    <main id="main-content" role="main" aria-label="Danh sách truyện theo thể loại">
        <div id="main_homepage" data-ajax-enabled="true">

            <!-- Breadcrumb -->
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList" style="display:none;">
                    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                        <a itemprop="item" href="<?php echo home_url(); ?>">
                            <span itemprop="name">Trang Chủ</span>
                        </a>
                        <meta itemprop="position" content="1">
                    </li>
                    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                        <a itemprop="item" href="<?php echo esc_url($genre_url); ?>">
                            <span itemprop="name">Thể loại <?php echo esc_html($genre_name); ?></span>
                        </a>
                        <meta itemprop="position" content="2">
                    </li>
                    <?php if ($paged > 1): ?>
                    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                        <span itemprop="name">Trang <?php echo $paged; ?></span>
                        <meta itemprop="position" content="3">
                    </li>
                    <?php endif; ?>
                </ol>
            </nav>

            <!-- Section Header -->
            <header class="homepage_tags">
                <h1>
                    <p class="text_list_update">
                        <i class="fa fa-font-awesome" aria-hidden="true"></i> Truyện
                        <?php echo esc_html($genre_name); ?>
                        <?php if ($paged > 1): ?> - Trang <?php echo $paged; ?><?php endif; ?>
                    </p>
                </h1>
                <div class="clear"></div>
            </header>

            <!-- Genre Description -->
            <?php if (!empty($genre_description)): ?>
            <div class="tags_detail" itemprop="description">
                <?php echo wp_kses_post($genre_description); ?>
            </div>
            <?php endif; ?>

            <section class="genre-listing" aria-label="Bộ lọc và danh sách truyện">

                <!-- Filter Box -->
                <div class="story-list-bl01 box">
                    <table>
                        <tbody>
                            <tr>
                                <th>Thể loại truyện</th>
                                <td>
                                    <div class="select is-warning">
                                        <select id="category" aria-label="Chọn thể loại">
                                            <?php foreach ($all_genres as $genre): ?>
                                            <option value="<?php echo esc_attr(get_term_link($genre)); ?>"
                                                <?php selected($genre->slug, $genre_slug); ?>>
                                                <?php echo esc_html($genre->name); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>Tình trạng</th>
                                <td>
                                    <ul class="choose">
                                        <li><a class="<?php echo ($status === '') ? 'active' : ''; ?>"
                                                href="javascript:void(0)" data-filter="status" data-value="">Tất cả</a>
                                        </li>
                                        <li><a class="<?php echo ($status === 'ongoing') ? 'active' : ''; ?>"
                                                href="javascript:void(0)" data-filter="status" data-value="ongoing">Đang
                                                tiến hành</a></li>
                                        <li><a class="<?php echo ($status === 'completed') ? 'active' : ''; ?>"
                                                href="javascript:void(0)" data-filter="status"
                                                data-value="completed">Hoàn thành</a></li>
                                        <li><a class="<?php echo ($status === 'coming_soon') ? 'active' : ''; ?>"
                                                href="javascript:void(0)" data-filter="status"
                                                data-value="coming_soon">Sắp ra mắt</a></li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <th>Quốc gia</th>
                                <td>
                                    <ul class="choose">
                                        <li><a class="<?php echo ($country === '') ? 'active' : ''; ?>"
                                                href="javascript:void(0)" data-filter="country" data-value="">Tất cả</a>
                                        </li>
                                        <li><a class="<?php echo ($country === 'China') ? 'active' : ''; ?>"
                                                title="Truyện Trung Quốc" href="javascript:void(0)"
                                                data-filter="country" data-value="China">Trung Quốc</a></li>
                                        <li><a class="<?php echo ($country === 'Vietnam') ? 'active' : ''; ?>"
                                                title="Truyện Việt Nam" href="javascript:void(0)" data-filter="country"
                                                data-value="Vietnam">Việt Nam</a></li>
                                        <li><a class="<?php echo ($country === 'Korea') ? 'active' : ''; ?>"
                                                title="Truyện Hàn Quốc" href="javascript:void(0)" data-filter="country"
                                                data-value="Korea">Hàn Quốc</a></li>
                                        <li><a class="<?php echo ($country === 'Japan') ? 'active' : ''; ?>"
                                                title="Truyện Nhật Bản" href="javascript:void(0)" data-filter="country"
                                                data-value="Japan">Nhật Bản</a></li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <th>Sắp xếp</th>
                                <td>
                                    <div class="select is-warning">
                                        <select id="category-sort" aria-label="Chọn cách sắp xếp">
                                            <option value="0" <?php selected($sort, 0); ?>>Ngày đăng giảm dần</option>
                                            <option value="1" <?php selected($sort, 1); ?>>Ngày đăng tăng dần</option>
                                            <option value="2" <?php selected($sort, 2); ?>>Ngày cập nhật giảm dần
                                            </option>
                                            <option value="3" <?php selected($sort, 3); ?>>Ngày cập nhật tăng dần
                                            </option>
                                            <option value="4" <?php selected($sort, 4); ?>>Lượt xem giảm dần</option>
                                            <option value="5" <?php selected($sort, 5); ?>>Lượt xem tăng dần</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="list_grid_out">
                    <ul class="list_grid grid">
                        <?php
                        if ($comics_query->have_posts()):
                            while ($comics_query->have_posts()):
                                $comics_query->the_post();
                                $post_id = get_the_ID();

                                $thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);
                                if (empty($thumbnail))
                                    $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
                                if (empty($thumbnail))
                                    $thumbnail = 'https://via.placeholder.com/190x247?text=No+Image';

                                $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
                                $manifest      = !empty($manifest_json) ? json_decode($manifest_json, true) : null;

                                $latest_chapter = 'Đang cập nhật';
                                if (!empty($manifest['chapters'])) {
                                    $latest         = end($manifest['chapters']);
                                    $latest_chapter = 'Chapter ' . $latest['name'];
                                }

                                $updated_at = !empty($manifest['updated_at'])
                                    ? $manifest['updated_at']
                                    : get_the_modified_date('Y-m-d H:i:s');
                                $time_ago   = truyenqq_time_ago_vietnamese($updated_at);

                                $follow_count = (int) get_post_meta($post_id, '_nettruyen_follow_count', true);

                                $view_count = 0;
                                $view_stats = $wpdb->get_row($wpdb->prepare(
                                    "SELECT total_display_views FROM {$stats_table} WHERE post_id = %d",
                                    $post_id
                                ));
                                if ($view_stats) $view_count = $view_stats->total_display_views;

                                $is_hot = in_array($post_id, $hot_comic_ids);
                                $is_new = (current_time('timestamp') - strtotime($updated_at)) <= (7 * 24 * 60 * 60);

                                $badge_type = '';
                                $badge_text = '';
                                if ($is_hot)     { $badge_type = 'hot'; $badge_text = 'Hot'; }
                                elseif ($is_new) { $badge_type = 'new'; $badge_text = 'New'; }
                                ?>
                        <li itemscope itemtype="http://schema.org/Book">
                            <div class="book_avatar">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"
                                    itemprop="url">
                                    <img class="center" src="<?php echo esc_url($thumbnail); ?>"
                                        alt="<?php echo esc_attr(get_the_title() . ' - Đọc truyện tranh online tại TruyenQQ'); ?>"
                                        width="190" height="247" loading="lazy" itemprop="image">
                                </a>
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
                                    <h3 itemprop="name">
                                        <a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </h3>
                                </div>
                                <div class="clear"></div>
                                <div class="text_detail">
                                    <span><i class="fa fa-bookmark"></i>
                                        <?php echo number_format($follow_count); ?></span>
                                    <span><i class="fa fa-eye"></i> <?php echo number_format($view_count); ?></span>
                                </div>
                                <div class="last_chapter">
                                    <a href="<?php the_permalink(); ?>"
                                        title="<?php echo esc_attr($latest_chapter); ?>">
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

                <?php if ($total_pages > 1): ?>
                <nav class="page_redirect" aria-label="Phân trang">
                    <?php if ($current_page > 1): ?>
                    <a href="javascript:void(0)" data-page="<?php echo $current_page - 1; ?>" aria-label="Trang trước">
                        <p><span aria-hidden="true">‹</span></p>
                    </a>
                    <?php endif; ?>

                    <?php
                    $range = 2;
                    $start = max(1, $current_page - $range);
                    $end   = min($total_pages, $current_page + $range);
                    if ($start > 1): ?>
                    <a href="javascript:void(0)" data-page="1" aria-label="Trang 1">
                        <p>1</p>
                    </a>
                    <?php if ($start > 2): ?><span class="dots">...</span><?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $current_page): ?>
                    <a href="javascript:void(0)" aria-current="page">
                        <p class="active"><?php echo $i; ?></p>
                    </a>
                    <?php else: ?>
                    <a href="javascript:void(0)" data-page="<?php echo $i; ?>" aria-label="Trang <?php echo $i; ?>">
                        <p><?php echo $i; ?></p>
                    </a>
                    <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($end < $total_pages): ?>
                    <?php if ($end < $total_pages - 1): ?><span class="dots">...</span><?php endif; ?>
                    <a href="javascript:void(0)" data-page="<?php echo $total_pages; ?>"
                        aria-label="Trang <?php echo $total_pages; ?>">
                        <p><?php echo $total_pages; ?></p>
                    </a>
                    <?php endif; ?>

                    <?php if ($current_page < $total_pages): ?>
                    <a href="javascript:void(0)" data-page="<?php echo $current_page + 1; ?>" aria-label="Trang tiếp">
                        <p><span aria-hidden="true">›</span></p>
                    </a>
                    <a href="javascript:void(0)" data-page="<?php echo $total_pages; ?>" aria-label="Trang cuối">
                        <p><span aria-hidden="true">»</span></p>
                    </a>
                    <?php endif; ?>
                </nav>
                <?php endif; ?>

            </section>
        </div>
    </main>

    <?php get_footer(); ?>
</body>

</html>