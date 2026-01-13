<?php
/**
 * REST API: Advanced Search Endpoint
 * 
 * Handles advanced search with genre filters, status, country, min chapters, etc.
 * 
 * @package TruyenQQ
 * @version 1.0.1
 */

class NetTruyen_Advanced_Search_API
{

    /**
     * Initialize the API
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register REST API routes
     */
    public function register_routes()
    {
        register_rest_route('nettruyen/v1', '/advanced-search', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_comics'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * Get comics with advanced filters
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_comics($request)
    {
        global $wpdb;

        // Get parameters
        $page = max(1, intval($request->get_param('page')));
        $genres_include = $request->get_param('genres') ? array_map('intval', explode(',', $request->get_param('genres'))) : array();
        $genres_exclude = $request->get_param('exclude') ? array_map('intval', explode(',', $request->get_param('exclude'))) : array();
        $status = sanitize_text_field($request->get_param('status'));
        $country = sanitize_text_field($request->get_param('country'));
        $minchapter = max(0, intval($request->get_param('minchapter')));
        $sort = max(0, min(5, intval($request->get_param('sort'))));

        $posts_per_page = 42;

        // Sort mapping
        $sort_options = array(
            0 => array('orderby' => 'date', 'order' => 'DESC'),
            1 => array('orderby' => 'date', 'order' => 'ASC'),
            2 => array('orderby' => 'modified', 'order' => 'DESC'),
            3 => array('orderby' => 'modified', 'order' => 'ASC'),
            4 => array('orderby' => 'view_count', 'order' => 'DESC'), // Custom handling
            5 => array('orderby' => 'view_count', 'order' => 'ASC'),  // Custom handling
        );

        $sort_config = $sort_options[$sort];

        // Build query args
        $args = array(
            'post_type' => 'nettruyen_comic',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $page,
        );

        // Build tax_query for genres and country
        $tax_query = array('relation' => 'AND');

        // Include genres (must have ALL)
        if (!empty($genres_include)) {
            $tax_query[] = array(
                'taxonomy' => 'nettruyen_genre',
                'field' => 'term_id',
                'terms' => $genres_include,
                'operator' => 'AND'
            );
        }

        // Exclude genres (must NOT have ANY)
        if (!empty($genres_exclude)) {
            $tax_query[] = array(
                'taxonomy' => 'nettruyen_genre',
                'field' => 'term_id',
                'terms' => $genres_exclude,
                'operator' => 'NOT IN'
            );
        }

        // Country filter
        if (!empty($country)) {
            $tax_query[] = array(
                'taxonomy' => 'nettruyen_country',
                'field' => 'slug',
                'terms' => $country
            );
        }

        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }

        // Build meta_query for status and min chapters
        $meta_query = array();

        // Status filter
        if (!empty($status)) {
            $meta_query[] = array(
                'key' => '_nettruyen_status',
                'value' => $status,
                'compare' => '='
            );
        }

        // Min chapter filter
        if ($minchapter > 0) {
            $meta_query[] = array(
                'key' => '_nettruyen_chapter_count',
                'value' => $minchapter,
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }

        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        // ===== FIX: Sort by view count using JOIN =====
        if ($sort == 4 || $sort == 5) {
            $stats_table = $wpdb->prefix . 'nettruyen_view_stats';

            // Add custom filter for JOIN
            add_filter('posts_join', function ($join) use ($wpdb, $stats_table) {
                $join .= " LEFT JOIN {$stats_table} ON {$wpdb->posts}.ID = {$stats_table}.post_id";
                return $join;
            });

            // Add custom filter for ORDER BY
            add_filter('posts_orderby', function ($orderby) use ($sort) {
                $order = ($sort == 4) ? 'DESC' : 'ASC';
                return "COALESCE({$GLOBALS['wpdb']->prefix}nettruyen_view_stats.total_display_views, 0) {$order}";
            });

            // Execute query
            $query = new WP_Query($args);

            // Remove filters after query
            remove_all_filters('posts_join');
            remove_all_filters('posts_orderby');

        } else {
            // Normal sort (date, modified)
            $args['orderby'] = $sort_config['orderby'];
            $args['order'] = $sort_config['order'];
            $query = new WP_Query($args);
        }

        // Get hot comics (top 20 by views)
        $stats_table = $wpdb->prefix . 'nettruyen_view_stats';
        $hot_comic_ids = $wpdb->get_col(
            "SELECT post_id 
            FROM {$stats_table} 
            WHERE total_display_views > 0 
            ORDER BY total_display_views DESC 
            LIMIT 20"
        );

        // Format comics
        $comics = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                // Get thumbnail
                $thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);
                if (empty($thumbnail)) {
                    $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
                }
                if (empty($thumbnail)) {
                    $thumbnail = 'https://via.placeholder.com/190x247?text=No+Image';
                }

                // Get chapter manifest
                $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
                $manifest = !empty($manifest_json) ? json_decode($manifest_json, true) : null;

                // Get latest chapter
                $latest_chapter = 'Đang cập nhật';
                if (!empty($manifest['chapters'])) {
                    $chapters = $manifest['chapters'];
                    $latest = end($chapters);
                    $latest_chapter = 'Chapter ' . $latest['name'];
                }

                // Get time ago
                $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_the_modified_time('U');
                $time_ago = human_time_diff(strtotime($updated_at), current_time('timestamp')) . ' trước';

                // Get stats
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

                // Format numbers
                $follow_count_formatted = number_format($follow_count);
                $view_count_formatted = number_format($view_count);

                // Badge priority: Hot > New
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

                $comics[] = array(
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'thumbnail' => $thumbnail,
                    'latest_chapter' => $latest_chapter,
                    'time_ago' => $time_ago,
                    'follow_count' => $follow_count_formatted,
                    'view_count' => $view_count_formatted,
                    'badge_type' => $badge_type,
                    'badge_text' => $badge_text
                );
            }

            wp_reset_postdata();
        }

        // Pagination
        $total_pages = $query->max_num_pages;
        $current_page = max(1, $page);

        return new WP_REST_Response(array(
            'success' => true,
            'comics' => $comics,
            'pagination' => array(
                'current_page' => $current_page,
                'total_pages' => $total_pages,
                'total_comics' => $query->found_posts
            )
        ), 200);
    }
}

// Initialize the API
new NetTruyen_Advanced_Search_API();