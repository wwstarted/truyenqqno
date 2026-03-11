<?php
/**
 * REST API: Comics by Country
 * Endpoint: GET /wp-json/nettruyen/v1/comics/country
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

if (!defined('ABSPATH'))
    exit;

add_action('rest_api_init', 'nettruyen_register_country_comics_endpoint');

function nettruyen_register_country_comics_endpoint()
{
    register_rest_route('nettruyen/v1', '/comics/country', array(
        'methods' => 'GET',
        'callback' => 'nettruyen_country_comics_callback',
        'permission_callback' => '__return_true',
        'args' => array(
            'country' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'description' => 'Country taxonomy slug',
            ),
            'page' => array(
                'required' => false,
                'type' => 'integer',
                'default' => 1,
            ),
            'sort' => array(
                'required' => false,
                'type' => 'integer',
                'default' => 2,
            ),
        ),
    ));
}

function nettruyen_country_comics_callback($request)
{
    global $wpdb;

    $country_slug = $request->get_param('country');
    $page = max(1, (int) $request->get_param('page'));
    $sort = (int) $request->get_param('sort');
    $posts_per_page = 42;
    $stats_table = $wpdb->prefix . 'nettruyen_view_stats';

    /* ── Verify country term ── */
    $country_term = get_term_by('slug', $country_slug, 'nettruyen_country');
    if (!$country_term || is_wp_error($country_term)) {
        return new WP_Error(
            'country_not_found',
            'Quốc gia không tồn tại.',
            array('status' => 404)
        );
    }

    $term_id = (int) $country_term->term_id;
    $offset = ($page - 1) * $posts_per_page;

    /* ── Sort config ── */
    switch ($sort) {
        case 0:
            $orderby_sql = 'p.post_date DESC';
            break;
        case 1:
            $orderby_sql = 'p.post_date ASC';
            break;
        case 3:
            $orderby_sql = 'p.post_modified ASC';
            break;
        case 4:
            $orderby_sql = 'COALESCE(vs.total_display_views, 0) DESC';
            break;
        case 5:
            $orderby_sql = 'COALESCE(vs.total_display_views, 0) ASC';
            break;
        default:
            $orderby_sql = 'p.post_modified DESC';
            break;
    }

    $view_join_sql = ($sort === 4 || $sort === 5)
        ? "LEFT JOIN {$stats_table} AS vs ON p.ID = vs.post_id"
        : '';

    /* ── Total count ── */
    $total_posts = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT p.ID)
         FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
         INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
         WHERE tt.taxonomy  = 'nettruyen_country'
           AND tt.term_id   = %d
           AND p.post_type  = 'nettruyen_comic'
           AND p.post_status = 'publish'",
        $term_id
    ));

    /* ── Get post IDs ── */
    $comic_post_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT p.ID
         FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
         INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
         {$view_join_sql}
         WHERE tt.taxonomy  = 'nettruyen_country'
           AND tt.term_id   = %d
           AND p.post_type  = 'nettruyen_comic'
           AND p.post_status = 'publish'
         ORDER BY {$orderby_sql}
         LIMIT %d OFFSET %d",
        $term_id,
        $posts_per_page,
        $offset
    ));

    /* ── Hot comic IDs ── */
    $hot_comic_ids = $wpdb->get_col(
        "SELECT post_id FROM {$stats_table}
         WHERE total_display_views > 0
         ORDER BY total_display_views DESC
         LIMIT 20"
    );

    /* ── Build result ── */
    $comics = array();

    foreach ($comic_post_ids as $post_id) {
        $post_id = (int) $post_id;

        $thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);
        if (empty($thumbnail))
            $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
        if (empty($thumbnail))
            $thumbnail = 'https://via.placeholder.com/190x247?text=No+Image';

        $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
        $manifest = !empty($manifest_json) ? json_decode($manifest_json, true) : null;
        $latest_chapter = 'Đang cập nhật';
        if (!empty($manifest['chapters'])) {
            $chapters = $manifest['chapters'];
            $latest = end($chapters);
            $latest_chapter = 'Chapter ' . $latest['name'];
        }

        $updated_at = !empty($manifest['updated_at'])
            ? $manifest['updated_at']
            : get_post_modified_time('Y-m-d H:i:s', false, $post_id);
        $time_ago = truyenqq_time_ago_vietnamese($updated_at);

        $follow_count = (int) get_post_meta($post_id, '_nettruyen_follow_count', true);

        $view_count = 0;
        $view_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT total_display_views FROM {$stats_table} WHERE post_id = %d",
            $post_id
        ));
        if ($view_stats)
            $view_count = (int) $view_stats->total_display_views;

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
            'title' => get_the_title($post_id),
            'url' => get_permalink($post_id),
            'thumbnail' => $thumbnail,
            'latest_chapter' => $latest_chapter,
            'time_ago' => $time_ago,
            'follow_count' => number_format($follow_count),
            'view_count' => number_format($view_count),
            'badge_type' => $badge_type,
            'badge_text' => $badge_text,
        );
    }

    return rest_ensure_response(array(
        'success' => true,
        'country' => array(
            'name' => $country_term->name,
            'slug' => $country_term->slug,
            'count' => (int) $country_term->count,
        ),
        'comics' => $comics,
        'pagination' => array(
            'current_page' => $page,
            'total_pages' => ($total_posts > 0) ? (int) ceil($total_posts / $posts_per_page) : 0,
            'total_results' => $total_posts,
        ),
    ));
}