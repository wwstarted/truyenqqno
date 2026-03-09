<?php
/**
 * Widget: Truyện Hay (Hot Comics Carousel)
 * Section carousel hiển thị truyện hot/nổi bật
 * Default query: top_views
 *
 * @package TruyenQQ
 * @version 2.0.0
 */

if (!defined('ABSPATH'))
    exit;

class TruyenQQ_Hot_Comics_Widget extends TruyenQQ_Base_Comic_Widget
{

    protected $default_count = 16;
    protected $min_count = 4;
    protected $max_count = 24;
    protected $default_query_type = 'top_views'; // Hot section mặc định theo view
    protected $default_icon = 'star';
    protected $default_title = 'Truyện Hay';

    public function __construct()
    {
        parent::__construct(
            'truyenqq_hot_comics',
            'Comic Section — Truyện Hay (TruyenQQ)',
            [
                'description' => 'Section carousel Truyện Hay. Mặc định hiển thị top lượt xem. Hỗ trợ tất cả kiểu query.',
                'classname' => 'truyenqq-hot-comics-widget',
            ]
        );
    }

    // =========================================================
    // FORM
    // =========================================================

    public function form($instance)
    {
        $this->render_base_form($instance, true);
    }

    // =========================================================
    // UPDATE
    // =========================================================

    public function update($new_instance, $old_instance)
    {
        return $this->sanitize_base($new_instance);
    }

    // =========================================================
    // WIDGET
    // =========================================================

    public function widget($args, $instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : $this->default_title;
        $icon = !empty($instance['icon']) ? $instance['icon'] : $this->default_icon;

        // ── Query ────────────────────────────────────────────
        $query_args = $this->build_query_args($instance);
        $hot_comics = new WP_Query($query_args);

        if (!$hot_comics->have_posts())
            return;

        // ── Unique swiper class ──────────────────────────────
        $swiper_class = 'truyen-hay-swiper-' . $this->number;

        // ── Batch view stats ─────────────────────────────────
        $post_ids = wp_list_pluck($hot_comics->posts, 'ID');
        $view_stats = $this->get_view_stats_batch($post_ids);

        $index = 0;
        ?>
<section class="homepage-suggest">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa fa-<?php echo esc_attr($icon); ?>"></i>
                <span>
                    <?php echo esc_html($title); ?>
                </span>
            </h2>
        </div>

        <div class="truyen-hay-carousel">
            <div class="swiper <?php echo esc_attr($swiper_class); ?>">
                <div class="swiper-wrapper">
                    <?php
                            while ($hot_comics->have_posts()):
                                $hot_comics->the_post();
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
                                    <span class="time-ago">
                                        <?php echo esc_html($meta['time_ago']); ?>
                                    </span>
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
        $nav_selector = '.truyen-hay-swiper-' . $this->number;
        ?>
<script>
(function() {
    function initHotSwiper() {
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
                nextEl: el.closest('.truyen-hay-carousel').querySelector('.swiper-button-next'),
                prevEl: el.closest('.truyen-hay-carousel').querySelector('.swiper-button-prev'),
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
        document.addEventListener('DOMContentLoaded', initHotSwiper);
    } else {
        initHotSwiper();
    }
})();
</script>
<?php
    }
}