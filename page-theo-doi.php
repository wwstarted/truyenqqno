<?php
/**
 * Template Name: Truyện Theo Dõi
 * 
 * @package TruyenQQ
 * @version 1.0.1
 * ✅ UPDATED: Added comic-stats to Độc Quyền section
 */

if (!is_user_logged_in()) {
    wp_redirect(home_url('/dang-nhap?redirect_to=' . urlencode($_SERVER['REQUEST_URI'])));
    exit;
}

get_header();

/**
 * Section: Độc Quyền Truyện QQ
 * ✅ UPDATED: Added follow count + view count stats
 */
$args = array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => 16,
    'orderby' => 'rand',
    'order' => 'DESC'
);

$exclusive_comics = new WP_Query($args);

// ✅ NEW: Khởi tạo $wpdb và stats table
global $wpdb;
$stats_table = $wpdb->prefix . 'nettruyen_view_stats';
?>

<?php if ($exclusive_comics->have_posts()): ?>
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

                            // Thumbnail
                            $thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);
                            if (empty($thumbnail)) {
                                $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
                            }
                            if (empty($thumbnail)) {
                                $thumbnail = 'https://via.placeholder.com/190x247?text=No+Image';
                            }

                            // Chapter info
                            $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
                            $manifest = !empty($manifest_json) ? json_decode($manifest_json, true) : null;

                            $latest_chapter = 'Đang cập nhật';
                            if (!empty($manifest['chapters'])) {
                                $chapters = $manifest['chapters'];
                                $latest = end($chapters);
                                $latest_chapter = 'Chương ' . $latest['name'];
                            }

                            // Time ago
                            $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_the_modified_time('U');
                            $time_ago = human_time_diff(strtotime($updated_at), current_time('timestamp')) . ' trước';

                            // ✅ NEW: Get follow count
                            $follow_count = get_post_meta($post_id, '_nettruyen_follow_count', true);
                            if (empty($follow_count)) {
                                $follow_count = 0;
                            }

                            // ✅ NEW: Get view count from database
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

                            // Format numbers
                            $follow_count_formatted = number_format($follow_count);
                            $view_count_formatted = number_format($view_count);

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

                                <!-- Bookmark Button -->
                                <span class="bookmark-badge" title="Theo dõi" data-post-id="<?php echo $post_id; ?>">
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

                                <!-- ✅ NEW: Stats -->
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
<?php endif; ?>

<!-- Bookmarks Section -->
<div class="bookmarks-page reading-history-page">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <h2 class="section-title">
                <a href="/theo-doi" title="Truyện Theo Dõi">
                    <i class="fa fa-bookmark"></i>
                    <span>Truyện Theo Dõi</span>
                </a>
            </h2>
            <div class="bookmark-actions">
                <button type="button" class="btn-clear-bookmarks" id="clear-all-bookmarks">
                    <i class="fa fa-trash-o"></i> Xóa Tất Cả
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div class="loading-state" id="bookmarks-loading">
            <i class="fa fa-spinner fa-spin"></i>
            <p>Đang tải truyện theo dõi...</p>
        </div>

        <!-- Empty State -->
        <div class="empty-state" id="bookmarks-empty" style="display: none;">
            <i class="fa fa-bookmark-o"></i>
            <h3>Chưa có truyện theo dõi</h3>
            <p>Bắt đầu theo dõi truyện yêu thích của bạn ngay!</p>
            <a href="<?php echo home_url(); ?>" class="btn btn-primary">
                <i class="fa fa-home"></i> Về Trang Chủ
            </a>
        </div>

        <!-- Bookmarks Grid -->
        <div class="comics-grid" id="bookmarks-grid" style="display: none;">
            <!-- Items will be loaded here by JavaScript -->
        </div>
    </div>
</div>

<?php get_footer(); ?>