<?php
/**
 * TruyenQQ Base Comic Widget
 * Abstract class cung cấp shared logic cho tất cả Comic Section Widgets
 *
 * @package TruyenQQ
 * @version 2.0.0
 */

if (!defined('ABSPATH'))
    exit;

abstract class TruyenQQ_Base_Comic_Widget extends WP_Widget
{

    /** @var int Posts per page default - override trong subclass */
    protected $default_count = 16;

    /** @var int Giới hạn tối thiểu */
    protected $min_count = 4;

    /** @var int Giới hạn tối đa */
    protected $max_count = 24;

    /** @var string Query type mặc định */
    protected $default_query_type = 'newest';

    /** @var string Icon mặc định (font-awesome suffix) */
    protected $default_icon = 'book';

    /** @var string Tiêu đề mặc định */
    protected $default_title = 'Truyện Tranh';

    // =========================================================
    // QUERY BUILDER
    // =========================================================

    /**
     * Build WP_Query args từ widget instance settings
     *
     * @param array $instance Widget settings
     * @return array WP_Query args
     */
    protected function build_query_args(array $instance): array
    {
        $query_type = $instance['query_type'] ?? $this->default_query_type;
        $genre_slug = $instance['genre_slug'] ?? '';
        $posts_count = (int) ($instance['posts_count'] ?? $this->default_count);
        $posts_count = max($this->min_count, min($this->max_count, $posts_count));

        $args = [
            'post_type' => 'nettruyen_comic',
            'post_status' => 'publish',
            'posts_per_page' => $posts_count,
            'no_found_rows' => true, // Tăng performance, không cần pagination
        ];

        switch ($query_type) {
            case 'top_views':
                global $wpdb;
                $stats_table = $wpdb->prefix . 'nettruyen_view_stats';
                // ✅ Dùng prepare() đúng cách
                $top_ids = $wpdb->get_col($wpdb->prepare(
                    "SELECT post_id FROM {$stats_table}
                     WHERE total_display_views > 0
                     ORDER BY total_display_views DESC
                     LIMIT %d",
                    $posts_count
                ));
                if (!empty($top_ids)) {
                    $args['post__in'] = $top_ids;
                    $args['orderby'] = 'post__in';
                } else {
                    $args['orderby'] = 'modified';
                    $args['order'] = 'DESC';
                }
                break;

            case 'by_genre':
                $args['orderby'] = 'modified';
                $args['order'] = 'DESC';
                if (!empty($genre_slug)) {
                    $args['tax_query'] = [
                        [
                            'taxonomy' => 'nettruyen_genre',
                            'field' => 'slug',
                            'terms' => $genre_slug,
                        ]
                    ];
                }
                break;

            case 'random':
                $args['orderby'] = 'rand';
                break;

            case 'newest':
            default:
                $args['orderby'] = 'modified';
                $args['order'] = 'DESC';
                break;
        }

        return $args;
    }

    // =========================================================
    // BATCH VIEW STATS — Fix N+1 query
    // =========================================================

    /**
     * Fetch view stats cho nhiều posts trong 1 query duy nhất
     * Thay vì gọi DB từng post trong loop
     *
     * @param int[] $post_ids
     * @return array [ post_id => view_count ]
     */
    protected function get_view_stats_batch(array $post_ids): array
    {
        if (empty($post_ids))
            return [];

        global $wpdb;
        $stats_table = $wpdb->prefix . 'nettruyen_view_stats';
        $placeholders = implode(',', array_fill(0, count($post_ids), '%d'));

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id, total_display_views
                 FROM {$stats_table}
                 WHERE post_id IN ({$placeholders})",
                ...$post_ids
            )
        );

        $stats = [];
        foreach ($rows as $row) {
            $stats[(int) $row->post_id] = (int) $row->total_display_views;
        }

        return $stats;
    }

    /**
     * Fetch hot comic IDs cho badge detection
     *
     * @param int $limit
     * @return int[]
     */
    protected function get_hot_ids(int $limit = 20): array
    {
        global $wpdb;
        $stats_table = $wpdb->prefix . 'nettruyen_view_stats';

        return array_map('intval', $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$stats_table}
             WHERE total_display_views > 0
             ORDER BY total_display_views DESC
             LIMIT %d",
            $limit
        )));
    }

    // =========================================================
    // COMIC METADATA HELPER
    // =========================================================

    /**
     * Lấy metadata của một comic post
     *
     * @param int $post_id
     * @return array
     */
    protected function get_comic_meta(int $post_id): array
    {
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

        $updated_at = !empty($manifest['updated_at'])
            ? $manifest['updated_at']
            : get_the_modified_date('Y-m-d H:i:s', $post_id);

        $follow_count = (int) get_post_meta($post_id, '_nettruyen_follow_count', true);

        return [
            'thumbnail' => $thumbnail,
            'latest_chapter' => $latest_chapter,
            'updated_at' => $updated_at,
            'time_ago' => truyenqq_time_ago_vietnamese($updated_at),
            'follow_count' => $follow_count,
        ];
    }

    // =========================================================
    // AVAILABLE OPTIONS
    // =========================================================

    protected function get_query_type_options(): array
    {
        return [
            'newest' => 'Mới cập nhật',
            'top_views' => 'Top lượt xem',
            'by_genre' => 'Theo thể loại',
            'random' => 'Ngẫu nhiên',
        ];
    }

    protected function get_icon_options(): array
    {
        return [
            'star' => '⭐ fa-star',
            'book' => '📖 fa-book',
            'cloud-download' => '☁️ fa-cloud-download',
            'fire' => '🔥 fa-fire',
            'trophy' => '🏆 fa-trophy',
            'heart' => '❤️ fa-heart',
            'bolt' => '⚡ fa-bolt',
        ];
    }

    // =========================================================
    // SHARED FORM RENDERING
    // =========================================================

    /**
     * Render các field chung cho tất cả widget
     * Gọi trong form() của subclass
     *
     * @param array $instance
     * @param bool  $show_icon Có render field icon không
     */
    protected function render_base_form(array $instance, bool $show_icon = true): void
    {
        $title = $instance['title'] ?? $this->default_title;
        $query_type = $instance['query_type'] ?? $this->default_query_type;
        $genre_slug = $instance['genre_slug'] ?? '';
        $posts_count = $instance['posts_count'] ?? $this->default_count;
        $icon = $instance['icon'] ?? $this->default_icon;

        $all_genres = get_terms([
            'taxonomy' => 'nettruyen_genre',
            'hide_empty' => true,
            'orderby' => 'name',
        ]);

        // Dùng widget_id làm namespace để tránh conflict JS giữa các widget
        $widget_id = $this->id;
        $genre_row = $this->get_field_id('genre_row');
        ?>

<!-- Tiêu đề -->
<p>
    <label for="<?php echo $this->get_field_id('title'); ?>"><strong>Tiêu đề section:</strong></label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>"
        name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title); ?>">
</p>

<?php if ($show_icon): ?>
<!-- Icon -->
<p>
    <label for="<?php echo $this->get_field_id('icon'); ?>"><strong>Icon:</strong></label>
    <select class="widefat" id="<?php echo $this->get_field_id('icon'); ?>"
        name="<?php echo $this->get_field_name('icon'); ?>">
        <?php foreach ($this->get_icon_options() as $val => $label): ?>
        <option value="<?php echo esc_attr($val); ?>" <?php selected($icon, $val); ?>>
            <?php echo esc_html($label); ?>
        </option>
        <?php endforeach; ?>
    </select>
</p>
<?php endif; ?>

<!-- Kiểu query -->
<p>
    <label for="<?php echo $this->get_field_id('query_type'); ?>"><strong>Kiểu hiển thị:</strong></label>
    <select class="widefat" id="<?php echo $this->get_field_id('query_type'); ?>"
        name="<?php echo $this->get_field_name('query_type'); ?>"
        onchange="truyenqqBaseToggleGenre(this, '<?php echo esc_js($genre_row); ?>')">
        <?php foreach ($this->get_query_type_options() as $val => $label): ?>
        <option value="<?php echo esc_attr($val); ?>" <?php selected($query_type, $val); ?>>
            <?php echo esc_html($label); ?>
        </option>
        <?php endforeach; ?>
    </select>
</p>

<!-- Thể loại (ẩn/hiện theo query_type) -->
<p id="<?php echo esc_attr($genre_row); ?>" style="<?php echo ($query_type !== 'by_genre') ? 'display:none;' : ''; ?>">
    <label for="<?php echo $this->get_field_id('genre_slug'); ?>"><strong>Chọn thể loại:</strong></label>
    <select class="widefat" id="<?php echo $this->get_field_id('genre_slug'); ?>"
        name="<?php echo $this->get_field_name('genre_slug'); ?>">
        <option value="">-- Chọn thể loại --</option>
        <?php if (!is_wp_error($all_genres) && !empty($all_genres)): ?>
        <?php foreach ($all_genres as $genre): ?>
        <option value="<?php echo esc_attr($genre->slug); ?>" <?php selected($genre_slug, $genre->slug); ?>>
            <?php echo esc_html($genre->name); ?>
            (
            <?php echo (int) $genre->count; ?>)
        </option>
        <?php endforeach; ?>
        <?php endif; ?>
    </select>
</p>

<!-- Số lượng truyện -->
<p>
    <label for="<?php echo $this->get_field_id('posts_count'); ?>">
        <strong>Số lượng truyện</strong>
        <small>(
            <?php echo $this->min_count; ?>–
            <?php echo $this->max_count; ?>):
        </small>
    </label>
    <input class="widefat" type="number" min="<?php echo $this->min_count; ?>" max="<?php echo $this->max_count; ?>"
        id="<?php echo $this->get_field_id('posts_count'); ?>"
        name="<?php echo $this->get_field_name('posts_count'); ?>" value="<?php echo esc_attr($posts_count); ?>">
</p>

<?php
        // JS toggle — dùng tên function chung, không conflict vì truyền ID cụ thể
        static $js_printed = false;
        if (!$js_printed):
            $js_printed = true;
            ?>
<script>
function truyenqqBaseToggleGenre(sel, rowId) {
    var row = document.getElementById(rowId);
    if (row) row.style.display = (sel.value === 'by_genre') ? 'block' : 'none';
}
</script>
<?php endif;
    }

    // =========================================================
    // SHARED SANITIZATION
    // =========================================================

    /**
     * Sanitize các field chung
     * Subclass gọi hàm này rồi append thêm field riêng
     *
     * @param array $new_instance
     * @return array
     */
    protected function sanitize_base(array $new_instance): array
    {
        $valid_icons = array_keys($this->get_icon_options());
        $valid_query_types = array_keys($this->get_query_type_options());

        $icon = sanitize_text_field($new_instance['icon'] ?? $this->default_icon);
        if (!in_array($icon, $valid_icons))
            $icon = $this->default_icon;

        $query_type = sanitize_text_field($new_instance['query_type'] ?? $this->default_query_type);
        if (!in_array($query_type, $valid_query_types))
            $query_type = $this->default_query_type;

        $posts_count = absint($new_instance['posts_count'] ?? $this->default_count);
        $posts_count = max($this->min_count, min($this->max_count, $posts_count));

        return [
            'title' => sanitize_text_field($new_instance['title'] ?? $this->default_title),
            'icon' => $icon,
            'query_type' => $query_type,
            'genre_slug' => sanitize_text_field($new_instance['genre_slug'] ?? ''),
            'posts_count' => $posts_count,
        ];
    }

    // =========================================================
    // ABSTRACT: subclass phải implement
    // =========================================================

    // abstract public function widget($args, $instance);
    // abstract public function form($instance);
    // abstract public function update($new_instance, $old_instance);

}