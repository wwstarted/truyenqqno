<?php get_header(); ?>

<?php
require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

global $wpdb;
$stats_table = $wpdb->prefix . 'nettruyen_view_stats';
?>

<h1 class="seo-h1" style="position:absolute;left:-9999px;top:-9999px;">
    TruyenQQ - Đọc Truyện Tranh Online Miễn Phí - Manga Manhwa Manhua
</h1>

<main id="main-content" role="main" aria-label="Nội dung chính"></main>

<?php
/* =====================================================
   HOMEPAGE SECTIONS

   Priority 1: sidebar "homepage-sections" (LINH HOẠT)
   ─ Kéo thả bất kỳ widget nào, bất kỳ thứ tự nào
   ─ Dùng TruyenQQ_Comic_Carousel_Widget hoặc
     TruyenQQ_New_Update_Widget hoặc cả hai

   Priority 2: 3 sidebar riêng (TƯƠNG THÍCH CŨ)
   ─ homepage-suggest-section
   ─ homepage-exclusive-section
   ─ homepage-new-update-section

   Priority 3: Hardcode fallback (nếu không có widget nào)
===================================================== */
?>

<?php if (is_active_sidebar('homepage-sections')): ?>

<?php dynamic_sidebar('homepage-sections'); ?>

<?php else: ?>

<?php
    /* =====================================================
       SECTION 1: TRUYỆN HAY
       Widget area: "Homepage - Truyện Hay"
    ===================================================== */
    if (is_active_sidebar('homepage-suggest-section')) {

        dynamic_sidebar('homepage-suggest-section');

    } else {

        $hot_limit = 16;
        $hot_comics_ids = $wpdb->get_col(
            "SELECT post_id
         FROM {$stats_table}
         WHERE total_display_views > 0
         ORDER BY total_display_views DESC
         LIMIT {$hot_limit}"
        );

        if (empty($hot_comics_ids)) {
            $hot_fallback_query = new WP_Query([
                'post_type' => 'nettruyen_comic',
                'post_status' => 'publish',
                'posts_per_page' => $hot_limit,
                'orderby' => 'date',
                'order' => 'DESC',
                'no_found_rows' => true,
            ]);
        } else {
            $hot_fallback_query = new WP_Query([
                'post_type' => 'nettruyen_comic',
                'post_status' => 'publish',
                'post__in' => $hot_comics_ids,
                'orderby' => 'post__in',
                'posts_per_page' => $hot_limit,
                'no_found_rows' => true,
            ]);
        }

        $hot_fallback_posts = $hot_fallback_query->posts;
        if (!is_array($hot_fallback_posts))
            $hot_fallback_posts = [];

        if (function_exists('nettruyen_sync_merge_featured_with_fallback')) {
            $hot_carousel_posts = nettruyen_sync_merge_featured_with_fallback($hot_fallback_posts, $hot_limit);
        } else {
            $hot_carousel_posts = array_slice($hot_fallback_posts, 0, $hot_limit);
        }

        if (!empty($hot_carousel_posts)):

            $post_ids_batch = wp_list_pluck($hot_carousel_posts, 'ID');
            $placeholders = implode(',', array_fill(0, count($post_ids_batch), '%d'));
            $view_stats_batch = [];

            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT post_id, total_display_views FROM {$stats_table} WHERE post_id IN ({$placeholders})",
                ...$post_ids_batch
            ));
            foreach ($rows as $row) {
                $view_stats_batch[(int) $row->post_id] = (int) $row->total_display_views;
            }
            $follow_batch = NetTruyen_Follow_Tracker::get_display_follows_batch($post_ids_batch);
            ?>

<section class="homepage-suggest layout-standard">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa fa-star"></i>
                <span>Truyện hay</span>
            </h2>
        </div>

        <div class="truyen-hay-carousel">
            <div class="swiper truyen-hay-swiper">
                <div class="swiper-wrapper">
                    <?php
                                $index = 0;
                                foreach ($hot_carousel_posts as $hot_post):
                                    setup_postdata($hot_post);
                                    $post_id = (int) $hot_post->ID;
                                    $post_title = get_the_title($post_id);
                                    $post_permalink = get_permalink($post_id);
                                    $index++;

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
                                        $latest_chapter = 'Chương ' . $latest['name'];
                                    }

                                    $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_the_modified_date('Y-m-d H:i:s');
                                    $time_ago = truyenqq_time_ago_vietnamese($updated_at);
                                    // $follow_count = (int) get_post_meta($post_id, '_nettruyen_follow_count', true);
                                    $view_count = $view_stats_batch[$post_id] ?? 0;
                                    $follow_count = $follow_batch[$post_id] ?? 0;
                                    $is_hot = ($index <= 10);
                                    ?>
                    <div class="swiper-slide">
                        <div class="comic-card">
                            <div class="comic-avatar">
                                <a href="<?php echo esc_url($post_permalink); ?>"
                                    title="<?php echo esc_attr($post_title); ?>">
                                    <img src="<?php echo esc_url($thumbnail); ?>"
                                        alt="<?php echo esc_attr($post_title . ' - Đọc truyện tranh online miễn phí tại TruyenQQ'); ?>"
                                        width="190" height="247" loading="lazy" decoding="async">
                                </a>
                                <span class="bookmark-badge" title="Theo dõi" data-post-id="<?php echo $post_id; ?>">
                                    <i class="fa fa-bookmark-o"></i>
                                </span>
                                <div class="top-notice">
                                    <span class="time-ago"><?php echo esc_html($time_ago); ?></span>
                                    <?php if ($is_hot): ?>
                                    <span class="hot-badge">Hot</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="comic-info">
                                <h3 class="comic-name">
                                    <a href="<?php echo esc_url($post_permalink); ?>"
                                        title="<?php echo esc_attr($post_title); ?>">
                                        <?php echo esc_html($post_title); ?>
                                    </a>
                                </h3>
                                <div class="comic-stats">
                                    <span class="stat-item">
                                        <i class="fa fa-bookmark"></i>
                                        <?php echo number_format($follow_count); ?>
                                    </span>
                                    <span class="stat-item">
                                        <i class="fa fa-eye"></i>
                                        <?php echo number_format($view_count); ?>
                                    </span>
                                </div>
                                <div class="latest-chapter">
                                    <a href="<?php echo esc_url($post_permalink); ?>"
                                        title="Đọc <?php echo esc_attr($latest_chapter); ?>">
                                        <?php echo esc_html($latest_chapter); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach;
                                wp_reset_postdata(); ?>
                </div>
            </div>

            <div class="swiper-nav">
                <button class="swiper-button-prev"><i class="fa fa-angle-left"></i></button>
                <button class="swiper-button-next"><i class="fa fa-angle-right"></i></button>
            </div>
        </div>
    </div>
</section>

<?php endif;
    } // end section 1
    ?>

<?php
    /* =====================================================
       SECTION 2: ĐỘC QUYỀN TRUYỆN QQ
       Widget area: "Homepage - Độc Quyền QQ"
    ===================================================== */
    if (is_active_sidebar('homepage-exclusive-section')) {

        dynamic_sidebar('homepage-exclusive-section');

    } else {

        $exclusive_comics = new WP_Query([
            'post_type' => 'nettruyen_comic',
            'post_status' => 'publish',
            'posts_per_page' => 16,
            'orderby' => 'modified',
            'order' => 'DESC',
            'no_found_rows' => true,
        ]);

        if ($exclusive_comics->have_posts()):

            $post_ids_batch = wp_list_pluck($exclusive_comics->posts, 'ID');
            $placeholders = implode(',', array_fill(0, count($post_ids_batch), '%d'));
            $view_stats_batch = [];

            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT post_id, total_display_views FROM {$stats_table} WHERE post_id IN ({$placeholders})",
                ...$post_ids_batch
            ));
            foreach ($rows as $row) {
                $view_stats_batch[(int) $row->post_id] = (int) $row->total_display_views;
            }
            $follow_batch = NetTruyen_Follow_Tracker::get_display_follows_batch($post_ids_batch);

            ?>

<section class="homepage-exclusive layout-standard">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa fa-book"></i>
                <span>Độc Quyền Truyện QQ</span>
            </h2>
        </div>

        <div class="exclusive-carousel">
            <div class="swiper exclusive-swiper">
                <div class="swiper-wrapper">
                    <?php
                                $index = 0;
                                while ($exclusive_comics->have_posts()):
                                    $exclusive_comics->the_post();
                                    $post_id = get_the_ID();
                                    $index++;

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
                                        $latest_chapter = 'Chương ' . $latest['name'];
                                    }

                                    $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_the_modified_date('Y-m-d H:i:s');
                                    $time_ago = truyenqq_time_ago_vietnamese($updated_at);
                                    // $follow_count = (int) get_post_meta($post_id, '_nettruyen_follow_count', true);
                                    $follow_count = $follow_batch[$post_id] ?? 0;
                                    $view_count = $view_stats_batch[$post_id] ?? 0;
                                    $is_hot = ($index <= 10);
                                    ?>
                    <div class="swiper-slide">
                        <div class="comic-card">
                            <div class="comic-avatar">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <img src="<?php echo esc_url($thumbnail); ?>"
                                        alt="<?php echo esc_attr(get_the_title() . ' - Đọc truyện tranh online miễn phí tại TruyenQQ'); ?>"
                                        width="190" height="247" loading="lazy" decoding="async">
                                </a>
                                <span class="bookmark-badge" title="Theo dõi" data-post-id="<?php echo $post_id; ?>">
                                    <i class="fa fa-bookmark-o"></i>
                                </span>
                                <div class="top-notice">
                                    <span class="time-ago"><?php echo esc_html($time_ago); ?></span>
                                    <?php if ($is_hot): ?>
                                    <span class="hot-badge">Hot</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="comic-info">
                                <h3 class="comic-name">
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                <div class="comic-stats">
                                    <span class="stat-item">
                                        <i class="fa fa-bookmark"></i>
                                        <?php echo number_format($follow_count); ?>
                                    </span>
                                    <span class="stat-item">
                                        <i class="fa fa-eye"></i>
                                        <?php echo number_format($view_count); ?>
                                    </span>
                                </div>
                                <div class="latest-chapter">
                                    <a href="<?php the_permalink(); ?>"
                                        title="Đọc <?php echo esc_attr($latest_chapter); ?>">
                                        <?php echo esc_html($latest_chapter); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile;
                                wp_reset_postdata(); ?>
                </div>
            </div>

            <div class="swiper-nav">
                <button class="swiper-button-prev"><i class="fa fa-angle-left"></i></button>
                <button class="swiper-button-next"><i class="fa fa-angle-right"></i></button>
            </div>
        </div>
    </div>
</section>

<?php endif;
        wp_reset_postdata();
    } // end section 2
    ?>

<?php
    /* =====================================================
       SECTION 3: TRUYỆN MỚI CẬP NHẬT
       Widget area: "Homepage - Truyện Mới Cập Nhật"
    ===================================================== */
    if (is_active_sidebar('homepage-new-update-section')) {

        dynamic_sidebar('homepage-new-update-section');

    } else {

        $new_comics = new WP_Query([
            'post_type' => 'nettruyen_comic',
            'post_status' => 'publish',
            'posts_per_page' => 42,
            'orderby' => 'modified',
            'order' => 'DESC',
            'no_found_rows' => true,
        ]);

        if ($new_comics->have_posts()):

            $post_ids_batch = wp_list_pluck($new_comics->posts, 'ID');
            $placeholders = implode(',', array_fill(0, count($post_ids_batch), '%d'));
            $view_stats_batch = [];

            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT post_id, total_display_views FROM {$stats_table} WHERE post_id IN ({$placeholders})",
                ...$post_ids_batch
            ));
            foreach ($rows as $row) {
                $view_stats_batch[(int) $row->post_id] = (int) $row->total_display_views;
            }
            $follow_batch = NetTruyen_Follow_Tracker::get_display_follows_batch($post_ids_batch);

            $hot_comic_ids = $wpdb->get_col(
                "SELECT post_id FROM {$stats_table}
             WHERE total_display_views > 0
             ORDER BY total_display_views DESC
             LIMIT 20"
            );

            $now = current_time('timestamp');
            ?>

<section class="homepage-new-update layout-standard">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <a href="<?php echo esc_url(home_url('/truyen-moi-cap-nhat')); ?>" title="Truyện mới cập nhật">
                    <i class="fa fa-cloud-download"></i>
                    <span>Truyện mới cập nhật</span>
                </a>
            </h2>
            <div class="filter-button">
                <a href="<?php echo esc_url(home_url('/tim-kiem-nang-cao')); ?>" title="Lọc truyện">
                    <button type="button">
                        <i class="fa fa-filter"></i>
                    </button>
                </a>
            </div>
        </div>

        <div class="comics-grid">
            <?php
                        while ($new_comics->have_posts()):
                            $new_comics->the_post();
                            $post_id = get_the_ID();

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

                            $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_the_modified_date('Y-m-d H:i:s');
                            $time_ago = truyenqq_time_ago_vietnamese($updated_at);
                            // $follow_count = (int) get_post_meta($post_id, '_nettruyen_follow_count', true);
                            $follow_count = $follow_batch[$post_id] ?? 0;
                            $view_count = $view_stats_batch[$post_id] ?? 0;

                            $is_hot = in_array($post_id, $hot_comic_ids);
                            $is_new = ($now - strtotime($updated_at)) <= (7 * 24 * 60 * 60);

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
            <div class="comic-item">
                <div class="comic-card">
                    <div class="comic-avatar">
                        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                            <img src="<?php echo esc_url($thumbnail); ?>"
                                alt="<?php echo esc_attr(get_the_title() . ' - Đọc truyện tranh online miễn phí tại TruyenQQ'); ?>"
                                width="190" height="247" loading="lazy" decoding="async">
                        </a>
                        <span class="bookmark-badge" title="Theo dõi" data-post-id="<?php echo $post_id; ?>">
                            <i class="fa fa-bookmark-o"></i>
                        </span>
                        <div class="top-notice">
                            <span class="time-ago"><?php echo esc_html($time_ago); ?></span>
                            <?php if ($badge_type): ?>
                            <span class="type-label <?php echo esc_attr($badge_type); ?>">
                                <?php echo esc_html($badge_text); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="comic-info">
                        <h3 class="comic-name">
                            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h3>
                        <div class="comic-stats">
                            <span class="stat-item">
                                <i class="fa fa-bookmark"></i>
                                <?php echo number_format($follow_count); ?>
                            </span>
                            <span class="stat-item">
                                <i class="fa fa-eye"></i>
                                <?php echo number_format($view_count); ?>
                            </span>
                        </div>
                        <div class="latest-chapter">
                            <a href="<?php the_permalink(); ?>" title="Đọc <?php echo esc_attr($latest_chapter); ?>">
                                <?php echo esc_html($latest_chapter); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile;
                        wp_reset_postdata(); ?>
        </div>

        <div class="view-more-section">
            <a href="<?php echo esc_url(home_url('/truyen-moi-cap-nhat')); ?>" class="view-more-btn">
                Xem thêm nhiều truyện
            </a>
        </div>

        <?php echo do_shortcode('[msc_comments]'); ?>
    </div>
</section>

<?php endif;
    } // end section 3
    ?>

<?php endif; // end homepage-sections check ?>

<?php get_footer(); ?>