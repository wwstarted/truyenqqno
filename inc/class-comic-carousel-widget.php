<?php
/**
 * Widget: Comic Section — Carousel (Merged)
 * Gộp "Truyện Hay" và "Độc Quyền QQ" thành 1 widget đa năng.
 * Hỗ trợ 3 layout, 2 màu sắc section, tất cả kiểu query.
 *
 * Backward-compatible aliases ở cuối file:
 *   - TruyenQQ_Hot_Comics_Widget     (id: truyenqq_hot_comics)
 *   - TruyenQQ_Comic_Section_Widget  (id: truyenqq_comic_section)
 * → Các widget instance cũ trong DB hoạt động bình thường,
 *   tự động được nâng cấp lên features mới.
 *
 * @package TruyenQQ
 * @version 2.1.0
 */

if (!defined('ABSPATH'))
    exit;

// ============================================================
// MAIN MERGED WIDGET CLASS
// ============================================================

class TruyenQQ_Comic_Carousel_Widget extends TruyenQQ_Base_Comic_Widget
{

    protected $default_count = 16;
    protected $min_count = 4;
    protected $max_count = 24;
    protected $default_query_type = 'top_views';
    protected $default_icon = 'star';
    protected $default_title = 'Truyện Hay';
    protected $default_section_style = 'suggest';   // suggest | exclusive
    protected $default_layout = 'standard';  // standard | compact | large

    public function __construct()
    {
        parent::__construct(
            'truyenqq_comic_carousel',
            'Comic Section — Carousel (TruyenQQ)',
            [
                'description' => 'Section carousel đa năng. Chọn layout (standard/compact/large), màu sắc (đỏ/xanh), kiểu query.',
                'classname' => 'truyenqq-comic-carousel-widget',
            ]
        );
    }

    // ─── Section style options ───────────────────────────────

    protected function get_section_style_options(): array
    {
        return [
            'suggest' => '🔴 Đỏ — Truyện Hay style',
            'exclusive' => '🔵 Xanh — Độc Quyền style',
        ];
    }

    /** CSS class cho thẻ <section> */
    protected function get_section_css_class(string $style): string
    {
        return $style === 'exclusive' ? 'homepage-exclusive' : 'homepage-suggest';
    }

    /** CSS class cho wrapper bên trong (carousel div) */
    protected function get_carousel_wrapper_class(string $style): string
    {
        return $style === 'exclusive' ? 'exclusive-carousel' : 'truyen-hay-carousel';
    }

    // ─── Form ─────────────────────────────────────────────────

    public function form($instance)
    {
        // Separator style
        echo '<hr style="border-color:#ddd;margin:12px 0 8px">';
        echo '<p style="font-size:11px;color:#888;margin:0 0 8px;text-transform:uppercase;letter-spacing:.5px">Nội dung</p>';
        $this->render_base_form($instance, true);

        echo '<hr style="border-color:#ddd;margin:12px 0 8px">';
        echo '<p style="font-size:11px;color:#888;margin:0 0 8px;text-transform:uppercase;letter-spacing:.5px">Giao diện</p>';
        $this->render_layout_field($instance, 'carousel');
        $this->render_section_style_field($instance);
    }

    protected function render_section_style_field(array $instance): void
    {
        $style = $instance['section_style'] ?? $this->default_section_style;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('section_style'); ?>"><strong>Màu sắc section:</strong></label>
            <select class="widefat" id="<?php echo $this->get_field_id('section_style'); ?>"
                name="<?php echo $this->get_field_name('section_style'); ?>">
                <?php foreach ($this->get_section_style_options() as $val => $label): ?>
                    <option value="<?php echo esc_attr($val); ?>" <?php selected($style, $val); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    // ─── Update ───────────────────────────────────────────────

    public function update($new_instance, $old_instance)
    {
        $instance = $this->sanitize_base($new_instance);

        $valid_styles = array_keys($this->get_section_style_options());
        $style = sanitize_text_field($new_instance['section_style'] ?? $this->default_section_style);
        $instance['section_style'] = in_array($style, $valid_styles, true) ? $style : $this->default_section_style;
        $instance['layout'] = $this->sanitize_layout($new_instance['layout'] ?? 'standard', 'carousel');

        return $instance;
    }

    // ─── Widget (frontend) ────────────────────────────────────

    public function widget($args, $instance)
    {
        $title = $instance['title'] ?? $this->default_title;
        $icon = $instance['icon'] ?? $this->default_icon;
        $layout = $instance['layout'] ?? $this->default_layout;
        $section_style = $instance['section_style'] ?? $this->default_section_style;

        $section_class = $this->get_section_css_class($section_style);
        $carousel_class = $this->get_carousel_wrapper_class($section_style);
        $swiper_id = $section_class . '-swiper-' . $this->number;
        $swiper_cfg = $this->get_swiper_config($layout);

        // Query
        $query_args = $this->build_query_args($instance);
        $comics = new WP_Query($query_args);
        if (!$comics->have_posts())
            return;

        // Batch stats
        $post_ids = wp_list_pluck($comics->posts, 'ID');
        $view_stats = $this->get_view_stats_batch($post_ids);

        $index = 0;
        ?>

        <section class="<?php echo esc_attr($section_class); ?> layout-<?php echo esc_attr($layout); ?>">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fa fa-<?php echo esc_attr($icon); ?>"></i>
                        <span><?php echo esc_html($title); ?></span>
                    </h2>
                </div>

                <div class="<?php echo esc_attr($carousel_class); ?>">
                    <div class="swiper <?php echo esc_attr($swiper_id); ?>">
                        <div class="swiper-wrapper">
                            <?php while ($comics->have_posts()):
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
                                            <?php if ($layout !== 'compact'): ?>
                                                <span class="bookmark-badge" title="Theo dõi" data-post-id="<?php echo $post_id; ?>">
                                                    <i class="fa fa-bookmark-o"></i>
                                                </span>
                                            <?php endif; ?>
                                            <div class="top-notice">
                                                <span class="time-ago"><?php echo esc_html($meta['time_ago']); ?></span>
                                                <?php if ($is_hot): ?>
                                                    <span class="hot-badge">Hot</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($layout === 'large' && $index <= 3): ?>
                                                <div class="rank-badge rank-<?php echo $index; ?>"><?php echo $index; ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="comic-info">
                                            <h3 class="comic-name">
                                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                                    <?php the_title(); ?>
                                                </a>
                                            </h3>
                                            <?php if ($layout !== 'compact'): ?>
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
                                            <?php endif; ?>
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

        <script>
            (function () {
                var cfg = {
                    m: <?php echo (int) $swiper_cfg['m']; ?>,
                    t: <?php echo (int) $swiper_cfg['t']; ?>,
                    d: <?php echo (int) $swiper_cfg['d']; ?>,
                    g: <?php echo (int) $swiper_cfg['gap']; ?>
                };

                function init() {
                    if (typeof Swiper === 'undefined') return;
                    var el = document.querySelector('.<?php echo esc_js($swiper_id); ?>');
                    if (!el) return;
                    var wrap = el.closest('.<?php echo esc_js($carousel_class); ?>');
                    new Swiper(el, {
                        loop: true,
                        slidesPerView: cfg.m,
                        spaceBetween: cfg.g,
                        speed: 400,
                        slidesPerGroup: 1,
                        navigation: {
                            nextEl: wrap ? wrap.querySelector('.swiper-button-next') : null,
                            prevEl: wrap ? wrap.querySelector('.swiper-button-prev') : null,
                        },
                        breakpoints: {
                            390: {
                                slidesPerView: cfg.m,
                                spaceBetween: cfg.g
                            },
                            768: {
                                slidesPerView: cfg.t,
                                spaceBetween: cfg.g + 5
                            },
                            1024: {
                                slidesPerView: cfg.d,
                                spaceBetween: cfg.g + 5
                            },
                        },
                        grabCursor: true,
                        watchOverflow: true,
                    });
                }
                document.readyState === 'loading' ?
                    document.addEventListener('DOMContentLoaded', init) :
                    init();
            })();
        </script>

        <?php
    }
}

// ============================================================
// BACKWARD-COMPATIBLE ALIASES
// ============================================================
// Các widget instance cũ trong database dùng ID
// 'truyenqq_hot_comics' và 'truyenqq_comic_section'.
// Kế thừa từ lớp mới → tự động có đầy đủ features,
// dữ liệu cũ không bị mất.
// ============================================================

/**
 * Alias: Truyện Hay (ID cũ: truyenqq_hot_comics)
 * Kế thừa TruyenQQ_Comic_Carousel_Widget, override ID + defaults.
 */
class TruyenQQ_Hot_Comics_Widget extends TruyenQQ_Comic_Carousel_Widget
{

    public function __construct()
    {
        // Set defaults trước khi WP_Widget::__construct
        $this->default_query_type = 'top_views';
        $this->default_icon = 'star';
        $this->default_title = 'Truyện Hay';
        $this->default_section_style = 'suggest';

        // Gọi thẳng WP_Widget để giữ id_base cũ
        WP_Widget::__construct(
            'truyenqq_hot_comics',
            'Comic Section — Truyện Hay (TruyenQQ)',
            [
                'description' => 'Section carousel Truyện Hay — v2.1: layout + màu sắc. Mặc định top lượt xem.',
                'classname' => 'truyenqq-hot-comics-widget',
            ]
        );
    }
}

/**
 * Alias: Độc Quyền QQ (ID cũ: truyenqq_comic_section)
 * Kế thừa TruyenQQ_Comic_Carousel_Widget, override ID + defaults.
 */
class TruyenQQ_Comic_Section_Widget extends TruyenQQ_Comic_Carousel_Widget
{

    public function __construct()
    {
        $this->default_query_type = 'newest';
        $this->default_icon = 'book';
        $this->default_title = 'Độc Quyền Truyện QQ';
        $this->default_section_style = 'exclusive';

        WP_Widget::__construct(
            'truyenqq_comic_section',
            'Comic Section — Độc Quyền QQ (TruyenQQ)',
            [
                'description' => 'Section carousel Độc Quyền QQ — v2.1: layout + màu sắc. Mặc định mới cập nhật.',
                'classname' => 'truyenqq-comic-section-widget',
            ]
        );
    }
}