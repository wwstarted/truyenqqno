<?php
/**
 * Widget: Độc Quyền Truyện QQ (Carousel)
 * Refactored từ v1 — extends TruyenQQ_Base_Comic_Widget
 *
 * @package TruyenQQ
 * @version 2.0.0
 */

if (!defined('ABSPATH'))
    exit;

class TruyenQQ_Comic_Section_Widget extends TruyenQQ_Base_Comic_Widget
{

    protected $default_count = 16;
    protected $min_count = 4;
    protected $max_count = 24;
    protected $default_query_type = 'newest';
    protected $default_icon = 'book';
    protected $default_title = 'Độc Quyền Truyện QQ';

    public function __construct()
    {
        parent::__construct(
            'truyenqq_comic_section',
            'Comic Section — Độc Quyền (TruyenQQ)',
            [
                'description' => 'Section carousel Độc Quyền QQ. Chọn kiểu query, số lượng, icon.',
                'classname' => 'truyenqq-comic-section-widget',
            ]
        );
    }

    // =========================================================
    // FORM (Backend)
    // =========================================================

    public function form($instance)
    {
        $this->render_base_form($instance, true);
    }

    // =========================================================
    // UPDATE (Save)
    // =========================================================

    public function update($new_instance, $old_instance)
    {
        return $this->sanitize_base($new_instance);
    }

    // =========================================================
    // WIDGET (Frontend)
    // =========================================================

    public function widget($args, $instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : $this->default_title;
        $icon = !empty($instance['icon']) ? $instance['icon'] : $this->default_icon;
        $posts_count = !empty($instance['posts_count']) ? (int) $instance['posts_count'] : $this->default_count;

        // ── Query ────────────────────────────────────────────
        $query_args = $this->build_query_args($instance);
        $comics = new WP_Query($query_args);

        if (!$comics->have_posts())
            return;

        // ── Unique swiper class per widget instance ──────────
        // Tránh conflict nếu 2 widget cùng loại trên trang
        $swiper_class = 'exclusive-swiper-' . $this->number;

        // ── Batch fetch view stats — 1 query thay vì N ──────
        $post_ids = wp_list_pluck($comics->posts, 'ID');
        $view_stats = $this->get_view_stats_batch($post_ids);

        $index = 0;
        ?>
<section class="homepage-exclusive">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa fa-<?php echo esc_attr($icon); ?>"></i>
                <span><?php echo esc_html($title); ?></span>
            </h2>
        </div>

        <div class="exclusive-carousel">
            <div class="swiper <?php echo esc_attr($swiper_class); ?>">
                <div class="swiper-wrapper">
                    <?php
                            while ($comics->have_posts()):
                                $comics->the_post();
                                $post_id = get_the_ID();
                                $index++;

                                $meta = $this->get_comic_meta($post_id);
                                $view_count = $view_stats[$post_id] ?? 0;
                                $is_hot = ($index <= 10);
                                ?>
                    <div class="swiper-slide">
                        <div class="comic-card">
                            <div class="comic-avatar">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <img src="<?php echo esc_url($meta['thumbnail']); ?>"
                                        alt="<?php echo esc_attr(get_the_title() . ' - TruyenQQ'); ?>" width="190"
                                        height="247" loading="lazy" decoding="async">
                                </a>
                                <span class="bookmark-badge" title="Theo dõi" data-post-id="<?php echo $post_id; ?>">
                                    <i class="fa fa-bookmark-o"></i>
                                </span>
                                <div class="top-notice">
                                    <span class="time-ago"><?php echo esc_html($meta['time_ago']); ?></span>
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
                                        <?php echo number_format($meta['follow_count']); ?>
                                    </span>
                                    <span class="stat-item">
                                        <i class="fa fa-eye"></i>
                                        <?php echo number_format($view_count); ?>
                                    </span>
                                </div>
                                <div class="latest-chapter">
                                    <a href="<?php the_permalink(); ?>"
                                        title="Đọc <?php echo esc_attr($meta['latest_chapter']); ?>">
                                        <?php echo esc_html($meta['latest_chapter']); ?>
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

<?php
        $nav_selector = '.exclusive-swiper-' . $this->number;
        ?>
<script>
(function() {
    function initExclusiveSwiper() {
        if (typeof Swiper === 'undefined') return;
        var el = document.querySelector('<?php echo esc_js($nav_selector); ?>');
        if (!el) return;
        new Swiper('<?php echo esc_js($nav_selector); ?>', {
            loop: true,
            slidesPerView: 2,
            spaceBetween: 15,
            speed: 400,
            slidesPerGroup: 1,
            navigation: {
                nextEl: el.closest('.exclusive-carousel').querySelector('.swiper-button-next'),
                prevEl: el.closest('.exclusive-carousel').querySelector('.swiper-button-prev'),
            },
            breakpoints: {
                390: {
                    slidesPerView: 2,
                    spaceBetween: 15
                },
                768: {
                    slidesPerView: 4,
                    spaceBetween: 20
                },
                1024: {
                    slidesPerView: 6,
                    spaceBetween: 20
                },
            },
            grabCursor: true,
            watchOverflow: true,
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initExclusiveSwiper);
    } else {
        initExclusiveSwiper();
    }
})();
</script>
<?php
    }
}