<?php
/**
 * REST API for Top Comics & Listings
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

class NetTruyen_Top_Comics_API
{
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes()
    {

        register_rest_route('nettruyen/v1', '/comics/top-ngay', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_top_daily'),
            'permission_callback' => '__return_true'
        ));


        register_rest_route('nettruyen/v1', '/comics/top-tuan', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_top_weekly'),
            'permission_callback' => '__return_true'
        ));


        register_rest_route('nettruyen/v1', '/comics/top-thang', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_top_monthly'),
            'permission_callback' => '__return_true'
        ));


        register_rest_route('nettruyen/v1', '/comics/yeu-thich', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_yeu_thich'),
            'permission_callback' => '__return_true'
        ));


        register_rest_route('nettruyen/v1', '/comics/truyen-moi', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_truyen_moi'),
            'permission_callback' => '__return_true'
        ));


        register_rest_route('nettruyen/v1', '/comics/truyen-full', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_truyen_full'),
            'permission_callback' => '__return_true'
        ));


        register_rest_route('nettruyen/v1', '/comics/ngau-nhien', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_ngau_nhien'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * Get Top Daily Comics
     */
    public function get_top_daily($request)
    {
        return $this->get_top_comics_by_view($request, 'daily_views');
    }

    /**
     * Get Top Weekly Comics
     */
    public function get_top_weekly($request)
    {
        return $this->get_top_comics_by_view($request, 'weekly_views');
    }

    /**
     * Get Top Monthly Comics
     */
    public function get_top_monthly($request)
    {
        return $this->get_top_comics_by_view($request, 'monthly_views');
    }

    /**
     * Core logic để lấy top comics theo view column (Giải pháp 2)
     */
    private function get_top_comics_by_view($request, $view_column)
    {
        global $wpdb;

        $page = $request->get_param('page') ?: 1;
        $status = $request->get_param('status') ?: '';
        $country = $request->get_param('country') ?: '';
        $posts_per_page = 42;

        $stats_table = $wpdb->prefix . 'nettruyen_view_stats';


        $comic_ids = $wpdb->get_col("
            SELECT p.ID 
            FROM {$wpdb->posts} p
            LEFT JOIN {$stats_table} s ON p.ID = s.post_id
            WHERE p.post_type = 'nettruyen_comic' 
            AND p.post_status = 'publish'
            ORDER BY COALESCE(s.{$view_column}, 0) DESC, 
                     COALESCE(s.total_display_views, 0) DESC,
                     p.post_date DESC
        ");

        if (empty($comic_ids)) {
            return rest_ensure_response(array(
                'success' => true,
                'comics' => array(),
                'pagination' => array(
                    'current_page' => 1,
                    'total_pages' => 0,
                    'total_comics' => 0
                )
            ));
        }

        $args = array(
            'post_type' => 'nettruyen_comic',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $page,
            'post__in' => $comic_ids,
            'orderby' => 'post__in'
        );

        if ($status !== '') {
            $args['meta_query'] = array(
                array(
                    'key' => '_nettruyen_status',
                    'value' => $status,
                    'compare' => '='
                )
            );
        }

        if ($country !== '') {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'nettruyen_country',
                    'field' => 'slug',
                    'terms' => $country
                )
            );
        }

        $query = new WP_Query($args);

        $comics = array();
        $rank = ($page - 1) * $posts_per_page;

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $rank++;

                $view_stats = $wpdb->get_row($wpdb->prepare(
                    "SELECT {$view_column} as views FROM {$stats_table} WHERE post_id = %d",
                    $post_id
                ));
                $view_count = $view_stats ? $view_stats->views : 0;

                $comics[] = $this->format_comic_data($post_id, $view_count, $rank);
            }
            wp_reset_postdata();
        }

        return rest_ensure_response(array(
            'success' => true,
            'comics' => $comics,
            'pagination' => array(
                'current_page' => (int) $page,
                'total_pages' => $query->max_num_pages,
                'total_comics' => $query->found_posts
            )
        ));
    }

    /**
     * Get Yêu Thích
     */
    public function get_yeu_thich($request)
    {
        $page = $request->get_param('page') ?: 1;
        $status = $request->get_param('status') ?: '';
        $country = $request->get_param('country') ?: '';

        $args = array(
            'post_type' => 'nettruyen_comic',
            'post_status' => 'publish',
            'posts_per_page' => 42,
            'paged' => $page,
            'meta_key' => '_nettruyen_follow_count',
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        );

        if ($status !== '') {
            $args['meta_query'] = array(
                'relation' => 'AND',
                array(
                    'key' => '_nettruyen_follow_count',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => '_nettruyen_status',
                    'value' => $status,
                    'compare' => '='
                )
            );
        }

        if ($country !== '') {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'nettruyen_country',
                    'field' => 'slug',
                    'terms' => $country
                )
            );
        }

        return $this->execute_query_and_format($args, true);
    }

    /**
     * Get Truyện Mới
     */
    public function get_truyen_moi($request)
    {
        $page = $request->get_param('page') ?: 1;
        $status = $request->get_param('status') ?: '';
        $country = $request->get_param('country') ?: '';

        $args = array(
            'post_type' => 'nettruyen_comic',
            'post_status' => 'publish',
            'posts_per_page' => 42,
            'paged' => $page,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        if ($status !== '') {
            $args['meta_query'] = array(
                array(
                    'key' => '_nettruyen_status',
                    'value' => $status,
                    'compare' => '='
                )
            );
        }

        if ($country !== '') {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'nettruyen_country',
                    'field' => 'slug',
                    'terms' => $country
                )
            );
        }

        return $this->execute_query_and_format($args, false, true);
    }

    /**
     * Get Truyện Full
     */
    public function get_truyen_full($request)
    {
        $page = $request->get_param('page') ?: 1;
        $country = $request->get_param('country') ?: '';

        $args = array(
            'post_type' => 'nettruyen_comic',
            'post_status' => 'publish',
            'posts_per_page' => 42,
            'paged' => $page,
            'meta_query' => array(
                array(
                    'key' => '_nettruyen_status',
                    'value' => 'completed',
                    'compare' => '='
                )
            ),
            'orderby' => 'modified',
            'order' => 'DESC'
        );

        if ($country !== '') {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'nettruyen_country',
                    'field' => 'slug',
                    'terms' => $country
                )
            );
        }

        return $this->execute_query_and_format($args);
    }

    /**
     * Get Ngẫu Nhiên
     */
    public function get_ngau_nhien($request)
    {
        $page = $request->get_param('page') ?: 1;
        $status = $request->get_param('status') ?: '';
        $country = $request->get_param('country') ?: '';

        $seed = date('Ymd');

        $args = array(
            'post_type' => 'nettruyen_comic',
            'post_status' => 'publish',
            'posts_per_page' => 42,
            'paged' => $page,
            'orderby' => 'rand(' . $seed . ')',
            'order' => 'ASC'
        );

        if ($status !== '') {
            $args['meta_query'] = array(
                array(
                    'key' => '_nettruyen_status',
                    'value' => $status,
                    'compare' => '='
                )
            );
        }

        if ($country !== '') {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'nettruyen_country',
                    'field' => 'slug',
                    'terms' => $country
                )
            );
        }

        return $this->execute_query_and_format($args);
    }

    /**
     * Execute query and format response
     */
    private function execute_query_and_format($args, $with_rank = false, $show_new_badge = false)
    {
        $query = new WP_Query($args);
        $comics = array();
        $rank = ($args['paged'] - 1) * $args['posts_per_page'];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                if ($with_rank) {
                    $rank++;
                }

                global $wpdb;
                $stats_table = $wpdb->prefix . 'nettruyen_view_stats';
                $view_stats = $wpdb->get_row($wpdb->prepare(
                    "SELECT total_display_views FROM {$stats_table} WHERE post_id = %d",
                    $post_id
                ));
                $view_count = $view_stats ? $view_stats->total_display_views : 0;

                $comic_data = $this->format_comic_data($post_id, $view_count, $with_rank ? $rank : null);

                if ($show_new_badge) {
                    $is_new = (time() - get_the_time('U')) < (3 * DAY_IN_SECONDS);
                    if ($is_new) {
                        $comic_data['badge_type'] = 'label-new';
                        $comic_data['badge_text'] = 'Mới';
                    }
                }

                $comics[] = $comic_data;
            }
            wp_reset_postdata();
        }

        return rest_ensure_response(array(
            'success' => true,
            'comics' => $comics,
            'pagination' => array(
                'current_page' => (int) $args['paged'],
                'total_pages' => $query->max_num_pages,
                'total_comics' => $query->found_posts
            )
        ));
    }

    /**
     * Format comic data
     */
    private function format_comic_data($post_id, $view_count, $rank = null)
    {
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

        $follow_count = get_post_meta($post_id, '_nettruyen_follow_count', true) ?: 0;

        $data = array(
            'id' => $post_id,
            'title' => get_the_title(),
            'url' => get_permalink(),
            'thumbnail' => $thumbnail,
            'latest_chapter' => $latest_chapter,
            'time_ago' => $time_ago,
            'follow_count' => number_format($follow_count),
            'view_count' => number_format($view_count)
        );

        if ($rank !== null) {
            $is_top3 = ($rank <= 3);
            $rank_class = '';
            if ($rank === 1)
                $rank_class = 'rank-1';
            elseif ($rank === 2)
                $rank_class = 'rank-2';
            elseif ($rank === 3)
                $rank_class = 'rank-3';

            $data['rank'] = $rank;
            $data['rank_class'] = $rank_class;
            $data['is_top3'] = $is_top3;
        }

        return $data;
    }
}

new NetTruyen_Top_Comics_API();