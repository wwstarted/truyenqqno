<?php
/**
 * Toyota Theme Functions
 * 
 * @package Toyota_Theme
 */

function toyota_enqueue_assets()
{
    wp_enqueue_script('jquery');

    // Global CSS
    wp_enqueue_style('toyota-global', get_template_directory_uri() . '/css/style.css', array(), '1.0.1');
    wp_enqueue_style('toyota-header', get_template_directory_uri() . '/css/header-v1.css', array('toyota-global'), '1.0.1');
    wp_enqueue_style('toyota-footer', get_template_directory_uri() . '/css/footer.css', array('toyota-global'), '1.0.1');

    // Bookmark Badge CSS (load on all pages)
    wp_enqueue_style('bookmark-badge', get_template_directory_uri() . '/css/bookmark-badge.css', array('toyota-global'), '1.0.1');

    // Swiper
    wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11.0.0');
    wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11.0.0', true);

    // ========================================
    // GLOBAL BOOKMARK HANDLER (load everywhere)
    // ========================================
    wp_enqueue_script(
        'truyenqq-bookmarks',
        get_template_directory_uri() . '/js/bookmarks.js',
        array('jquery'),
        '1.0.1',
        true
    );

    // Localize for bookmark script
    wp_localize_script('truyenqq-bookmarks', 'truyenqqConfig', array(
        'apiBase' => esc_url_raw(rest_url('nettruyen/v1')),
        'nonce' => wp_create_nonce('wp_rest'),
        'homeUrl' => esc_url(home_url()),
        'isLoggedIn' => is_user_logged_in()
    ));

    // ========================================
    // BASE LISTING ASSETS
    // ========================================
    $listing_templates = array(
        'page-truyen-moi-cap-nhat.php',
        'page-top-ngay.php',
        'page-top-tuan.php',
        'page-top-thang.php',
        'page-yeu-thich.php',
        'page-truyen-moi.php',
        'page-truyen-full.php',
        'page-ngau-nhien.php'
    );

    $is_listing_page = false;
    foreach ($listing_templates as $template) {
        if (is_page_template($template)) {
            $is_listing_page = true;
            break;
        }
    }

    if ($is_listing_page) {
        wp_enqueue_style(
            'truyen-moi-cap-nhat-base',
            get_template_directory_uri() . '/css/truyen-moi-cap-nhat.css',
            array('toyota-global'),
            '1.0.1'
        );

        wp_enqueue_script(
            'comics-listing-base',
            get_template_directory_uri() . '/js/comics-listing-base.js',
            array('jquery'),
            '1.0.1',
            true
        );
    }

    // ========================================
    // TRUYỆN MỚI CẬP NHẬT
    // ========================================
    if (is_page_template('page-truyen-moi-cap-nhat.php')) {
        wp_enqueue_script(
            'truyen-moi-cap-nhat-js',
            get_template_directory_uri() . '/js/truyen-moi-cap-nhat.js',
            array('comics-listing-base'),
            '1.0.1',
            true
        );

        wp_localize_script('truyen-moi-cap-nhat-js', 'nettruyenData', array(
            'restUrl' => rest_url('nettruyen/v1/comics'),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }

    // ========================================
    // TOP NGÀY
    // ========================================
    if (is_page_template('page-top-ngay.php')) {
        wp_enqueue_style(
            'top-ngay-css',
            get_template_directory_uri() . '/css/top-ngay.css',
            array('truyen-moi-cap-nhat-base'),
            '1.0.1'
        );

        wp_enqueue_script(
            'top-ranking-js',
            get_template_directory_uri() . '/js/top-ranking.js',
            array('comics-listing-base'),
            '1.0.1',
            true
        );

        wp_localize_script('top-ranking-js', 'nettruyenData', array(
            'restUrl' => rest_url('nettruyen/v1/comics/top-ngay'),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }

    // ========================================
    // TOP TUẦN
    // ========================================
    if (is_page_template('page-top-tuan.php')) {
        wp_enqueue_style(
            'top-ngay-css',
            get_template_directory_uri() . '/css/top-ngay.css',
            array('truyen-moi-cap-nhat-base'),
            '1.0.1'
        );

        wp_enqueue_script(
            'top-ranking-js',
            get_template_directory_uri() . '/js/top-ranking.js',
            array('comics-listing-base'),
            '1.0.1',
            true
        );

        wp_localize_script('top-ranking-js', 'nettruyenData', array(
            'restUrl' => rest_url('nettruyen/v1/comics/top-tuan'),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }

    // ========================================
    // TOP THÁNG
    // ========================================
    if (is_page_template('page-top-thang.php')) {
        wp_enqueue_style(
            'top-ngay-css',
            get_template_directory_uri() . '/css/top-ngay.css',
            array('truyen-moi-cap-nhat-base'),
            '1.0.1'
        );

        wp_enqueue_script(
            'top-ranking-js',
            get_template_directory_uri() . '/js/top-ranking.js',
            array('comics-listing-base'),
            '1.0.1',
            true
        );

        wp_localize_script('top-ranking-js', 'nettruyenData', array(
            'restUrl' => rest_url('nettruyen/v1/comics/top-thang'),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }

    // ========================================
    // YÊU THÍCH
    // ========================================
    if (is_page_template('page-yeu-thich.php')) {
        wp_enqueue_style(
            'top-ngay-css',
            get_template_directory_uri() . '/css/top-ngay.css',
            array('truyen-moi-cap-nhat-base'),
            '1.0.1'
        );

        wp_enqueue_script(
            'top-ranking-js',
            get_template_directory_uri() . '/js/top-ranking.js',
            array('comics-listing-base'),
            '1.0.1',
            true
        );

        wp_localize_script('top-ranking-js', 'nettruyenData', array(
            'restUrl' => rest_url('nettruyen/v1/comics/yeu-thich'),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }

    // ========================================
    // TRUYỆN MỚI
    // ========================================
    if (is_page_template('page-truyen-moi.php')) {
        wp_enqueue_script(
            'truyen-moi-cap-nhat-js',
            get_template_directory_uri() . '/js/truyen-moi-cap-nhat.js',
            array('comics-listing-base'),
            '1.0.1',
            true
        );

        wp_localize_script('truyen-moi-cap-nhat-js', 'nettruyenData', array(
            'restUrl' => rest_url('nettruyen/v1/comics/truyen-moi'),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }

    // ========================================
    // TRUYỆN FULL
    // ========================================
    if (is_page_template('page-truyen-full.php')) {
        wp_enqueue_script(
            'truyen-moi-cap-nhat-js',
            get_template_directory_uri() . '/js/truyen-moi-cap-nhat.js',
            array('comics-listing-base'),
            '1.0.1',
            true
        );

        wp_localize_script('truyen-moi-cap-nhat-js', 'nettruyenData', array(
            'restUrl' => rest_url('nettruyen/v1/comics/truyen-full'),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }

    // ========================================
    // NGẪU NHIÊN
    // ========================================
    if (is_page_template('page-ngau-nhien.php')) {
        wp_enqueue_script(
            'truyen-moi-cap-nhat-js',
            get_template_directory_uri() . '/js/truyen-moi-cap-nhat.js',
            array('comics-listing-base'),
            '1.0.1',
            true
        );

        wp_localize_script('truyen-moi-cap-nhat-js', 'nettruyenData', array(
            'restUrl' => rest_url('nettruyen/v1/comics/ngau-nhien'),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }

    // ========================================
    // THEO DÕI PAGE
    // ========================================
    if (is_page_template('page-theo-doi.php')) {
        // CSS cho carousel
        wp_enqueue_style(
            'toyota-front-page',
            get_template_directory_uri() . '/css/front-page-v2.css',
            array('toyota-global'),
            '1.0.1'
        );

        // CSS cho grid layout (reuse từ reading-history)
        wp_enqueue_style(
            'reading-history-css',
            get_template_directory_uri() . '/css/reading-history.css',
            array('toyota-front-page'),
            '1.0.1'
        );

        // JS cho carousel (front-page)
        wp_enqueue_script(
            'toyota-front-page',
            get_template_directory_uri() . '/js/front-page.js',
            array('jquery', 'swiper'),
            '1.0.1',
            true
        );

        // JS riêng cho trang theo dõi
        wp_enqueue_script(
            'theo-doi-js',
            get_template_directory_uri() . '/js/theo-doi.js',
            array('jquery', 'truyenqq-bookmarks'),
            '1.0.1',
            true
        );
    }

    // ========================================
    // LỊCH SỬ PAGE
    // ========================================
    if (is_page_template('page-lich-su.php')) {
        wp_enqueue_style(
            'toyota-front-page',
            get_template_directory_uri() . '/css/front-page-v2.css',
            array('toyota-global'),
            '1.0.1'
        );

        wp_enqueue_style(
            'reading-history-css',
            get_template_directory_uri() . '/css/reading-history.css',
            array('toyota-front-page'),
            '1.0.1'
        );

        wp_enqueue_script(
            'toyota-front-page',
            get_template_directory_uri() . '/js/front-page.js',
            array('jquery', 'swiper'),
            '1.0.1',
            true
        );

        wp_enqueue_script(
            'reading-history-js',
            get_template_directory_uri() . '/js/reading-history.js',
            array('jquery'),
            '1.0.1',
            true
        );

        wp_localize_script('reading-history-js', 'truyenqqConfig', array(
            'apiBase' => esc_url_raw(rest_url('nettruyen/v1')),
            'nonce' => wp_create_nonce('wp_rest'),
            'homeUrl' => esc_url(home_url())
        ));
    }

    // ========================================
    // OTHER PAGES
    // ========================================
    if (is_page_template('page-thuythu.php')) {
        wp_enqueue_style('thuythu', get_template_directory_uri() . '/css/thuythu.css', array('toyota-global'), '1.0.1');
        wp_enqueue_script('thuythu', get_template_directory_uri() . '/js/thuythu.js', array('jquery'), '1.0.1', true);
    }

    if (is_page_template('page-wwdo.php')) {
        wp_enqueue_style('toyota-wwdo', get_template_directory_uri() . '/css/wwdo.css', array('toyota-global'), '1.0.1');
        wp_enqueue_script('toyota-wwdo', get_template_directory_uri() . '/js/wwdo.js', array('jquery'), '1.0.1', true);
    }

    if (is_page_template('page-login.php')) {
        wp_enqueue_style('toyota-login-css', get_template_directory_uri() . '/css/auth-modals.css', array('toyota-global'), '1.0.1');
        wp_enqueue_script('toyota-login-js', get_template_directory_uri() . '/js/auth.js', array('jquery'), '1.0.1', true);
    }

    if (is_front_page()) {
        wp_enqueue_style('toyota-front-page', get_template_directory_uri() . '/css/front-page-v2.css', array('toyota-global'), '1.0.1');
        wp_enqueue_script('toyota-front-page', get_template_directory_uri() . '/js/front-page.js', array('jquery', 'swiper'), '1.0.1', true);
    }

    if (is_tax('nettruyen_genre')) {
        wp_enqueue_style('nettruyen-genre-listing', get_template_directory_uri() . '/css/the-loai.css', array('toyota-global'), '1.0.1');
        wp_enqueue_script('nettruyen-genre-listing', get_template_directory_uri() . '/js/the-loai.js', array('jquery'), '1.0.1', true);
        wp_localize_script('nettruyen-genre-listing', 'nettruyenGenreData', array(
            'restUrl' => rest_url('nettruyen/v1/comics/genre'),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }

    if (is_page_template('page-advanced-search.php')) {
        wp_enqueue_style(
            'truyen-moi-cap-nhat-base',
            get_template_directory_uri() . '/css/truyen-moi-cap-nhat.css',
            array('toyota-global'),
            '1.0.2'
        );

        wp_enqueue_style(
            'advanced-search-css',
            get_template_directory_uri() . '/css/advanced-search.css',
            array('truyen-moi-cap-nhat-base'),
            '1.0.2'
        );

        wp_enqueue_script(
            'advanced-search-js',
            get_template_directory_uri() . '/js/advanced-search.js',
            array('jquery'),
            '1.0.2',
            true
        );

        wp_localize_script('advanced-search-js', 'nettruyenData', array(
            'restUrl' => rest_url('nettruyen/v1/advanced-search'),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }

    // Header & Footer JS
    wp_enqueue_script('toyota-header', get_template_directory_uri() . '/js/header.js', array('jquery'), '1.0.1', true);
    wp_localize_script('toyota-header', 'TRUYENQQ_CONFIG', array(
        'restUrl' => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest'),
    ));

    wp_enqueue_script('toyota-footer', get_template_directory_uri() . '/js/footer.js', array('jquery'), '1.0.1', true);

    // Single Comic & Chapter
    if (is_singular('nettruyen_comic')) {
        wp_enqueue_style('single-comic-css', get_template_directory_uri() . '/css/single-comic.css', array('toyota-global'), '1.0.1');

        if (get_query_var('chapter')) {
            wp_enqueue_style('chapter', get_template_directory_uri() . '/css/single-chapter.css', array('toyota-global'), '1.0.1');

            wp_enqueue_script(
                'single-chapter-js',
                get_template_directory_uri() . '/js/single-chapter.js',
                array('jquery'),
                '1.0.0',
                true
            );
        }

        if (is_user_logged_in()) {
            wp_localize_script('single-chapter-js', 'wpApiSettings', array(
                'root' => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest')
            ));
        }
    }

    // Auth pages
    wp_enqueue_style('truyenqq-auth', get_template_directory_uri() . '/css/auth-pages.css', array(), '1.0.1');
    wp_enqueue_script('truyenqq-auth', get_template_directory_uri() . '/js/auth-pages.js', array('jquery'), '1.0.1', true);
}
add_action('wp_enqueue_scripts', 'toyota_enqueue_assets');

add_filter('logout_redirect', function ($redirect_to, $requested_redirect_to, $user) {
    return home_url('/dang-nhap');
}, 10, 3);

// Include files
require_once get_template_directory() . '/inc/auth-db-migration.php';
require_once get_template_directory() . '/inc/api-user-auth.php';
require_once get_template_directory() . '/inc/class-truyenqq-otp-manager.php';
require_once get_template_directory() . '/inc/class-truyenqq-auth-handler.php';
require_once get_template_directory() . '/inc/ajax-handlers-with-recaptcha.php';
require_once get_template_directory() . '/inc/class-nettruyen-comics-rest-api.php';
require_once get_template_directory() . '/inc/class-advanced-search-api.php';
require_once get_template_directory() . '/inc/single-nettruyen-comics.php';
require_once get_template_directory() . '/inc/class-top-comics-api.php';
require_once get_template_directory() . '/inc/create-reading-history-table.php';
require_once get_template_directory() . '/inc/reading-history-api.php';

// ========================================
// BOOKMARK SYSTEM
// ========================================
require_once get_template_directory() . '/inc/create-bookmarks-table.php';
require_once get_template_directory() . '/inc/bookmarks-api.php';

/**
 * Helper function: Render bookmark badge with proper state
 * 
 * @param int $post_id Post ID
 * @param bool|null $is_bookmarked Override bookmark state (null = auto-check)
 */
function truyenqq_render_bookmark_badge($post_id, $is_bookmarked = null)
{
    if ($is_bookmarked === null && is_user_logged_in()) {
        $is_bookmarked = truyenqq_is_bookmarked($post_id);
    }

    $icon_class = $is_bookmarked ? 'fa-bookmark' : 'fa-bookmark-o';
    $active_class = $is_bookmarked ? 'active' : '';
    $title = $is_bookmarked ? 'Bỏ theo dõi' : 'Theo dõi';

    echo sprintf(
        '<span class="bookmark-badge %s" title="%s" data-post-id="%d">
            <i class="fa %s"></i>
        </span>',
        esc_attr($active_class),
        esc_attr($title),
        (int) $post_id,
        esc_attr($icon_class)
    );
}
// // Force create reading history table on init
// add_action('init', function () {
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'nettruyen_reading_history';

//     // Check if table exists
//     if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
//         truyenqq_create_reading_history_table();
//     }
// }, 5);

// Theme setup và các functions khác giữ nguyên...

add_action('init', function () {
    static $flushed = false;
    if (!$flushed && get_option('truyenqq_api_flushed') !== 'yes') {
        flush_rewrite_rules();
        update_option('truyenqq_api_flushed', 'yes');
        $flushed = true;
    }
}, 999);


function nettruyen_enqueue_comics_listing_scripts()
{
    if (is_page_template('page-truyen-moi-cap-nhat.php')) {
        wp_enqueue_script(
            'nettruyen-comics-listing',
            get_template_directory_uri() . '/js/comics-listing.js',
            array(),
            '1.0.0',
            true
        );

        wp_localize_script('nettruyen-comics-listing', 'nettruyenData', array(
            'restUrl' => rest_url('nettruyen/v1/comics'),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }
}
add_action('wp_enqueue_scripts', 'nettruyen_enqueue_comics_listing_scripts');

function toyota_theme_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height' => 100,
        'width' => 400,
        'flex-height' => true,
        'flex-width' => true,
    ));
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'toyota-theme'),
        'footer' => __('Footer Menu', 'toyota-theme'),
    ));
}
add_action('after_setup_theme', 'toyota_theme_setup');


function toyota_register_products_cpt()
{
    $labels = array(
        'name' => 'Sản phẩm',
        'singular_name' => 'Sản phẩm',
        'menu_name' => 'Sản phẩm',
        'add_new' => 'Thêm mới',
        'add_new_item' => 'Thêm sản phẩm mới',
        'edit_item' => 'Sửa sản phẩm',
        'new_item' => 'Sản phẩm mới',
        'view_item' => 'Xem sản phẩm',
        'search_items' => 'Tìm sản phẩm',
        'not_found' => 'Không tìm thấy sản phẩm',
        'not_found_in_trash' => 'Không có sản phẩm trong thùng rác',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-car',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'rewrite' => array('slug' => 'san-pham'),
        'capability_type' => 'post',
    );

    register_post_type('product', $args);
}
add_action('init', 'toyota_register_products_cpt');

function toyota_add_featured_image_to_rest()
{
    register_rest_field('product', 'featured_media_url', array(
        'get_callback' => function ($post) {
            $image_id = get_post_thumbnail_id($post['id']);
            if ($image_id) {
                $image = wp_get_attachment_image_src($image_id, 'medium');
                return $image ? $image[0] : '';
            }
            return '';
        },
        'schema' => array(
            'description' => 'Featured image URL',
            'type' => 'string',
        ),
    ));

    register_rest_field('post', 'featured_media_url', array(
        'get_callback' => function ($post) {
            $image_id = get_post_thumbnail_id($post['id']);
            if ($image_id) {
                $image = wp_get_attachment_image_src($image_id, 'medium');
                return $image ? $image[0] : '';
            }
            return '';
        },
        'schema' => array(
            'description' => 'Featured image URL',
            'type' => 'string',
        ),
    ));
}
add_action('rest_api_init', 'toyota_add_featured_image_to_rest');


function toyota_register_search_endpoint()
{
    register_rest_route('toyota/v1', '/search', array(
        'methods' => 'GET',
        'callback' => 'toyota_search_callback',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'toyota_register_search_endpoint');



function toyota_search_callback($request)
{
    $query = sanitize_text_field($request->get_param('q'));

    if (empty($query)) {
        return new WP_Error('empty_query', 'Search query is required', array('status' => 400));
    }

    $products = get_posts(array(
        'post_type' => 'product',
        'posts_per_page' => 5,
        's' => $query,
    ));

    $posts = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => 5,
        's' => $query,
    ));

    return array(
        'products' => $products,
        'posts' => $posts,
    );
}

function toyota_register_sidebars()
{
    register_sidebar(array(
        'name' => 'Blog Sidebar',
        'id' => 'sidebar-blog',
        'description' => 'Sidebar hiển thị ở trang blog',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));

    register_sidebar(array(
        'name' => 'Footer Widget 1',
        'id' => 'footer-1',
        'description' => 'Footer column 1',
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="footer-widget-title">',
        'after_title' => '</h4>',
    ));

    register_sidebar(array(
        'name' => 'Footer Widget 2',
        'id' => 'footer-2',
        'description' => 'Footer column 2',
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="footer-widget-title">',
        'after_title' => '</h4>',
    ));

    register_sidebar(array(
        'name' => 'Footer Widget 3',
        'id' => 'footer-3',
        'description' => 'Footer column 3',
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="footer-widget-title">',
        'after_title' => '</h4>',
    ));
}
add_action('widgets_init', 'toyota_register_sidebars');


function toyota_get_product_price($post_id)
{
    $price = get_post_meta($post_id, 'product_price', true);
    return $price ? number_format($price) . ' VNĐ' : '';
}

function toyota_format_phone($phone)
{
    return preg_replace('/(\d{4})(\d{3})(\d{3,4})/', '$1 $2 $3', $phone);
}

function toyota_remove_wp_defaults()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');

    remove_action('wp_head', 'wp_generator');

    remove_action('wp_head', 'rsd_link');

    remove_action('wp_head', 'wlwmanifest_link');
}
add_action('init', 'toyota_remove_wp_defaults');

function toyota_excerpt_length($length)
{
    return 30;
}
add_filter('excerpt_length', 'toyota_excerpt_length');

function toyota_excerpt_more($more)
{
    return '...';
}
add_filter('excerpt_more', 'toyota_excerpt_more');


add_action('rest_api_init', 'nettruyen_register_search_endpoint');

function nettruyen_register_search_endpoint()
{
    register_rest_route('nettruyen/v1', '/search', array(
        'methods' => 'GET',
        'callback' => 'nettruyen_search_comics',
        'permission_callback' => '__return_true',
        'args' => array(
            'q' => array(
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ));
}

function nettruyen_search_comics($request)
{
    $keyword = $request->get_param('q');

    if (empty($keyword)) {
        return rest_ensure_response(array());
    }

    $args = array(
        'post_type' => 'nettruyen_comic',
        's' => $keyword,
        'posts_per_page' => 20,
        'post_status' => 'publish'
    );

    $query = new WP_Query($args);
    $results = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
            $manifest = !empty($manifest_json) ? json_decode($manifest_json, true) : null;

            $latest_chapter = '';
            if (!empty($manifest['chapters'])) {
                $chapters = $manifest['chapters'];
                $latest = end($chapters);
                $latest_chapter = 'Chương ' . $latest['name'];
            }

            $thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);

            if (empty($thumbnail)) {
                $thumbnail = get_the_post_thumbnail_url($post_id, 'thumbnail');
            }

            $alternative_title = get_post_meta($post_id, '_nettruyen_alternative_title', true);

            $results[] = array(
                'id' => $post_id,
                'title' => get_the_title(),
                'alternative_title' => $alternative_title ? $alternative_title : '',
                'thumbnail' => $thumbnail ? $thumbnail : '',
                'latest_chapter' => $latest_chapter,
                'slug' => get_post_field('post_name', $post_id),
                'link' => get_permalink($post_id)
            );
        }
    }

    wp_reset_postdata();

    return rest_ensure_response($results);
}

require_once get_template_directory() . '/inc/nettruyen-view-migration.php';


function nettruyen_run_view_migration()
{
    if (!NetTruyen_View_Migration::needs_migration()) {
        return;
    }

    $result = NetTruyen_View_Migration::run();

    if ($result) {
        set_transient('nettruyen_migration_success', true, 60);
        error_log('NetTruyen Migration: SUCCESS');
    } else {
        set_transient('nettruyen_migration_error', true, 60);
        error_log('NetTruyen Migration: FAILED');
    }
}

add_action('after_switch_theme', 'nettruyen_run_view_migration');

add_action('admin_init', function () {
    if (NetTruyen_View_Migration::needs_migration()) {
        nettruyen_run_view_migration();
    }
});

add_action('admin_notices', 'nettruyen_migration_notices');

function nettruyen_migration_notices()
{
    if (get_transient('nettruyen_migration_success')) {
        ?>
<div class="notice notice-success is-dismissible">
    <p><strong>✅ NetTruyen View System:</strong> Database tables created successfully!</p>
    <p>
        <a href="<?php echo admin_url('tools.php?page=nettruyen-migration-debug'); ?>" class="button button-primary">
            🔍 View Migration Status
        </a>
    </p>
</div>
<?php
        delete_transient('nettruyen_migration_success');
    }

    if (get_transient('nettruyen_migration_error')) {
        ?>
<div class="notice notice-error is-dismissible">
    <p><strong>❌ NetTruyen View System:</strong> Failed to create database tables!</p>
    <p>Check error log at: <code>wp-content/debug.log</code></p>
    <p>
        <a href="<?php echo admin_url('tools.php?page=nettruyen-migration-debug'); ?>" class="button button-secondary">
            🔧 Debug Migration
        </a>
    </p>
</div>
<?php
        delete_transient('nettruyen_migration_error');
    }
}

add_action('admin_menu', 'nettruyen_migration_debug_menu');

function nettruyen_migration_debug_menu()
{
    add_management_page(
        'NetTruyen Migration Debug',
        'NetTruyen Migration',
        'manage_options',
        'nettruyen-migration-debug',
        'nettruyen_migration_debug_page'
    );
}

function nettruyen_migration_debug_page()
{
    global $wpdb;

    if (isset($_POST['force_migration']) && check_admin_referer('nettruyen_migration_debug')) {
        delete_option('nettruyen_view_migration_version');
        nettruyen_run_view_migration();
        echo '<div class="notice notice-info"><p>🔄 Migration re-run triggered!</p></div>';
    }

    if (isset($_POST['drop_tables']) && check_admin_referer('nettruyen_migration_debug')) {
        NetTruyen_View_Migration::rollback();
        echo '<div class="notice notice-warning"><p>⚠️ Tables dropped!</p></div>';
    }

    $needs_migration = NetTruyen_View_Migration::needs_migration();
    $version = get_option('nettruyen_view_migration_version', 'Not installed');
    $date = get_option('nettruyen_view_migration_date', 'N/A');

    $tables = array(
        'Chapter Views' => $wpdb->prefix . 'nettruyen_chapter_views',
        'Comic Views' => $wpdb->prefix . 'nettruyen_comic_views',
        'View Stats' => $wpdb->prefix . 'nettruyen_view_stats'
    );

    ?>
<div class="wrap">
    <h1>🔧 NetTruyen View System - Migration Debug</h1>

    <!-- Status Card -->
    <div class="card" style="max-width: 800px; margin: 20px 0;">
        <h2>📊 Migration Status</h2>
        <table class="form-table">
            <tr>
                <th style="width: 200px;">Status:</th>
                <td>
                    <?php if ($needs_migration): ?>
                    <span style="color: orange; font-weight: bold;">⚠️ NOT INSTALLED</span>
                    <?php else: ?>
                    <span style="color: green; font-weight: bold;">✅ INSTALLED</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Version:</th>
                <td><code><?php echo esc_html($version); ?></code></td>
            </tr>
            <tr>
                <th>Install Date:</th>
                <td><?php echo esc_html($date); ?></td>
            </tr>
            <tr>
                <th>Migration File:</th>
                <td>
                    <?php
                        $file_path = get_template_directory() . '/inc/nettruyen-view-migration.php';
                        if (file_exists($file_path)): ?>
                    <span style="color: green;">✅ Found</span>
                    <code><?php echo esc_html($file_path); ?></code>
                    <?php else: ?>
                    <span style="color: red;">❌ NOT FOUND</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- Tables Info -->
    <div class="card" style="max-width: 800px; margin: 20px 0;">
        <h2>🗄️ Database Tables</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Table Name</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 100px;">Rows</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tables as $name => $table):
                        $exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'") == $table;
                        $rows = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM {$table}") : 0;
                        ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($name); ?></strong><br>
                        <code style="font-size: 11px;"><?php echo esc_html($table); ?></code>
                    </td>
                    <td>
                        <?php if ($exists): ?>
                        <span style="color: green; font-weight: bold;">✅ OK</span>
                        <?php else: ?>
                        <span style="color: red; font-weight: bold;">❌ Missing</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo number_format($rows); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Actions -->
    <div class="card" style="max-width: 800px; margin: 20px 0;">
        <h2>⚡ Actions</h2>

        <form method="post" style="margin-bottom: 15px;">
            <?php wp_nonce_field('nettruyen_migration_debug'); ?>
            <button type="submit" name="force_migration" class="button button-primary">
                🚀 Force Run Migration
            </button>
            <p class="description">
                Chạy lại migration (sẽ tạo hoặc update tables)
            </p>
        </form>

        <form method="post" onsubmit="return confirm('⚠️ This will DELETE all tables and data! Continue?');">
            <?php wp_nonce_field('nettruyen_migration_debug'); ?>
            <button type="submit" name="drop_tables" class="button button-secondary">
                🗑️ Drop All Tables
            </button>
            <p class="description" style="color: red;">
                Xóa toàn bộ tables (chỉ dùng khi cần reset)
            </p>
        </form>
    </div>

    <!-- Debug Info -->
    <div class="card" style="max-width: 800px; margin: 20px 0;">
        <h2>🐛 Debug Info</h2>
        <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto; font-size: 12px;"><?php
            echo "WordPress Version: " . get_bloginfo('version') . "\n";
            echo "PHP Version: " . PHP_VERSION . "\n";
            echo "MySQL Version: " . $wpdb->db_version() . "\n";
            echo "Theme Directory: " . get_template_directory() . "\n";
            echo "Database Prefix: " . $wpdb->prefix . "\n";
            echo "\nWP Options:\n";
            echo "- nettruyen_view_migration_version: " . get_option('nettruyen_view_migration_version', 'NULL') . "\n";
            echo "- nettruyen_view_migration_date: " . get_option('nettruyen_view_migration_date', 'NULL') . "\n";
            ?></pre>
    </div>
</div>
<?php
}


add_action('admin_menu', 'nettruyen_population_tool_menu');

function nettruyen_population_tool_menu()
{
    add_management_page(
        'NetTruyen View Population',
        'View Population',
        'manage_options',
        'nettruyen-view-population',
        'nettruyen_population_tool_page'
    );
}

function nettruyen_population_tool_page()
{
    global $wpdb;

    require_once get_template_directory() . '/inc/nettruyen-view-population.php';

    if (isset($_POST['start_populate']) && check_admin_referer('nettruyen_population')) {
        $batch_size = 50;
        $offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0;

        $result = NetTruyen_View_Population::populate_all($batch_size, $offset);

        if ($result['success']) {
            if ($result['is_complete']) {
                echo '<div class="notice notice-success"><p>✅ Population completed! Processed ' . $result['processed'] . ' comics.</p></div>';
            } else {
                ?>
<div class="notice notice-info">
    <p>⏳ Processing...
        <?php echo $result['message']; ?>
    </p>
</div>
<script>
setTimeout(function() {
    document.getElementById('offset_input').value = <?php echo $result['offset']; ?>;
    document.getElementById('populate_form').submit();
}, 1000);
</script>
<?php
            }
        } else {
            echo '<div class="notice notice-error"><p>❌ Error: ' . esc_html($result['message']) . '</p></div>';
        }
    }

    if (isset($_POST['reset_all']) && check_admin_referer('nettruyen_population')) {
        NetTruyen_View_Population::reset_all();
        echo '<div class="notice notice-warning"><p>⚠️ All fake views data has been reset!</p></div>';
    }

    $stats_table = $wpdb->prefix . 'nettruyen_view_stats';
    $total_in_db = $wpdb->get_var("SELECT COUNT(*) FROM {$stats_table}");
    $total_comics = wp_count_posts('nettruyen_comic')->publish;
    $total_fake_views = $wpdb->get_var("SELECT SUM(total_fake_views) FROM {$stats_table}");
    $avg_fake_views = $total_in_db > 0 ? ($total_fake_views / $total_in_db) : 0;

    ?>
<div class="wrap">
    <h1>📊 NetTruyen View Population Tool</h1>

    <div class="card" style="max-width: 800px; margin: 20px 0;">
        <h2>Current Status</h2>
        <table class="form-table">
            <tr>
                <th style="width: 250px;">Total Comics:</th>
                <td><strong>
                        <?php echo number_format($total_comics); ?>
                    </strong></td>
            </tr>
            <tr>
                <th>Comics with Fake Views:</th>
                <td>
                    <strong>
                        <?php echo number_format($total_in_db); ?>
                    </strong>
                    <?php if ($total_in_db < $total_comics): ?>
                    <span style="color: orange;">(
                        <?php echo number_format($total_comics - $total_in_db); ?> pending)
                    </span>
                    <?php else: ?>
                    <span style="color: green;">✅ All populated</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Total Fake Views:</th>
                <td><strong>
                        <?php echo number_format($total_fake_views); ?>
                    </strong></td>
            </tr>
            <tr>
                <th>Average Fake Views/Comic:</th>
                <td><strong>
                        <?php echo number_format($avg_fake_views, 0); ?>
                    </strong></td>
            </tr>
        </table>
    </div>

    <div class="card" style="max-width: 800px; margin: 20px 0;">
        <h2>Actions</h2>

        <form method="post" id="populate_form">
            <?php wp_nonce_field('nettruyen_population'); ?>
            <input type="hidden" name="offset" id="offset_input" value="0">

            <p>
                <button type="submit" name="start_populate" class="button button-primary button-large">
                    🚀
                    <?php echo $total_in_db < $total_comics ? 'Start' : 'Re-run'; ?> Population
                </button>
            </p>
            <p class="description">
                This will calculate and store fake views for all comics.<br>
                Processing in batches of 50 to avoid timeout.
            </p>
        </form>

        <hr style="margin: 30px 0;">

        <form method="post" onsubmit="return confirm('⚠️ This will DELETE all fake views data! Continue?');">
            <?php wp_nonce_field('nettruyen_population'); ?>
            <p>
                <button type="submit" name="reset_all" class="button button-secondary">
                    🗑️ Reset All Data
                </button>
            </p>
            <p class="description" style="color: red;">
                Xóa toàn bộ dữ liệu fake views (chỉ dùng khi muốn tính lại từ đầu)
            </p>
        </form>
    </div>

    <!-- Sample Data -->
    <div class="card" style="max-width: 800px; margin: 20px 0;">
        <h2>📈 Sample Data (Top 10 Comics)</h2>
        <?php
            $samples = $wpdb->get_results("
                SELECT s.*, p.post_title 
                FROM {$stats_table} s
                LEFT JOIN {$wpdb->posts} p ON s.post_id = p.ID
                ORDER BY s.total_fake_views DESC
                LIMIT 10
            ");

            if ($samples):
                ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Comic</th>
                    <th style="width: 150px;">Fake Views</th>
                    <th style="width: 150px;">Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($samples as $row): ?>
                <tr>
                    <td>
                        <strong>
                            <?php echo esc_html($row->post_title); ?>
                        </strong><br>
                        <small>ID:
                            <?php echo $row->post_id; ?>
                        </small>
                    </td>
                    <td>
                        <?php echo number_format($row->total_fake_views); ?>
                    </td>
                    <td>
                        <?php echo $row->fake_updated_at; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No data yet. Click "Start Population" above.</p>
        <?php endif; ?>
    </div>
</div>
<?php
}




require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';
require_once get_template_directory() . '/inc/class-nettruyen-view-api.php';

add_action('wp_enqueue_scripts', 'nettruyen_enqueue_view_tracker');

function nettruyen_enqueue_view_tracker()
{
    $should_load = true;



    if (!$should_load) {
        return;
    }

    wp_enqueue_script(
        'nettruyen-view-tracker',
        get_template_directory_uri() . '/js/nettruyen-view-tracker.js',
        array(),
        '1.0.0',
        true
    );

    wp_localize_script('nettruyen-view-tracker', 'NettruyenViewTracker', array(
        'restUrl' => rest_url('nettruyen/v1/track-view'),
        'nonce' => wp_create_nonce('wp_rest')
    ));
}

add_filter('query_vars', 'nettruyen_add_chapter_query_var');

function nettruyen_add_chapter_query_var($vars)
{
    $vars[] = 'chapter';
    return $vars;
}


/**
 * Get và display view count
 * 
 * @param int $post_id Optional. Default current post.
 * @param bool $echo Echo hoặc return
 * @return string|void
 */
function nettruyen_get_view_count($post_id = null, $echo = true)
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $stats = NetTruyen_View_Tracker::get_stats($post_id);

    if (!$stats) {
        return $echo ? '' : 0;
    }

    $count = number_format($stats['total_display_views']);

    if ($echo) {
        echo esc_html($count);
    } else {
        return $count;
    }
}

/**
 * Display view count với icon
 * 
 * @param int $post_id Optional
 */
function nettruyen_display_view_count($post_id = null)
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $stats = NetTruyen_View_Tracker::get_stats($post_id);

    if (!$stats) {
        return;
    }

    ?>
<span class="view-count">
    <i class="icon-eye"></i>
    <?php echo number_format($stats['total_display_views']); ?> lượt xem
</span>
<?php
}

/**
 * Display full stats box
 * 
 * @param int $post_id Optional
 */
function nettruyen_display_stats_box($post_id = null)
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $stats = NetTruyen_View_Tracker::get_stats($post_id);

    if (!$stats) {
        return;
    }

    ?>
<div class="nettruyen-stats-box">
    <div class="stat-item">
        <strong><?php echo number_format($stats['total_display_views']); ?></strong>
        <span>Tổng lượt xem</span>
    </div>
    <div class="stat-item">
        <strong><?php echo number_format($stats['daily_views']); ?></strong>
        <span>Hôm nay</span>
    </div>
    <div class="stat-item">
        <strong><?php echo number_format($stats['weekly_views']); ?></strong>
        <span>7 ngày qua</span>
    </div>
    <div class="stat-item">
        <strong><?php echo number_format($stats['monthly_views']); ?></strong>
        <span>30 ngày qua</span>
    </div>
</div>
<?php
}

/**
 * Check if should track view (PHP fallback)
 * Gọi trong template nếu muốn track bằng PHP thay vì JS
 * 
 * @param int $post_id
 * @param string $chapter_slug
 */
function nettruyen_track_view_php($post_id, $chapter_slug)
{
    if (!$post_id || !$chapter_slug) {
        return;
    }

    NetTruyen_View_Tracker::track_chapter_view($post_id, $chapter_slug);
}

add_filter('manage_nettruyen_comic_posts_columns', 'nettruyen_add_views_column');

function nettruyen_add_views_column($columns)
{
    $new_columns = array();

    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;

        if ($key === 'title') {
            $new_columns['views'] = '<i class="dashicons dashicons-visibility"></i> Views';
        }
    }

    return $new_columns;
}

add_action('manage_nettruyen_comic_posts_custom_column', 'nettruyen_show_views_column', 10, 2);

function nettruyen_show_views_column($column, $post_id)
{
    if ($column === 'views') {
        $stats = NetTruyen_View_Tracker::get_stats($post_id);

        if ($stats) {
            echo '<strong>' . number_format($stats['total_display_views']) . '</strong>';
            echo '<br><small style="color: #666;">';
            echo 'Real: ' . number_format($stats['total_real_views']);

            if ($stats['use_fake_views']) {
                echo ' | Fake: ' . number_format($stats['total_fake_views']);
            }

            echo '</small>';
        } else {
            echo '<span style="color: #999;">—</span>';
        }
    }
}

add_filter('manage_edit-nettruyen_comic_sortable_columns', 'nettruyen_make_views_sortable');

function nettruyen_make_views_sortable($columns)
{
    $columns['views'] = 'views';
    return $columns;
}


add_action('wp', 'nettruyen_schedule_view_cleanup');

function nettruyen_schedule_view_cleanup()
{
    if (!wp_next_scheduled('nettruyen_cleanup_old_views')) {
        wp_schedule_event(time(), 'weekly', 'nettruyen_cleanup_old_views');
    }
}

add_action('nettruyen_cleanup_old_views', 'nettruyen_do_cleanup_old_views');

function nettruyen_do_cleanup_old_views()
{
    global $wpdb;

    $chapter_table = $wpdb->prefix . 'nettruyen_chapter_views';
    $comic_table = $wpdb->prefix . 'nettruyen_comic_views';

    $wpdb->query("
        DELETE FROM {$chapter_table} 
        WHERE view_date < DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    ");

    $wpdb->query("
        DELETE FROM {$comic_table} 
        WHERE view_date < DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    ");

    error_log('NetTruyen: Old view data cleaned up');
}

add_action('init', 'nettruyen_start_session');

function nettruyen_start_session()
{
    if (!session_id()) {
        session_start();
    }
}





add_action('init', 'custom_register_country_taxonomy');
function custom_register_country_taxonomy()
{
    $labels = array(
        'name' => 'Quốc gia',
        'singular_name' => 'Quốc gia',
        'search_items' => 'Tìm quốc gia',
        'all_items' => 'Tất cả quốc gia',
        'edit_item' => 'Sửa quốc gia',
        'update_item' => 'Cập nhật',
        'add_new_item' => 'Thêm quốc gia mới',
        'new_item_name' => 'Tên quốc gia mới',
        'menu_name' => 'Quốc gia',
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'quoc-gia'),
    );

    register_taxonomy('nettruyen_country', array('nettruyen_comic'), $args);
}


add_action('admin_init', 'auto_migrate_genre_to_country');
function auto_migrate_genre_to_country()
{
    if (!isset($_GET['run_country_migration']) || $_GET['run_country_migration'] != '1') {
        return;
    }
    if (!current_user_can('manage_options')) {
        return;
    }

    $countries = array(
        'China' => 'Trung Quốc',
        'Korea' => 'Hàn Quốc',
        'Japan' => 'Nhật Bản',
        'Vietnam' => 'Việt Nam'
    );

    foreach ($countries as $slug => $name) {
        if (!term_exists($name, 'nettruyen_country')) {
            wp_insert_term($name, 'nettruyen_country', array('slug' => $slug));
        }
    }

    $args = array(
        'post_type' => 'nettruyen_comic',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => true,
    );

    $comics = get_posts($args);
    $count = 0;

    foreach ($comics as $post_id) {
        $genres = wp_get_post_terms($post_id, 'nettruyen_genre', array('fields' => 'slugs'));

        if (is_wp_error($genres) || empty($genres))
            continue;

        $target_country = '';

        if (in_array('manhua', $genres)) {
            $target_country = 'China';
        } elseif (in_array('manhwa', $genres)) {
            $target_country = 'Korea';
        } elseif (in_array('manga', $genres)) {
            $target_country = 'Japan';
        }

        if (!empty($target_country)) {
            $term = get_term_by('slug', $target_country, 'nettruyen_country');
            if ($term) {
                wp_set_object_terms($post_id, (int) $term->term_id, 'nettruyen_country');
                $count++;
            }
        }
    }

    echo '<div class="notice notice-success is-dismissible"><p><strong>Đã xử lý xong!</strong> Tổng cộng ' . $count . ' truyện đã được cập nhật Quốc gia tự động.</p></div>';
}

// ============================ add asset register for bookmarks =======================