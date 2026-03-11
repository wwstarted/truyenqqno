<?php
/**
 * Template: Truyện Theo Tác Giả
 * Taxonomy: nettruyen_author
 *
 * @package TruyenQQ
 * @version 1.0.1
 */

require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

$current_author = get_queried_object();
$author_slug = $current_author->slug;
$author_name = $current_author->name;
$author_desc = $current_author->description;
$author_count = $current_author->count;

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$posts_per_page = 42;
$sort = isset($_GET['sort']) ? absint($_GET['sort']) : 2;

global $wpdb;
$stats_table = $wpdb->prefix . 'nettruyen_view_stats';

$term_id = (int) $current_author->term_id;

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
        break;
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
     WHERE tt.taxonomy  = 'nettruyen_author'
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
     WHERE tt.taxonomy  = 'nettruyen_author'
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
$author_url = get_term_link($current_author);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php get_header(); ?>

    <main id="main-content" role="main" aria-label="Danh sách truyện theo tác giả">
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
                        <a itemprop="item" href="<?php echo esc_url($author_url); ?>">
                            <span itemprop="name">Tác giả <?php echo esc_html($author_name); ?></span>
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

            <!-- Page heading -->
            <header class="homepage_tags">
                <h1>
                    <p class="text_list_update">
                        <i class="fa fa-pencil" aria-hidden="true"></i>
                        Truyện của <?php echo esc_html($author_name); ?>
                        <?php if ($paged > 1): ?> - Trang <?php echo $paged; ?><?php endif; ?>
                    </p>
                </h1>
            </header>

            <section class="author-listing" aria-label="Thông tin tác giả và danh sách truyện">

                <!-- ── Author Info Box ── -->
                <div class="author-info-box">
                    <div class="author-info-avatar">
                        <div class="author-avatar-circle">
                            <span><?php echo mb_strtoupper(mb_substr($author_name, 0, 1, 'UTF-8'), 'UTF-8'); ?></span>
                        </div>
                    </div>
                    <div class="author-info-body">
                        <h2 class="author-info-name"><?php echo esc_html($author_name); ?></h2>
                        <div class="author-info-meta">
                            <span class="author-meta-item">
                                <i class="fa fa-book"></i>
                                <strong><?php echo number_format($author_count); ?></strong> bộ truyện
                            </span>
                        </div>
                        <?php if (!empty($author_desc)): ?>
                        <p class="author-info-desc"><?php echo wp_kses_post($author_desc); ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- Sort -->
                    <div class="author-info-sort">
                        <label class="author-sort-label" for="author-sort">
                            <i class="fa fa-sort-amount-desc"></i> Sắp xếp
                        </label>
                        <div class="select is-warning">
                            <select id="author-sort" aria-label="Chọn cách sắp xếp">
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
                                    $latest = end($manifest['chapters']);
                                    $latest_chapter = 'Chapter ' . $latest['name'];
                                }

                                $updated_at = !empty($manifest['updated_at'])
                                    ? $manifest['updated_at']
                                    : get_post_modified_time('Y-m-d H:i:s', false, $post_id);
                                $time_ago = truyenqq_time_ago_vietnamese($updated_at);
                                $follow_count = (int) get_post_meta($post_id, '_nettruyen_follow_count', true);

                                $view_count = 0;
                                $view_row = $wpdb->get_row($wpdb->prepare(
                                    "SELECT total_display_views FROM {$stats_table} WHERE post_id = %d",
                                    $post_id
                                ));
                                if ($view_row)
                                    $view_count = $view_row->total_display_views;

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

                        if ($start > 1): ?>
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