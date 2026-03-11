<?php
/**
 * Widget: Truyện Mới Cập Nhật (Grid)
 * Extends TruyenQQ_Base_Comic_Widget
 *
 * @package TruyenQQ
 * @version 2.0.0
 */

if (!defined('ABSPATH'))
    exit;

class TruyenQQ_New_Update_Widget extends TruyenQQ_Base_Comic_Widget
{
    protected $default_count = 42;
    protected $min_count = 6;
    protected $max_count = 60;
    protected $default_query_type = 'newest';
    protected $default_icon = 'cloud-download';
    protected $default_title = 'Truyện mới cập nhật';

    public function __construct()
    {
        parent::__construct(
            'truyenqq_new_update',
            'Comic Section — Mới Cập Nhật (TruyenQQ)',
            array(
                'description' => 'Section grid Truyện Mới Cập Nhật. Hỗ trợ filter button và view more.',
                'classname' => 'truyenqq-new-update-widget',
            )
        );
    }

    // =========================================================
    // FORM
    // =========================================================

    public function form($instance)
    {
        $this->render_base_form($instance, true);

        $show_filter = isset($instance['show_filter']) ? (bool) $instance['show_filter'] : true;
        $show_view_more = isset($instance['show_view_more']) ? (bool) $instance['show_view_more'] : true;
        $view_more_url = $instance['view_more_url'] ?? '/truyen-moi-cap-nhat';
        ?>

<p>
    <input type="checkbox" id="<?php echo $this->get_field_id('show_filter'); ?>"
        name="<?php echo $this->get_field_name('show_filter'); ?>" value="1" <?php checked($show_filter); ?>>
    <label for="<?php echo $this->get_field_id('show_filter'); ?>">
        <strong>Hiện nút lọc truyện</strong>
    </label>
</p>

<p>
    <input type="checkbox" id="<?php echo $this->get_field_id('show_view_more'); ?>"
        name="<?php echo $this->get_field_name('show_view_more'); ?>" value="1" <?php checked($show_view_more); ?>>
    <label for="<?php echo $this->get_field_id('show_view_more'); ?>">
        <strong>Hiện nút "Xem thêm"</strong>
    </label>
</p>

<p>
    <label for="<?php echo $this->get_field_id('view_more_url'); ?>">
        <strong>URL "Xem thêm":</strong>
    </label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('view_more_url'); ?>"
        name="<?php echo $this->get_field_name('view_more_url'); ?>" value="<?php echo esc_attr($view_more_url); ?>">
    <small>Ví dụ: /truyen-moi-cap-nhat</small>
</p>
<?php
    }

    // =========================================================
    // UPDATE
    // =========================================================

    public function update($new_instance, $old_instance)
    {
        $instance = $this->sanitize_base($new_instance);

        $instance['show_filter'] = !empty($new_instance['show_filter']) ? 1 : 0;
        $instance['show_view_more'] = !empty($new_instance['show_view_more']) ? 1 : 0;
        $instance['view_more_url'] = sanitize_text_field($new_instance['view_more_url'] ?? '/truyen-moi-cap-nhat');

        return $instance;
    }

    // =========================================================
    // WIDGET
    // =========================================================

    public function widget($args, $instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : $this->default_title;
        $icon = !empty($instance['icon']) ? $instance['icon'] : $this->default_icon;
        $show_filter = isset($instance['show_filter']) ? (bool) $instance['show_filter'] : true;
        $show_view_more = isset($instance['show_view_more']) ? (bool) $instance['show_view_more'] : true;
        $view_more_url = !empty($instance['view_more_url']) ? $instance['view_more_url'] : '/truyen-moi-cap-nhat';

        if (strpos($view_more_url, 'http') !== 0) {
            $view_more_url = home_url($view_more_url);
        }

        $query_args = $this->build_query_args($instance);
        $comics = new WP_Query($query_args);

        if (!$comics->have_posts())
            return;

        $post_ids = wp_list_pluck($comics->posts, 'ID');
        $view_stats = $this->get_view_stats_batch($post_ids);
        $hot_ids = $this->get_hot_ids(20);
        $now = current_time('timestamp');
        ?>

<section class="homepage-new-update">
    <div class="container">

        <div class="section-header">
            <h2 class="section-title">
                <a href="<?php echo esc_url($view_more_url); ?>" title="<?php echo esc_attr($title); ?>">
                    <i class="fa fa-<?php echo esc_attr($icon); ?>"></i>
                    <span><?php echo esc_html($title); ?></span>
                </a>
            </h2>
            <?php if ($show_filter): ?>
            <div class="filter-button">
                <a href="<?php echo esc_url(home_url('/tim-kiem-nang-cao')); ?>" title="Lọc truyện">
                    <button type="button"><i class="fa fa-filter"></i></button>
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="comics-grid">
            <?php
                    while ($comics->have_posts()):
                        $comics->the_post();
                        $post_id = get_the_ID();
                        $meta = $this->get_comic_meta($post_id);
                        $view_count = $view_stats[$post_id] ?? 0;

                        $is_hot = in_array($post_id, $hot_ids);
                        $is_new = ($now - strtotime($meta['updated_at'])) <= (7 * 24 * 60 * 60);

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
                            <img src="<?php echo esc_url($meta['thumbnail']); ?>"
                                alt="<?php echo esc_attr(get_the_title() . ' - TruyenQQ'); ?>" width="190" height="247"
                                loading="lazy" decoding="async">
                        </a>
                        <span class="bookmark-badge" title="Theo dõi" data-post-id="<?php echo $post_id; ?>">
                            <i class="fa fa-bookmark-o"></i>
                        </span>
                        <div class="top-notice">
                            <span class="time-ago"><?php echo esc_html($meta['time_ago']); ?></span>
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

        <?php if ($show_view_more): ?>
        <div class="view-more-section">
            <a href="<?php echo esc_url($view_more_url); ?>" class="view-more-btn">
                Xem thêm nhiều truyện
            </a>
        </div>
        <?php endif; ?>

        <?php echo do_shortcode('[msc_comments]'); ?>

    </div>
</section>
<?php
    }
}