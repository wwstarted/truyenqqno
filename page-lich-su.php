<?php
/**
 * Template Name: Lịch Sử Đọc Truyện
 * 
 * @package TruyenQQ
 * @version 1.0.1
 * ✅ Updated: Grid layout like homepage-new-update
 */

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/dang-nhap?redirect_to=' . urlencode($_SERVER['REQUEST_URI'])));
    exit;
}

get_header();

/**
 * Section: Độc Quyền Truyện QQ (Reuse from home)
 * Hiển thị 16 truyện ngẫu nhiên
 */
$args = array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => 16,
    'orderby' => 'rand',
    'order' => 'DESC'
);

$exclusive_comics = new WP_Query($args);
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
                                <?php truyenqq_render_bookmark_badge($post_id); ?>

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
<?php endif; ?>

<!-- Reading History Section -->
<div class="reading-history-page">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <h2 class="section-title">
                <a href="/lich-su" title="Lịch Sử Đọc Truyện">
                    <i class="fa fa-history"></i>
                    <span>Lịch Sử Đọc Truyện</span>
                </a>
            </h2>
            <div class="history-actions">
                <button type="button" class="btn-clear-history" id="clear-all-history">
                    <i class="fa fa-trash-o"></i> Xóa Tất Cả
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div class="loading-state" id="history-loading">
            <i class="fa fa-spinner fa-spin"></i>
            <p>Đang tải lịch sử...</p>
        </div>

        <!-- Empty State -->
        <div class="empty-state" id="history-empty" style="display: none;">
            <i class="fa fa-book"></i>
            <h3>Chưa có lịch sử đọc truyện</h3>
            <p>Bắt đầu đọc truyện yêu thích của bạn ngay!</p>
            <a href="<?php echo home_url(); ?>" class="btn btn-primary">
                <i class="fa fa-home"></i> Về Trang Chủ
            </a>
        </div>

        <!-- History Grid (giống homepage-new-update) -->
        <div class="comics-grid" id="history-grid" style="display: none;">
            <!-- Items will be loaded here by JavaScript -->
        </div>
    </div>
</div>

<?php get_footer(); ?>