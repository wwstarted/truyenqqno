<?php
/**
 * NetTruyen View Tracker
 * Core class để track real views từ users
 * 
 * @package NetTruyen
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class NetTruyen_View_Tracker
{

    /**
     * Track view cho một chapter
     * 
     * @param int $post_id ID truyện
     * @param string $chapter_slug Slug chapter (từ manifest)
     * @return array ['success' => bool, 'message' => string, 'counted' => bool]
     */
    public static function track_chapter_view($post_id, $chapter_slug)
    {
        global $wpdb;


        if (empty($post_id) || empty($chapter_slug)) {
            return array(
                'success' => false,
                'message' => 'Invalid post_id or chapter_slug',
                'counted' => false
            );
        }


        $post = get_post($post_id);
        if (!$post || $post->post_status !== 'publish' || $post->post_type !== 'nettruyen_comic') {
            return array(
                'success' => false,
                'message' => 'Comic not found or not published',
                'counted' => false
            );
        }


        $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
        if (empty($manifest_json)) {
            return array(
                'success' => false,
                'message' => 'No chapter manifest found',
                'counted' => false
            );
        }

        $manifest = json_decode($manifest_json, true);
        $chapter_exists = false;
        foreach ($manifest['chapters'] as $chapter) {
            if ($chapter['slug'] === $chapter_slug) {
                $chapter_exists = true;
                break;
            }
        }

        if (!$chapter_exists) {
            return array(
                'success' => false,
                'message' => 'Chapter not found in manifest',
                'counted' => false
            );
        }


        if (!self::should_count_view($post_id, $chapter_slug)) {
            return array(
                'success' => true,
                'message' => 'View already counted (anti-spam)',
                'counted' => false
            );
        }


        $today = current_time('Y-m-d');
        $chapter_table = $wpdb->prefix . 'nettruyen_chapter_views';
        $comic_table = $wpdb->prefix . 'nettruyen_comic_views';
        $stats_table = $wpdb->prefix . 'nettruyen_view_stats';

        try {
            $wpdb->query('START TRANSACTION');


            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$chapter_table} 
                (post_id, chapter_slug, view_date, view_count) 
                VALUES (%d, %s, %s, 1)
                ON DUPLICATE KEY UPDATE view_count = view_count + 1",
                $post_id,
                $chapter_slug,
                $today
            ));


            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$comic_table} 
                (post_id, view_date, view_count) 
                VALUES (%d, %s, 1)
                ON DUPLICATE KEY UPDATE view_count = view_count + 1",
                $post_id,
                $today
            ));


            self::update_stats_cache($post_id);

            $wpdb->query('COMMIT');


            self::mark_view_counted($post_id, $chapter_slug);

            return array(
                'success' => true,
                'message' => 'View tracked successfully',
                'counted' => true
            );

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('NetTruyen View Tracker Error: ' . $e->getMessage());

            return array(
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
                'counted' => false
            );
        }
    }

    /**
     * Check xem có nên count view không (Anti-spam)
     * Logic: 1 view/IP/chapter/24h + session check
     * 
     * @param int $post_id ID truyện
     * @param string $chapter_slug Slug chapter
     * @return bool True = nên count, False = đã count rồi
     */
    private static function should_count_view($post_id, $chapter_slug)
    {

        $user_ip = self::get_user_ip();


        $session_key = 'nettruyen_view_' . $post_id . '_' . $chapter_slug;


        if (isset($_SESSION[$session_key])) {
            $last_view = $_SESSION[$session_key];
            if ((time() - $last_view) < DAY_IN_SECONDS) {
                return false;
            }
        }


        $transient_key = 'nettruyen_view_' . md5($user_ip . $post_id . $chapter_slug);
        if (get_transient($transient_key)) {
            return false;
        }

        return true;
    }

    /**
     * Mark view as counted (set session + transient)
     * 
     * @param int $post_id ID truyện
     * @param string $chapter_slug Slug chapter
     */
    private static function mark_view_counted($post_id, $chapter_slug)
    {

        if (!session_id()) {
            session_start();
        }


        $session_key = 'nettruyen_view_' . $post_id . '_' . $chapter_slug;
        $_SESSION[$session_key] = time();


        $user_ip = self::get_user_ip();
        $transient_key = 'nettruyen_view_' . md5($user_ip . $post_id . $chapter_slug);
        set_transient($transient_key, true, DAY_IN_SECONDS);
    }

    /**
     * Get user IP address
     * 
     * @return string IP address
     */
    private static function get_user_ip()
    {
        $ip_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Update stats cache (total views, daily, weekly, monthly)
     * 
     * @param int $post_id ID truyện
     */
    private static function update_stats_cache($post_id)
    {
        global $wpdb;

        require_once get_template_directory() . '/inc/class-nettruyen-view-calculator.php';

        $chapter_table = $wpdb->prefix . 'nettruyen_chapter_views';
        $stats_table = $wpdb->prefix . 'nettruyen_view_stats';


        $total_real = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(view_count) FROM {$chapter_table} WHERE post_id = %d",
            $post_id
        ));

        $daily = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(view_count) FROM {$chapter_table} 
            WHERE post_id = %d AND view_date = CURDATE()",
            $post_id
        ));

        $weekly = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(view_count) FROM {$chapter_table} 
            WHERE post_id = %d AND view_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
            $post_id
        ));

        $monthly = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(view_count) FROM {$chapter_table} 
            WHERE post_id = %d AND view_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
            $post_id
        ));


        $current = $wpdb->get_row($wpdb->prepare(
            "SELECT total_fake_views, use_fake_views FROM {$stats_table} WHERE post_id = %d",
            $post_id
        ));

        $fake_views = $current ? (int) $current->total_fake_views : 0;
        $use_fake = $current ? (int) $current->use_fake_views : 1;


        if ($use_fake) {
            $use_fake = NetTruyen_View_Calculator::should_use_fake_views($post_id, $total_real) ? 1 : 0;
        }


        $display_views = $use_fake ? ($total_real + $fake_views) : $total_real;


        $wpdb->replace(
            $stats_table,
            array(
                'post_id' => $post_id,
                'total_real_views' => $total_real ?: 0,
                'total_fake_views' => $fake_views,
                'total_display_views' => $display_views,
                'daily_views' => $daily ?: 0,
                'weekly_views' => $weekly ?: 0,
                'monthly_views' => $monthly ?: 0,
                'last_view_at' => current_time('mysql'),
                'use_fake_views' => $use_fake
            ),
            array('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%d')
        );
    }

    /**
     * Get view stats cho một truyện
     * 
     * @param int $post_id ID truyện
     * @return array|null Stats array hoặc null nếu không tìm thấy
     */
    public static function get_stats($post_id)
    {
        global $wpdb;

        $stats_table = $wpdb->prefix . 'nettruyen_view_stats';

        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$stats_table} WHERE post_id = %d",
            $post_id
        ), ARRAY_A);

        return $stats;
    }

    /**
     * Get chapter views distribution (cho chart)
     * 
     * @param int $post_id ID truyện
     * @param int $days Số ngày lấy data (default 30)
     * @return array ['labels' => [], 'data' => []]
     */
    public static function get_chapter_views_chart($post_id, $days = 30)
    {
        global $wpdb;

        $chapter_table = $wpdb->prefix . 'nettruyen_chapter_views';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT chapter_slug, SUM(view_count) as total_views
            FROM {$chapter_table}
            WHERE post_id = %d 
            AND view_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
            GROUP BY chapter_slug
            ORDER BY chapter_slug ASC",
            $post_id,
            $days
        ));

        $labels = array();
        $data = array();

        foreach ($results as $row) {
            $labels[] = 'Ch. ' . $row->chapter_slug;
            $data[] = (int) $row->total_views;
        }

        return array(
            'labels' => $labels,
            'data' => $data
        );
    }

    /**
     * Get daily views trend (cho timeline chart)
     * 
     * @param int $post_id ID truyện
     * @param int $days Số ngày
     * @return array ['dates' => [], 'views' => []]
     */
    public static function get_daily_trend($post_id, $days = 30)
    {
        global $wpdb;

        $comic_table = $wpdb->prefix . 'nettruyen_comic_views';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT view_date, view_count
            FROM {$comic_table}
            WHERE post_id = %d 
            AND view_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
            ORDER BY view_date ASC",
            $post_id,
            $days
        ));

        $dates = array();
        $views = array();

        foreach ($results as $row) {
            $dates[] = date('d/m', strtotime($row->view_date));
            $views[] = (int) $row->view_count;
        }

        return array(
            'dates' => $dates,
            'views' => $views
        );
    }
}