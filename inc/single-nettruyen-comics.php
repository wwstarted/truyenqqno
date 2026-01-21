<?php
/**
 * Chapter URL Rewrite Rules
 * Add to functions.php
 * 
 * Pattern: /truyen-tranh/{comic-slug}-chap-{chapter-slug}.html
 * 
 * @package TruyenQQ
 * @version 2.0.0
 */

/**
 * Add custom rewrite rules for chapter URLs
 * 
 * FIX: Pattern now accepts any characters in chapter slug (not just numbers)
 */
function nettruyen_add_chapter_rewrite_rules()
{


    add_rewrite_rule(
        '^truyen-tranh/([^/]+)-chap-([^\.]+)\.html$',
        'index.php?nettruyen_comic=$matches[1]&chapter=$matches[2]',
        'top'
    );
}
add_action('init', 'nettruyen_add_chapter_rewrite_rules');

/**
 * Add custom query vars
 * 
 * FIX: Added 'nettruyen_comic' query var (was missing)
 */
function nettruyen_add_query_vars($vars)
{
    $vars[] = 'chapter';
    $vars[] = 'nettruyen_comic';
    return $vars;
}
add_filter('query_vars', 'nettruyen_add_query_vars');

/**
 * Template redirect for chapter pages
 * 
 * FIX: Now properly loads comic post from slug before including template
 */
function nettruyen_chapter_template_redirect()
{
    $chapter_slug = get_query_var('chapter');
    $comic_slug = get_query_var('nettruyen_comic');


    if (!empty($chapter_slug) && !empty($comic_slug)) {


        $comic_post = get_page_by_path($comic_slug, OBJECT, 'nettruyen_comic');

        if ($comic_post) {

            global $post;
            $post = $comic_post;
            setup_postdata($post);


            $template = locate_template('single-chapter.php');

            if ($template) {
                include $template;
                exit;
            } else {

                wp_die('Template single-chapter.php not found. Please create this file in your theme.');
            }
        } else {

            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            get_template_part('404');
            exit;
        }
    }
}
add_action('template_redirect', 'nettruyen_chapter_template_redirect', 1);

/**
 * Auto-flush rewrite rules on first load
 * 
 * FIX: Automatic flush with option flag (no manual intervention needed)
 */
function nettruyen_activate_rewrite_rules()
{

    $flushed = get_option('nettruyen_rewrite_flushed');

    if (!$flushed) {

        nettruyen_add_chapter_rewrite_rules();


        flush_rewrite_rules();


        update_option('nettruyen_rewrite_flushed', '1');


        error_log('TruyenQQ: Rewrite rules flushed successfully');
    }
}
add_action('init', 'nettruyen_activate_rewrite_rules', 999);

/**
 * Clear flush flag on theme switch or deactivation
 * This ensures rules are re-flushed when theme is reactivated
 */
function nettruyen_deactivate_rewrite_rules()
{
    delete_option('nettruyen_rewrite_flushed');
    flush_rewrite_rules();
}
add_action('switch_theme', 'nettruyen_deactivate_rewrite_rules');

/**
 * Helper function to get chapter URL
 * 
 * @param int    $post_id      Comic post ID
 * @param string $chapter_slug Chapter slug (can be: 1, 1-2, 0.5, prologue, etc.)
 * @return string Chapter URL
 */
function nettruyen_get_chapter_url($post_id, $chapter_slug)
{
    $comic_slug = get_post_field('post_name', $post_id);
    return home_url("/truyen-tranh/{$comic_slug}-chap-{$chapter_slug}.html");
}

/**
 * Helper function to get chapter navigation URLs
 * 
 * @param int    $post_id        Comic post ID
 * @param string $chapter_slug   Current chapter slug
 * @return array Array with 'prev' and 'next' URLs (or null if not available)
 */
function nettruyen_get_chapter_navigation($post_id, $chapter_slug)
{

    $chapters_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
    $chapters = [];

    if (!empty($chapters_json)) {
        $manifest = json_decode($chapters_json, true);
        if (isset($manifest['chapters']) && is_array($manifest['chapters'])) {
            $chapters = $manifest['chapters'];
        }
    }


    if (empty($chapters)) {
        $chapters_data = get_post_meta($post_id, '_nettruyen_chapter_manifest', true);
        if (!empty($chapters_data)) {
            $unserialized = maybe_unserialize($chapters_data);
            if (is_array($unserialized)) {
                $chapters = $unserialized;
            }
        }
    }


    $current_index = -1;
    foreach ($chapters as $index => $chapter) {
        if (isset($chapter['slug']) && $chapter['slug'] == $chapter_slug) {
            $current_index = $index;
            break;
        }
    }

    $prev_url = null;
    $next_url = null;


    if ($current_index > 0 && isset($chapters[$current_index - 1]['slug'])) {
        $prev_url = nettruyen_get_chapter_url($post_id, $chapters[$current_index - 1]['slug']);
    }


    if ($current_index >= 0 && isset($chapters[$current_index + 1]['slug'])) {
        $next_url = nettruyen_get_chapter_url($post_id, $chapters[$current_index + 1]['slug']);
    }

    return [
        'prev' => $prev_url,
        'next' => $next_url,
        'current_index' => $current_index,
        'total_chapters' => count($chapters),
        'chapters' => $chapters
    ];
}

/**
 * Enqueue single chapter styles
 */
function nettruyen_enqueue_chapter_styles()
{

    $chapter_slug = get_query_var('chapter');
    $comic_slug = get_query_var('nettruyen_comic');

    if (!empty($chapter_slug) && !empty($comic_slug)) {
        wp_enqueue_style(
            'single-chapter-css',
            get_template_directory_uri() . '/css/single-chapter.css',
            array('toyota-global'),
            '1.0.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'nettruyen_enqueue_chapter_styles');

/**
 * Debug function - Remove after testing
 * Shows current query vars for debugging
 */
function nettruyen_debug_query_vars()
{
    if (current_user_can('administrator') && isset($_GET['debug_rewrite'])) {
        echo '<pre style="background: #000; color: #0f0; padding: 20px; position: fixed; top: 0; right: 0; z-index: 99999; max-width: 400px; overflow: auto;">';
        echo '<strong>Current URL:</strong> ' . $_SERVER['REQUEST_URI'] . "\n\n";
        echo '<strong>Query Vars:</strong>' . "\n";
        global $wp_query;
        print_r($wp_query->query_vars);
        echo "\n<strong>Rewrite Rules (chapter related):</strong>\n";
        global $wp_rewrite;
        $rules = get_option('rewrite_rules');
        foreach ($rules as $pattern => $replacement) {
            if (strpos($pattern, 'truyen-tranh') !== false) {
                echo $pattern . ' => ' . $replacement . "\n";
            }
        }
        echo '</pre>';
    }
}
add_action('wp_footer', 'nettruyen_debug_query_vars');

/**
 * Manual flush rewrite rules (for emergency use via URL)
 * Visit: yourdomain.com/?flush_rewrite=1 (admin only)
 */
function nettruyen_manual_flush_rewrite()
{
    if (current_user_can('administrator') && isset($_GET['flush_rewrite'])) {
        delete_option('nettruyen_rewrite_flushed');
        flush_rewrite_rules();
        wp_die('Rewrite rules flushed! <a href="' . home_url() . '">Go back</a>');
    }
}
add_action('init', 'nettruyen_manual_flush_rewrite', 1);