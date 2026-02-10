<?php
/**
 * Template Name: Kết Quả Tìm Kiếm
 * Description: Search results page with filtering
 * 
 * @package TruyenQQ
 * @version 1.0.2 - FIXED SORT OPTIONS
 */

require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

 
$keyword = isset($_GET['keyword']) ? sanitize_text_field($_GET['keyword']) : '';

 
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$country = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
$sort = isset($_GET['sort']) ? absint($_GET['sort']) : 0;  

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$posts_per_page = 42;

 
$args = array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
);

 
if (!empty($keyword)) {
    $args['s'] = $keyword;
}

 
$sort_options = array(
    0 => array('orderby' => 'relevance', 'order' => 'DESC'),  
    1 => array('orderby' => 'meta_value_num', 'order' => 'DESC', 'meta_key' => '_nettruyen_view_count'),  
    2 => array('orderby' => 'meta_value_num', 'order' => 'ASC', 'meta_key' => '_nettruyen_view_count'),  
    3 => array('orderby' => 'date', 'order' => 'DESC'),  
    4 => array('orderby' => 'date', 'order' => 'ASC'),  
);

$sort_config = isset($sort_options[$sort]) ? $sort_options[$sort] : $sort_options[0];

 
if (isset($sort_config['meta_key'])) {
    $args['meta_key'] = $sort_config['meta_key'];
}
$args['orderby'] = $sort_config['orderby'];
$args['order'] = $sort_config['order'];

 
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
$total_results = $comics_query->found_posts;

 
$page_title = 'Kết quả tìm kiếm';
if (!empty($keyword)) {
    $page_title = 'Tìm kiếm: ' . $keyword;
}
if ($paged > 1) {
    $page_title .= ' - Trang ' . $paged;
}
$page_title .= ' | TruyenQQ';

$meta_description = 'Kết quả tìm kiếm truyện tranh';
if (!empty($keyword)) {
    $meta_description = 'Tìm kiếm "' . $keyword . '" - Tìm thấy ' . number_format($total_results) . ' kết quả tại TruyenQQ';
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo esc_html($page_title); ?></title>
    <meta name="description" content="<?php echo esc_attr($meta_description); ?>">

    <link rel="canonical" href="<?php echo esc_url(home_url('/ket-qua-tim-kiem/')); ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo esc_attr($page_title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($meta_description); ?>">
    <meta property="og:url" content="<?php echo esc_url(home_url('/ket-qua-tim-kiem/')); ?>">
    <meta property="og:site_name" content="TruyenQQ">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php get_header(); ?>

    <main id="main_homepage" data-ajax-enabled="true">
        <div id="main_homepage_inner">

            <!-- Header Section -->
            <header class="homepage_tags">
                <h1>
                    <p class="text_list_update">
                        <i class="fa fa-search" aria-hidden="true"></i>
                        <?php if (!empty($keyword)): ?>
                        Kết quả tìm kiếm: <span class="search-keyword"><?php echo esc_html($keyword); ?></span>
                        <?php else: ?>
                        Kết quả tìm kiếm
                        <?php endif; ?>
                    </p>
                </h1>
                <div class="search-meta">
                    <?php if ($total_results > 0): ?>
                    Tìm thấy <strong><?php echo number_format($total_results); ?></strong> kết quả
                    <?php else: ?>
                    Không tìm thấy kết quả nào
                    <?php endif; ?>
                </div>
            </header>

            <!-- Toggle Filter Button -->
            <div class="filter-toggle-wrapper">
                <button class="filter-toggle-btn" id="filterToggleBtn" aria-expanded="false" aria-controls="filterBox">
                    <i class="fa fa-filter"></i>
                    <span>Lọc kết quả</span>
                    <i class="fa fa-chevron-down toggle-icon"></i>
                </button>
            </div>

            <section class="search-results-section">
                <!-- Filter Box -->
                <div class="story-list-bl01 box filter-box" id="filterBox">
                    <table>
                        <tbody>
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

                            <!-- ✅ FIXED: Sort Filter - Removed Follow options -->
                            <tr>
                                <th>Sắp xếp</th>
                                <td>
                                    <div class="select is-warning">
                                        <select id="sort-select" aria-label="Chọn cách sắp xếp">
                                            <option value="0" <?php selected($sort, 0); ?>>Liên quan nhất</option>
                                            <option value="1" <?php selected($sort, 1); ?>>Lượt xem cao nhất</option>
                                            <option value="2" <?php selected($sort, 2); ?>>Lượt xem thấp nhất</option>
                                            <option value="3" <?php selected($sort, 3); ?>>Mới nhất</option>
                                            <option value="4" <?php selected($sort, 4); ?>>Cũ nhất</option>
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

                                $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_the_modified_date('Y-m-d H:i:s');
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

                        <li itemscope itemtype="http://schema.org/Book">
                            <div class="book_avatar">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"
                                    itemprop="url">
                                    <img class="center" src="<?php echo esc_url($thumbnail); ?>"
                                        alt="<?php echo esc_attr(get_the_title()); ?>" width="190" height="247"
                                        loading="lazy" itemprop="image">
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
                                        <i class="fa fa-bookmark"></i>
                                        <?php echo esc_html($follow_count_formatted); ?>
                                    </span>
                                    <span>
                                        <i class="fa fa-eye"></i>
                                        <?php echo esc_html($view_count_formatted); ?>
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
                            ?>
                        <li class="no-results">
                            <div class="no-results-content">
                                <i class="fa fa-search"></i>
                                <p>Không tìm thấy kết quả nào
                                    <?php if (!empty($keyword)): ?>
                                    cho từ khóa "<strong><?php echo esc_html($keyword); ?></strong>"
                                    <?php endif; ?>
                                </p>
                                <p class="suggestion">Thử tìm kiếm với từ khóa khác hoặc <a
                                        href="<?php echo home_url('/'); ?>">quay về trang chủ</a></p>
                            </div>
                        </li>
                        <?php
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