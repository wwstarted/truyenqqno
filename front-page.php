<?php get_header(); ?>

<?php

/**
 * Section: Truyện Hay (Hot Comics Carousel)
 * Hiển thị 16 truyện hot nhất theo view count
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

$args = array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => 16,
    'orderby' => 'meta_value_num',
    'meta_key' => '_nettruyen_total_views',
    'order' => 'DESC'
);

global $wpdb;
$stats_table = $wpdb->prefix . 'nettruyen_view_stats';

$hot_comics_ids = $wpdb->get_col(
    "SELECT post_id 
    FROM {$stats_table} 
    WHERE total_display_views > 0 
    ORDER BY total_display_views DESC 
    LIMIT 16"
);

if (empty($hot_comics_ids)) {
    $args = array(
        'post_type' => 'nettruyen_comic',
        'post_status' => 'publish',
        'posts_per_page' => 16,
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $hot_comics = new WP_Query($args);
} else {
    $args = array(
        'post_type' => 'nettruyen_comic',
        'post_status' => 'publish',
        'post__in' => $hot_comics_ids,
        'orderby' => 'post__in',
        'posts_per_page' => 16
    );
    $hot_comics = new WP_Query($args);
}

if (!$hot_comics->have_posts()) {
    return;
}
?>

<section class="homepage-suggest">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa fa-star"></i>
                <span>Truyện hay</span>
            </h2>
        </div>

        <!-- Swiper Carousel -->
        <div class="truyen-hay-carousel">
            <div class="swiper truyen-hay-swiper">
                <div class="swiper-wrapper">
                    <?php
                    $index = 0;
                    while ($hot_comics->have_posts()):
                        $hot_comics->the_post();
                        $post_id = get_the_ID();
                        $index++;

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
                            $latest_chapter = 'Chương ' . $latest['name'];
                        }

                        $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_the_modified_time('U');
                        $time_ago = human_time_diff(strtotime($updated_at), current_time('timestamp')) . ' trước';

                        $is_hot = ($index <= 10);
                        ?>

                    <div class="swiper-slide">
                        <div class="comic-card">
                            <!-- Thumbnail -->
                            <div class="comic-avatar">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?>"
                                        loading="lazy">
                                </a>

                                <!-- Bookmark Button (UI Only) -->
                                <span class="bookmark-badge" title="Theo dõi">
                                    <i class="fa fa-bookmark-o"></i>
                                </span>

                                <!-- Top Notice: Time + Hot Badge -->
                                <div class="top-notice">
                                    <span class="time-ago">
                                        <?php echo esc_html($time_ago); ?>
                                    </span>
                                    <?php if ($is_hot): ?>
                                    <span class="hot-badge">Hot</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Comic Info -->
                            <div class="comic-info">
                                <h3 class="comic-name">
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
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

            <!-- Navigation Buttons -->
            <div class="swiper-nav">
                <button class="swiper-button-prev">
                    <i class="fa fa-angle-left"></i>
                </button>
                <button class="swiper-button-next">
                    <i class="fa fa-angle-right"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<?php
/**
 * Section: Độc Quyền Truyện QQ
 * Hiển thị 16 truyện ngẫu nhiên (sẽ update logic sau)
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

$args = array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => 16,
    'orderby' => 'rand',
    'order' => 'DESC'
);

$exclusive_comics = new WP_Query($args);

if (!$exclusive_comics->have_posts()) {
    return;
}
?>

<section class="homepage-exclusive">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <h2 class="section-title">
                <a href="/truyen-dich-qq.html" title="Độc Quyền Truyện QQ">
                    <i class="fa fa-book"></i>
                    <span>Độc Quyền Truyện QQ</span>
                </a>
            </h2>
        </div>

        <!-- Swiper Carousel -->
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
                            $latest_chapter = 'Chương ' . $latest['name'];
                        }

                        $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_the_modified_time('U');
                        $time_ago = human_time_diff(strtotime($updated_at), current_time('timestamp')) . ' trước';

                        $is_hot = ($index <= 10);
                        ?>

                    <div class="swiper-slide">
                        <div class="comic-card">
                            <!-- Thumbnail -->
                            <div class="comic-avatar">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?>"
                                        loading="lazy">
                                </a>

                                <!-- Bookmark Button (UI Only) -->
                                <span class="bookmark-badge" title="Theo dõi">
                                    <i class="fa fa-bookmark-o"></i>
                                </span>

                                <!-- Top Notice: Time + Hot Badge -->
                                <div class="top-notice">
                                    <span class="time-ago">
                                        <?php echo esc_html($time_ago); ?>
                                    </span>
                                    <?php if ($is_hot): ?>
                                    <span class="hot-badge">Hot</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Comic Info -->
                            <div class="comic-info">
                                <h3 class="comic-name">
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
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

            <!-- Navigation Buttons -->
            <div class="swiper-nav">
                <button class="swiper-button-prev">
                    <i class="fa fa-angle-left"></i>
                </button>
                <button class="swiper-button-next">
                    <i class="fa fa-angle-right"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<?php
/**
 * Section: Truyện Mới Cập Nhật
 * Hiển thị 42 truyện mới nhất theo ngày cập nhật
 * Grid layout: Desktop (6x7), Tablet (4x11), Mobile (2x21)
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

$args = array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => 42,
    'orderby' => 'modified',
    'order' => 'DESC'
);

$new_comics = new WP_Query($args);

if (!$new_comics->have_posts()) {
    return;
}

global $wpdb;
$stats_table = $wpdb->prefix . 'nettruyen_view_stats';
$hot_comic_ids = $wpdb->get_col(
    "SELECT post_id 
    FROM {$stats_table} 
    WHERE total_display_views > 0 
    ORDER BY total_display_views DESC 
    LIMIT 20"
);
?>

<section class="homepage-new-update">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <h2 class="section-title">
                <a href="/truyen-moi-cap-nhat.html" title="Truyện mới cập nhật">
                    <i class="fa fa-cloud-download"></i>
                    <span>Truyện mới cập nhật</span>
                </a>
            </h2>
            <div class="filter-button">
                <a href="#" title="Lọc truyện">
                    <button type="button">
                        <i class="fa fa-filter"></i>
                    </button>
                </a>
            </div>
        </div>

        <!-- Grid Layout -->
        <div class="comics-grid">
            <?php
            $index = 0;
            while ($new_comics->have_posts()):
                $new_comics->the_post();
                $post_id = get_the_ID();
                $index++;

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

            <div class="comic-item">
                <div class="comic-card">
                    <!-- Thumbnail -->
                    <div class="comic-avatar">
                        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?>"
                                loading="lazy">
                        </a>

                        <!-- Bookmark Button -->
                        <span class="bookmark-badge" title="Theo dõi">
                            <i class="fa fa-bookmark-o"></i>
                        </span>

                        <!-- Top Notice: Time + Badge -->
                        <div class="top-notice">
                            <span class="time-ago">
                                <?php echo esc_html($time_ago); ?>
                            </span>
                            <?php if ($badge_type): ?>
                            <span class="type-label <?php echo esc_attr($badge_type); ?>">
                                <?php echo esc_html($badge_text); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Comic Info -->
                    <div class="comic-info">
                        <h3 class="comic-name">
                            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h3>

                        <!-- Stats -->
                        <div class="comic-stats">
                            <span class="stat-item">
                                <i class="fa fa-bookmark"></i>
                                <?php echo esc_html($follow_count_formatted); ?>
                            </span>
                            <span class="stat-item">
                                <i class="fa fa-eye"></i>
                                <?php echo esc_html($view_count_formatted); ?>
                            </span>
                        </div>

                        <!-- Latest Chapter -->
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

        <!-- View More Button -->
        <div class="view-more-section">
            <a href="<?php echo esc_url(home_url('/truyen-moi-cap-nhat')); ?>" class="view-more-btn">
                Xem thêm nhiều truyện
            </a>
        </div>
    </div>
</section>



<?php get_footer(); ?>