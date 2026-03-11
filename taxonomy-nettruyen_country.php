<?php
/**
 * Template: Truyện Theo Quốc Gia
 * Taxonomy: nettruyen_country
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

$current_country = get_queried_object();
$country_slug = $current_country->slug;
$country_name = $current_country->name;
$country_desc = $current_country->description;
$country_count = $current_country->count;

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$posts_per_page = 42;

$sort = isset($_GET['sort']) ? absint($_GET['sort']) : 2;

global $wpdb;
$stats_table = $wpdb->prefix . 'nettruyen_view_stats';

/*
 * ── Direct SQL query ──────────────────────────────────────────────────────
 * Dùng $wpdb thay vì WP_Query để tránh bị ảnh hưởng bởi pre_get_posts hooks.
 */
$term_id = (int) $current_country->term_id;

switch ($sort) {
    case 0:
        $orderby_sql = 'p.post_date DESC';
        break;
    case 1:
        $orderby_sql = 'p.post_date ASC';
        break;
    case 3:
        $orderby_sql = 'p.post_modified ASC';
        break;
    case 4:
        $orderby_sql = 'COALESCE(vs.total_display_views, 0) DESC';
        break;
    case 5:
        $orderby_sql = 'COALESCE(vs.total_display_views, 0) ASC';
        break;
    default:
        $orderby_sql = 'p.post_modified DESC';
        break; // sort=2
}

$view_join_sql = ($sort === 4 || $sort === 5)
    ? "LEFT JOIN {$stats_table} AS vs ON p.ID = vs.post_id"
    : '';

$offset = ($paged - 1) * $posts_per_page;

$total_posts = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(DISTINCT p.ID)
     FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
     INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
     WHERE tt.taxonomy  = 'nettruyen_country'
       AND tt.term_id   = %d
       AND p.post_type  = 'nettruyen_comic'
       AND p.post_status = 'publish'",
    $term_id
));

$comic_post_ids = $wpdb->get_col($wpdb->prepare(
    "SELECT DISTINCT p.ID
     FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
     INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
     {$view_join_sql}
     WHERE tt.taxonomy  = 'nettruyen_country'
       AND tt.term_id   = %d
       AND p.post_type  = 'nettruyen_comic'
       AND p.post_status = 'publish'
     ORDER BY {$orderby_sql}
     LIMIT %d OFFSET %d",
    $term_id,
    $posts_per_page,
    $offset
));

$hot_comic_ids = $wpdb->get_col(
    "SELECT post_id FROM {$stats_table}
     WHERE total_display_views > 0
     ORDER BY total_display_views DESC
     LIMIT 20"
);

$total_pages = ($total_posts > 0) ? (int) ceil($total_posts / $posts_per_page) : 0;
$current_page = max(1, $paged);

$og_image = get_template_directory_uri() . '/images/default-og.jpg';
if (!empty($comic_post_ids)) {
    $first_thumb = get_post_meta($comic_post_ids[0], '_nettruyen_thumbnail', true);
    if (!empty($first_thumb))
        $og_image = $first_thumb;
}

/* ── SEO ── */
$title_parts = array('Truyện ' . $country_name);
if ($paged > 1)
    $title_parts[] = 'Trang ' . $paged;
$page_title = implode(' - ', $title_parts) . ' | TruyenQQ';

$meta_description = 'Đọc toàn bộ truyện tranh ' . $country_name . ' mới nhất, cập nhật liên tục tại TruyenQQ.';
if (!empty($country_desc)) {
    $meta_description .= ' ' . wp_trim_words(wp_strip_all_tags($country_desc), 20, '...');
}
if ($paged > 1)
    $meta_description .= ' - Trang ' . $paged;

$canonical_url = get_term_link($current_country);
if ($paged > 1) {
    $canonical_url = trailingslashit($canonical_url) . 'page/' . $paged . '/';
}

$prev_url = '';
$next_url = '';
if ($paged > 1) {
    $prev_url = ($paged == 2)
        ? get_term_link($current_country)
        : trailingslashit(get_term_link($current_country)) . 'page/' . ($paged - 1) . '/';
}
if ($paged < $total_pages) {
    $next_url = trailingslashit(get_term_link($current_country)) . 'page/' . ($paged + 1) . '/';
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo esc_html($page_title); ?></title>
    <meta name="description" content="<?php echo esc_attr($meta_description); ?>">
    <link rel="canonical" href="<?php echo esc_url($canonical_url); ?>">

    <?php if ($prev_url): ?>
    <link rel="prev" href="<?php echo esc_url($prev_url); ?>">
    <?php endif; ?>
    <?php if ($next_url): ?>
    <link rel="next" href="<?php echo esc_url($next_url); ?>">
    <?php endif; ?>

    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo esc_attr(implode(' - ', $title_parts)); ?>">
    <meta property="og:description" content="<?php echo esc_attr($meta_description); ?>">
    <meta property="og:url" content="<?php echo esc_url($canonical_url); ?>">
    <meta property="og:image" content="<?php echo esc_url($og_image); ?>">
    <meta property="og:site_name" content="TruyenQQ">
    <meta property="og:locale" content="vi_VN">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr(implode(' - ', $title_parts)); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($meta_description); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($og_image); ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php get_header(); ?>

    <main id="main-content" role="main" aria-label="Danh sách truyện theo quốc gia">
        <div id="main_homepage" data-ajax-enabled="true">

            <!-- Breadcrumb -->
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
                    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                        <a itemprop="item" href="<?php echo home_url(); ?>">
                            <span itemprop="name"><i class="fa fa-home"></i> Trang Chủ</span>
                        </a>
                        <meta itemprop="position" content="1">
                    </li>
                    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                        <span itemprop="name">Truyện <?php echo esc_html($country_name); ?></span>
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

            <!-- Page heading -->
            <header class="homepage_tags">
                <h1>
                    <p class="text_list_update">
                        <i class="fa fa-globe" aria-hidden="true"></i>
                        Truyện <?php echo esc_html($country_name); ?>
                        <?php if ($paged > 1): ?> - Trang <?php echo $paged; ?><?php endif; ?>
                    </p>
                </h1>
            </header>

            <section class="author-listing" aria-label="Thông tin quốc gia và danh sách truyện">

                <!-- Country Info Box -->
                <div class="author-info-box">
                    <div class="author-info-avatar">
                        <div class="author-avatar-circle">
                            <span><?php echo esc_html(mb_strtoupper(mb_substr($country_name, 0, 1, 'UTF-8'), 'UTF-8')); ?></span>
                        </div>
                    </div>
                    <div class="author-info-body">
                        <h2 class="author-info-name"><?php echo esc_html($country_name); ?></h2>
                        <div class="author-info-meta">
                            <span class="author-meta-item">
                                <i class="fa fa-book"></i>
                                <strong><?php echo number_format($country_count); ?></strong> bộ truyện
                            </span>
                        </div>
                        <?php if (!empty($country_desc)): ?>
                        <p class="author-info-desc"><?php echo wp_kses_post($country_desc); ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- Sort -->
                    <div class="author-info-sort">
                        <label class="author-sort-label" for="country-sort">
                            <i class="fa fa-sort-amount-desc"></i> Sắp xếp
                        </label>
                        <div class="select is-warning">
                            <select id="country-sort" aria-label="Chọn cách sắp xếp">
                                <option value="0" <?php selected($sort, 0); ?>>Ngày đăng giảm dần</option>
                                <option value="1" <?php selected($sort, 1); ?>>Ngày đăng tăng dần</option>
                                <option value="2" <?php selected($sort, 2); ?>>Ngày cập nhật giảm dần</option>
                                <option value="3" <?php selected($sort, 3); ?>>Ngày cập nhật tăng dần</option>
                                <option value="4" <?php selected($sort, 4); ?>>Lượt xem giảm dần</option>
                                <option value="5" <?php selected($sort, 5); ?>>Lượt xem tăng dần</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ── Comics Grid ── -->
                <div class="list_grid_out">
                    <ul class="list_grid grid">
                        <?php
                        if (!empty($comic_post_ids)):
                            foreach ($comic_post_ids as $post_id):
                                $post_id = (int) $post_id;

                                $thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);
                                if (empty($thumbnail))
                                    $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
                                if (empty($thumbnail))
                                    $thumbnail = 'https://via.placeholder.com/190x247?text=No+Image';

                                $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
                                $manifest = !empty($manifest_json) ? json_decode($manifest_json, true) : null;
                                $latest_chapter = 'Đang cập nhật';
                                if (!empty($manifest['chapters'])) {
                                    $chapters = $manifest['chapters'];
                                    $latest = end($chapters);
                                    $latest_chapter = 'Chapter ' . $latest['name'];
                                }

                                $updated_at = !empty($manifest['updated_at'])
                                    ? $manifest['updated_at']
                                    : get_post_modified_time('Y-m-d H:i:s', false, $post_id);
                                $time_ago = truyenqq_time_ago_vietnamese($updated_at);

                                $follow_count = (int) get_post_meta($post_id, '_nettruyen_follow_count', true);

                                $view_count = 0;
                                $view_stats = $wpdb->get_row($wpdb->prepare(
                                    "SELECT total_display_views FROM {$stats_table} WHERE post_id = %d",
                                    $post_id
                                ));
                                if ($view_stats)
                                    $view_count = $view_stats->total_display_views;

                                $permalink = get_permalink($post_id);
                                $post_title = get_the_title($post_id);

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
                                <a href="<?php echo esc_url($permalink); ?>"
                                    title="<?php echo esc_attr($post_title); ?>" itemprop="url">
                                    <img class="center" src="<?php echo esc_url($thumbnail); ?>"
                                        alt="<?php echo esc_attr($post_title . ' - TruyenQQ'); ?>" width="190"
                                        height="247" loading="lazy" itemprop="image">
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
                                        <a title="<?php echo esc_attr($post_title); ?>"
                                            href="<?php echo esc_url($permalink); ?>">
                                            <?php echo esc_html($post_title); ?>
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
                                    <a href="<?php echo esc_url($permalink); ?>"
                                        title="<?php echo esc_attr($latest_chapter); ?>">
                                        <?php echo esc_html($latest_chapter); ?>
                                    </a>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </li>
                        <?php
                            endforeach;
                        else:
                            echo '<li class="no-results"><p>Không tìm thấy truyện nào.</p></li>';
                        endif;
                        ?>
                    </ul>
                </div>

                <div class="clear"></div>

                <!-- ── Pagination ── -->
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
                    <a href="javascript:void(0)" data-page="1">
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
                    <a href="javascript:void(0)" data-page="<?php echo $i; ?>">
                        <p><?php echo $i; ?></p>
                    </a>
                    <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($end < $total_pages): ?>
                    <?php if ($end < $total_pages - 1): ?><span class="dots">...</span><?php endif; ?>
                    <a href="javascript:void(0)" data-page="<?php echo $total_pages; ?>">
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