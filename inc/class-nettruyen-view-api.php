<?php
/**
 * NetTruyen View API
 * REST API endpoints cho view tracking system
 * 
 * @package NetTruyen
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class NetTruyen_View_API
{

    /**
     * Register REST API routes
     */
    public static function register_routes()
    {
        // Track view endpoint
        register_rest_route('nettruyen/v1', '/track-view', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'track_view_callback'),
            'permission_callback' => '__return_true',
            'args' => array(
                'post_id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function ($param) {
                        return is_numeric($param) && $param > 0;
                    }
                ),
                'chapter_slug' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                )
            ),
        ));

        // Get stats endpoint
        register_rest_route('nettruyen/v1', '/comic/(?P<id>\d+)/stats', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_stats_callback'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'validate_callback' => function ($param) {
                        return is_numeric($param);
                    }
                ),
            ),
        ));

        // Get chapter chart endpoint
        register_rest_route('nettruyen/v1', '/comic/(?P<id>\d+)/chapter-chart', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_chapter_chart_callback'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'validate_callback' => function ($param) {
                        return is_numeric($param);
                    }
                ),
                'days' => array(
                    'default' => 30,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));

        // Get daily trend endpoint
        register_rest_route('nettruyen/v1', '/comic/(?P<id>\d+)/daily-trend', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_daily_trend_callback'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'validate_callback' => function ($param) {
                        return is_numeric($param);
                    }
                ),
                'days' => array(
                    'default' => 30,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
    }

    /**
     * Track view callback
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public static function track_view_callback($request)
    {
        require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

        $post_id = $request->get_param('post_id');
        $chapter_slug = $request->get_param('chapter_slug');

        $result = NetTruyen_View_Tracker::track_chapter_view($post_id, $chapter_slug);

        return rest_ensure_response($result);
    }

    /**
     * Get stats callback
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public static function get_stats_callback($request)
    {
        require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

        $post_id = $request->get_param('id');

        $stats = NetTruyen_View_Tracker::get_stats($post_id);

        if (!$stats) {
            return new WP_Error(
                'no_stats',
                'No stats found for this comic',
                array('status' => 404)
            );
        }

        return rest_ensure_response($stats);
    }

    /**
     * Get chapter chart callback
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public static function get_chapter_chart_callback($request)
    {
        require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

        $post_id = $request->get_param('id');
        $days = $request->get_param('days');

        $chart_data = NetTruyen_View_Tracker::get_chapter_views_chart($post_id, $days);

        return rest_ensure_response($chart_data);
    }

    /**
     * Get daily trend callback
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public static function get_daily_trend_callback($request)
    {
        require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

        $post_id = $request->get_param('id');
        $days = $request->get_param('days');

        $trend_data = NetTruyen_View_Tracker::get_daily_trend($post_id, $days);

        return rest_ensure_response($trend_data);
    }
}

// Hook to register routes
add_action('rest_api_init', array('NetTruyen_View_API', 'register_routes'));