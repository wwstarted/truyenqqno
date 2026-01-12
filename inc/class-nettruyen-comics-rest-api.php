<?php
/**
 * REST API for Comics Listing
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class NetTruyen_Comics_REST_API
{
    /**
     * Register REST API routes
     */
    public static function register_routes()
    {
        // Route 1: All comics (Truyện Mới Cập Nhật)
        register_rest_route('nettruyen/v1', '/comics', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_comics'),
            'permission_callback' => '__return_true',
            'args' => array(
                'page' => array(
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ),
                'status' => array(
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'country' => array(
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));

        // Route 2: Comics by genre (Truyện Theo Thể Loại)
        register_rest_route('nettruyen/v1', '/comics/genre', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_comics_by_genre'),
            'permission_callback' => '__return_true',
            'args' => array(
                'genre' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'page' => array(
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ),
                'status' => array(
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'country' => array(
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'sort' => array(
                    'default' => '2',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
    }

    /**
     * Get comics data (for All Comics page)
     */
    public static function get_comics($request)
    {
        try {
            global $wpdb;

            $page = $request->get_param('page');
            $status = $request->get_param('status');
            $country = $request->get_param('country');
            $posts_per_page = 42;

            // Build query args
            $args = array(
                'post_type' => 'nettruyen_comic',
                'post_status' => 'publish',
                'posts_per_page' => $posts_per_page,
                'paged' => $page,
                'orderby' => 'modified',
                'order' => 'DESC'
            );

            // Apply status filter
            if ($status !== '') {
                $args['meta_query'] = array(
                    array(
                        'key' => '_nettruyen_status',
                        'value' => $status,
                        'compare' => '='
                    )
                );
            }

            // Apply country filter
            if ($country !== '') {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'nettruyen_country',
                        'field' => 'slug',
                        'terms' => $country
                    )
                );
            }

            $comics_query = new WP_Query($args);

            // Get hot comics (cache for performance)
            $stats_table = $wpdb->prefix . 'nettruyen_view_stats';
            $hot_comic_ids = wp_cache_get('hot_comic_ids', 'nettruyen');

            if (false === $hot_comic_ids) {
                $hot_comic_ids = $wpdb->get_col(
                    "SELECT post_id 
                    FROM {$stats_table} 
                    WHERE total_display_views > 0 
                    ORDER BY total_display_views DESC 
                    LIMIT 20"
                );
                wp_cache_set('hot_comic_ids', $hot_comic_ids, 'nettruyen', 300);
            }

            // Build comics data
            $comics = self::build_comics_array($comics_query, $hot_comic_ids);

            // Build response
            $response = array(
                'success' => true,
                'comics' => $comics,
                'pagination' => array(
                    'current_page' => $page,
                    'total_pages' => $comics_query->max_num_pages,
                    'total_comics' => $comics_query->found_posts,
                    'per_page' => $posts_per_page,
                ),
            );

            return rest_ensure_response($response);

        } catch (Exception $e) {
            return new WP_Error(
                'comics_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }
    }

    /**
     * Get comics by genre (for Genre page)
     */
    public static function get_comics_by_genre($request)
    {
        try {
            global $wpdb;

            $genre = $request->get_param('genre');
            $page = $request->get_param('page');
            $status = $request->get_param('status');
            $country = $request->get_param('country');
            $sort = $request->get_param('sort');
            $posts_per_page = 42;

            // Sort mapping
            $sort_options = array(
                '0' => array('orderby' => 'date', 'order' => 'DESC'),
                '1' => array('orderby' => 'date', 'order' => 'ASC'),
                '2' => array('orderby' => 'modified', 'order' => 'DESC'),
                '3' => array('orderby' => 'modified', 'order' => 'ASC'),
                '4' => array('orderby' => 'meta_value_num', 'order' => 'DESC'),
                '5' => array('orderby' => 'meta_value_num', 'order' => 'ASC'),
            );

            $sort_config = isset($sort_options[$sort]) ? $sort_options[$sort] : $sort_options['2'];

            // Build query args
            $args = array(
                'post_type' => 'nettruyen_comic',
                'post_status' => 'publish',
                'posts_per_page' => $posts_per_page,
                'paged' => $page,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'nettruyen_genre',
                        'field' => 'slug',
                        'terms' => $genre
                    )
                ),
                'orderby' => $sort_config['orderby'],
                'order' => $sort_config['order']
            );

            // Apply status filter
            if ($status !== '') {
                if (!isset($args['meta_query'])) {
                    $args['meta_query'] = array();
                }
                $args['meta_query'][] = array(
                    'key' => '_nettruyen_status',
                    'value' => $status,
                    'compare' => '='
                );
            }

            // Apply country filter
            if ($country !== '') {
                $args['tax_query'][] = array(
                    'taxonomy' => 'nettruyen_country',
                    'field' => 'slug',
                    'terms' => $country
                );
            }

            // For sort by views - USE DIFFERENT QUERY
            if ($sort == '4' || $sort == '5') {
                // Don't use meta_key - query view stats table instead
                unset($args['orderby']);
                unset($args['order']);

                // We'll sort manually after getting posts
                $args['posts_per_page'] = -1; // Get all first
                $args['fields'] = 'ids'; // Only IDs
            }

            $comics_query = new WP_Query($args);

            // Manual sorting for view-based queries
            if ($sort == '4' || $sort == '5') {
                $stats_table = $wpdb->prefix . 'nettruyen_view_stats';

                // Get view counts for all posts
                $post_ids = $comics_query->posts;
                if (!empty($post_ids)) {
                    $placeholders = implode(',', array_fill(0, count($post_ids), '%d'));
                    $view_data = $wpdb->get_results($wpdb->prepare(
                        "SELECT post_id, total_display_views 
                        FROM {$stats_table} 
                        WHERE post_id IN ({$placeholders})",
                        ...$post_ids
                    ), OBJECT_K);

                    // Sort posts by view count
                    usort($post_ids, function ($a, $b) use ($view_data, $sort) {
                        $views_a = isset($view_data[$a]) ? (int) $view_data[$a]->total_display_views : 0;
                        $views_b = isset($view_data[$b]) ? (int) $view_data[$b]->total_display_views : 0;

                        if ($sort == '4') {
                            return $views_b - $views_a; // DESC
                        } else {
                            return $views_a - $views_b; // ASC
                        }
                    });

                    // Apply pagination manually
                    $offset = ($page - 1) * $posts_per_page;
                    $paginated_ids = array_slice($post_ids, $offset, $posts_per_page);

                    // Create new query with paginated IDs
                    $comics_query = new WP_Query(array(
                        'post_type' => 'nettruyen_comic',
                        'post__in' => $paginated_ids,
                        'orderby' => 'post__in',
                        'posts_per_page' => $posts_per_page
                    ));

                    // Set pagination info manually
                    $comics_query->max_num_pages = ceil(count($post_ids) / $posts_per_page);
                    $comics_query->found_posts = count($post_ids);
                }
            }

            // Get hot comics
            $stats_table = $wpdb->prefix . 'nettruyen_view_stats';
            $hot_comic_ids = wp_cache_get('hot_comic_ids', 'nettruyen');

            if (false === $hot_comic_ids) {
                $hot_comic_ids = $wpdb->get_col(
                    "SELECT post_id 
                    FROM {$stats_table} 
                    WHERE total_display_views > 0 
                    ORDER BY total_display_views DESC 
                    LIMIT 20"
                );
                wp_cache_set('hot_comic_ids', $hot_comic_ids, 'nettruyen', 300);
            }

            // Build comics data
            $comics = self::build_comics_array($comics_query, $hot_comic_ids);

            // Build response
            $response = array(
                'success' => true,
                'comics' => $comics,
                'pagination' => array(
                    'current_page' => $page,
                    'total_pages' => $comics_query->max_num_pages,
                    'total_comics' => $comics_query->found_posts,
                    'per_page' => $posts_per_page,
                ),
            );

            return rest_ensure_response($response);

        } catch (Exception $e) {
            return new WP_Error(
                'comics_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }
    }

    /**
     * Build comics array (reusable method)
     */
    private static function build_comics_array($comics_query, $hot_comic_ids)
    {
        global $wpdb;
        $comics = array();
        $stats_table = $wpdb->prefix . 'nettruyen_view_stats';

        if ($comics_query->have_posts()) {
            while ($comics_query->have_posts()) {
                $comics_query->the_post();
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

                // Badge logic
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
                    'follow_count' => number_format($follow_count),
                    'view_count' => number_format($view_count),
                    'badge_type' => $badge_type,
                    'badge_text' => $badge_text,
                );
            }
            wp_reset_postdata();
        }

        return $comics;
    }
}

// ✅ Register routes on REST API init
add_action('rest_api_init', array('NetTruyen_Comics_REST_API', 'register_routes'));