<?php
/**
 * Template Name: Truyện Theo Thể Loại - SEO OPTIMIZED
 * Template for nettruyen_genre taxonomy
 * 
 * @package TruyenQQ
 * @version 1.1.0 - SEO OPTIMIZED
 */

require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

$current_genre = get_queried_object();
$genre_slug = $current_genre->slug;
$genre_name = $current_genre->name;
$genre_description = $current_genre->description;

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$posts_per_page = 42;

$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$country = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
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
    'tax_query' => array(
        array(
            'taxonomy' => 'nettruyen_genre',
            'field' => 'slug',
            'terms' => $genre_slug
        )
    ),
    'orderby' => $sort_config['orderby'],
    'order' => $sort_config['order']
);

if ($status !== '') {
    $args['meta_query'][] = array(
        'key' => '_nettruyen_status',
        'value' => $status,
        'compare' => '='
    );
}

if ($country !== '') {
    $args['tax_query'][] = array(
        'taxonomy' => 'nettruyen_country',
        'field' => 'slug',
        'terms' => $country
    );
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
    'hide_empty' => true,
    'orderby' => 'name',
    'order' => 'ASC'
));

$total_pages = $comics_query->max_num_pages;
$current_page = max(1, $paged);

// ============================================
// ✅ SEO: Prepare meta data
// ============================================
$genre_url = get_term_link($current_genre);

// Build title parts
$title_parts = array('Truyện ' . $genre_name);

// Add filters to title
if ($status) {
    $status_labels = array(
        'ongoing' => 'Đang tiến hành',
        'completed' => 'Hoàn thành',
        'coming_soon' => 'Sắp ra mắt'
    );
    $title_parts[] = $status_labels[$status] ?? $status;
}

if ($country) {
    $country_labels = array(
        'China' => 'Trung Quốc',
        'Vietnam' => 'Việt Nam',
        'Korea' => 'Hàn Quốc',
        'Japan' => 'Nhật Bản'
    );
    $title_parts[] = $country_labels[$country] ?? $country;
}

// Add page number if not first page
if ($paged > 1) {
    $title_parts[] = 'Trang ' . $paged;
}

$page_title = implode(' - ', $title_parts) . ' | TruyenQQ';

// Build meta description
$meta_description = 'Đọc truyện tranh thể loại ' . $genre_name;

if ($status) {
    $meta_description .= ' ' . ($status_labels[$status] ?? $status);
}

if ($country) {
    $meta_description .= ' ' . ($country_labels[$country] ?? $country);
}

$meta_description .= ' mới nhất, cập nhật liên tục tại TruyenQQ.';

// Add genre description if available
if (!empty($genre_description)) {
    $short_desc = wp_trim_words(wp_strip_all_tags($genre_description), 20, '...');
    $meta_description .= ' ' . $short_desc;
}

if ($paged > 1) {
    $meta_description .= ' - Trang ' . $paged;
}

// OG Image (use first comic thumbnail if available)
$og_image = get_template_directory_uri() . '/images/default-og.jpg';
if ($comics_query->have_posts()) {
    $comics_query->the_post();
    $first_comic_id = get_the_ID();
    $first_thumbnail = get_post_meta($first_comic_id, '_nettruyen_thumbnail', true);
    if (!empty($first_thumbnail)) {
        $og_image = $first_thumbnail;
    }
    wp_reset_postdata();
}

// Build canonical URL (without filter params for cleaner SEO)
$canonical_url = get_term_link($current_genre);
if ($paged > 1) {
    $canonical_url = trailingslashit($canonical_url) . 'page/' . $paged . '/';
}

// Prev/Next URLs for pagination
$prev_url = '';
$next_url = '';

if ($paged > 1) {
    if ($paged == 2) {
        $prev_url = get_term_link($current_genre);
    } else {
        $prev_url = trailingslashit(get_term_link($current_genre)) . 'page/' . ($paged - 1) . '/';
    }
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

    <!-- ✅ SEO: Custom Title -->
    <title><?php echo esc_html($page_title); ?></title>

    <!-- ✅ SEO: Meta Description -->
    <meta name="description" content="<?php echo esc_attr($meta_description); ?>">

    <!-- ✅ SEO: Canonical Link -->
    <link rel="canonical" href="<?php echo esc_url($canonical_url); ?>">

    <!-- ✅ SEO: Prev/Next Navigation -->
    <?php if ($prev_url): ?>
    <link rel="prev" href="<?php echo esc_url($prev_url); ?>">
    <?php endif; ?>
    <?php if ($next_url): ?>
    <link rel="next" href="<?php echo esc_url($next_url); ?>">
    <?php endif; ?>

    <!-- ✅ SEO: Open Graph (Facebook) -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo esc_attr(implode(' - ', $title_parts)); ?>">
    <meta property="og:description" content="<?php echo esc_attr($meta_description); ?>">
    <meta property="og:url" content="<?php echo esc_url($canonical_url); ?>">
    <meta property="og:image" content="<?php echo esc_url($og_image); ?>">
    <meta property="og:image:width" content="800">
    <meta property="og:image:height" content="1200">
    <meta property="og:site_name" content="TruyenQQ">
    <meta property="og:locale" content="vi_VN">

    <!-- ✅ SEO: Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr(implode(' - ', $title_parts)); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($meta_description); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($og_image); ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php get_header(); ?>

    <!-- ✅ Semantic HTML: Main content wrapper -->
    <main id="main-content" role="main" aria-label="Danh sách truyện theo thể loại">
        <div id="main_homepage" data-ajax-enabled="true">
            <!-- ✅ SEO: BreadcrumbList (inline for this page) -->
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList" style="display: none;">
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
                        <?php if ($paged > 1): ?>
                        - Trang <?php echo $paged; ?>
                        <?php endif; ?>
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

            <!-- ✅ Semantic HTML: Section wrapper -->
            <section class="genre-listing" aria-label="Bộ lọc và danh sách truyện">
                <!-- Filter Box -->
                <div class="story-list-bl01 box">
                    <table>
                        <tbody>
                            <!-- Genre Selector -->
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

                            <!-- Status Filter -->
                            <tr>
                                <th>Tình trạng</th>
                                <td>
                                    <ul class="choose">
                                        <li>
                                            <a class="<?php echo ($status === '') ? 'active' : ''; ?>"
                                                href="javascript:void(0)" data-filter="status" data-value=""
                                                aria-label="Lọc tất cả tình trạng">
                                                Tất cả
                                            </a>
                                        </li>
                                        <li>
                                            <a class="<?php echo ($status === 'ongoing') ? 'active' : ''; ?>"
                                                href="javascript:void(0)" data-filter="status" data-value="ongoing"
                                                aria-label="Lọc truyện đang tiến hành">
                                                Đang tiến hành
                                            </a>
                                        </li>
                                        <li>
                                            <a class="<?php echo ($status === 'completed') ? 'active' : ''; ?>"
                                                href="javascript:void(0)" data-filter="status" data-value="completed"
                                                aria-label="Lọc truyện hoàn thành">
                                                Hoàn thành
                                            </a>
                                        </li>
                                        <li>
                                            <a class="<?php echo ($status === 'coming_soon') ? 'active' : ''; ?>"
                                                href="javascript:void(0)" data-filter="status" data-value="coming_soon"
                                                aria-label="Lọc truyện sắp ra mắt">
                                                Sắp ra mắt
                                            </a>
                                        </li>
                                    </ul>
                                </td>
                            </tr>

                            <!-- Country Filter -->
                            <tr>
                                <th>Quốc gia</th>
                                <td>
                                    <ul class="choose">
                                        <li>
                                            <a class="<?php echo ($country === '') ? 'active' : ''; ?>"
                                                href="javascript:void(0)" data-filter="country" data-value=""
                                                aria-label="Lọc tất cả quốc gia">
                                                Tất cả
                                            </a>
                                        </li>
                                        <li>
                                            <a class="<?php echo ($country === 'China') ? 'active' : ''; ?>"
                                                title="Truyện Trung Quốc" href="javascript:void(0)"
                                                data-filter="country" data-value="China"
                                                aria-label="Lọc truyện Trung Quốc">
                                                Trung Quốc
                                            </a>
                                        </li>
                                        <li>
                                            <a class="<?php echo ($country === 'Vietnam') ? 'active' : ''; ?>"
                                                title="Truyện Việt Nam" href="javascript:void(0)" data-filter="country"
                                                data-value="Vietnam" aria-label="Lọc truyện Việt Nam">
                                                Việt Nam
                                            </a>
                                        </li>
                                        <li>
                                            <a class="<?php echo ($country === 'Korea') ? 'active' : ''; ?>"
                                                title="Truyện Hàn Quốc" href="javascript:void(0)" data-filter="country"
                                                data-value="Korea" aria-label="Lọc truyện Hàn Quốc">
                                                Hàn Quốc
                                            </a>
                                        </li>
                                        <li>
                                            <a class="<?php echo ($country === 'Japan') ? 'active' : ''; ?>"
                                                title="Truyện Nhật Bản" href="javascript:void(0)" data-filter="country"
                                                data-value="Japan" aria-label="Lọc truyện Nhật Bản">
                                                Nhật Bản
                                            </a>
                                        </li>
                                    </ul>
                                </td>
                            </tr>

                            <!-- Sort Filter -->
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
                                    <span>
                                        <i class="fa fa-bookmark"></i> <?php echo esc_html($follow_count_formatted); ?>
                                    </span>
                                    <span>
                                        <i class="fa fa-eye"></i> <?php echo esc_html($view_count_formatted); ?>
                                    </span>
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

                <!-- Pagination -->
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
                    $end = min($total_pages, $current_page + $range);

                    if ($start > 1):
                        ?>
                    <a href="javascript:void(0)" data-page="1" aria-label="Trang 1">
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
                    <a href="javascript:void(0)" aria-current="page">
                        <p class="active"><?php echo $i; ?></p>
                    </a>
                    <?php else: ?>
                    <a href="javascript:void(0)" data-page="<?php echo $i; ?>" aria-label="Trang <?php echo $i; ?>">
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
                    <a href="javascript:void(0)" data-page="<?php echo $total_pages; ?>"
                        aria-label="Trang <?php echo $total_pages; ?>">
                        <p><?php echo $total_pages; ?></p>
                    </a>
                    <?php endif; ?>

                    <?php if ($current_page < $total_pages): ?>
                    <a href="javascript:void(0)" data-page="<?php echo $current_page + 1; ?>"
                        aria-label="Trang tiếp theo">
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