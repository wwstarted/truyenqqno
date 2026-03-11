<?php
/**
 * REST API: Comics by Author
 * Endpoint: GET /wp-json/nettruyen/v1/comics/author
 *
 * @package TruyenQQ
 * @version 1.0.1 
 */

if (!defined('ABSPATH'))
    exit;

add_action('rest_api_init', 'nettruyen_register_author_comics_endpoint');

function nettruyen_register_author_comics_endpoint()
{
    register_rest_route('nettruyen/v1', '/comics/author', array(
        'methods' => 'GET',
        'callback' => 'nettruyen_author_comics_callback',
        'permission_callback' => '__return_true',
        'args' => array(
            'author' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
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

function nettruyen_author_comics_callback($request)
{
    global $wpdb;

    $author_slug = $request->get_param('author');
    $page = max(1, (int) $request->get_param('page'));
    $sort = (int) $request->get_param('sort');
    $posts_per_page = 42;
    $stats_table = $wpdb->prefix . 'nettruyen_view_stats';

    /* ── Verify author term ── */
    $author_term = get_term_by('slug', $author_slug, 'nettruyen_author');
    if (!$author_term || is_wp_error($author_term)) {
        return new WP_Error(
            'author_not_found',
            'Tác giả không tồn tại.',
            array('status' => 404)
        );
    }

    /* ── Base query args ── */
    $args = array(
        'post_type' => 'nettruyen_comic',
        'post_status' => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'tax_query' => array(
            array(
                'taxonomy' => 'nettruyen_author',
                'field' => 'slug',
                'terms' => $author_slug,
            ),
        ),
    );

    $need_view_join = ($sort === 4 || $sort === 5);

    switch ($sort) {
        case 0:
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        case 1:
            $args['orderby'] = 'date';
            $args['order'] = 'ASC';
            break;
        case 3:
            $args['orderby'] = 'modified';
            $args['order'] = 'ASC';
            break;
        case 4:
            /* orderby overridden by posts_orderby filter below */
            $args['orderby'] = 'post_date'; // placeholder
            $args['order'] = 'DESC';
            break;
        case 5:
            $args['orderby'] = 'post_date'; // placeholder
            $args['order'] = 'ASC';
            break;
        default: // 2
            $args['orderby'] = 'modified';
            $args['order'] = 'DESC';
            break;
    }

    /* ── Attach temporary filters for view-based sort ── */
    if ($need_view_join) {
        $view_order = ($sort === 4) ? 'DESC' : 'ASC';

        $join_fn = function ($join) use ($wpdb, $stats_table) {
            $join .= " LEFT JOIN {$stats_table} AS vs_author ON {$wpdb->posts}.ID = vs_author.post_id ";
            return $join;
        };

        $orderby_fn = function ($orderby) use ($view_order) {
            return "COALESCE(vs_author.total_display_views, 0) {$view_order}";
        };

        add_filter('posts_join', $join_fn);
        add_filter('posts_orderby', $orderby_fn);
    }

    $query = new WP_Query($args);

    if ($need_view_join) {
        remove_filter('posts_join', $join_fn);
        remove_filter('posts_orderby', $orderby_fn);
    }

    $hot_comic_ids = $wpdb->get_col(
        "SELECT post_id FROM {$stats_table}
         WHERE total_display_views > 0
         ORDER BY total_display_views DESC
         LIMIT 20"
    );

    $comics = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

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
                : get_the_modified_date('Y-m-d H:i:s');
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

    return rest_ensure_response(array(
        'success' => true,
        'author' => array(
            'name' => $author_term->name,
            'slug' => $author_term->slug,
            'count' => (int) $author_term->count,
        ),
        'comics' => $comics,
        'pagination' => array(
            'current_page' => $page,
            'total_pages' => (int) $query->max_num_pages,
            'total_results' => (int) $query->found_posts,
        ),
    ));
}

add_filter('register_taxonomy_args', function ($args, $taxonomy) {
    if ($taxonomy === 'nettruyen_author') {
        $args['rewrite'] = [
            'slug' => 'tac-gia',
            'with_front' => false,
            'hierarchical' => false,
        ];
    }
    return $args;
}, 10, 2);