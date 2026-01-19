<?php


if (
    is_page_template('template-login.php') ||
    is_page_template('template-register.php') ||
    is_page_template('template-forgot-password.php')
) {
    wp_enqueue_script(
        'google-recaptcha',
        'https://www.google.com/recaptcha/api.js',
        array(),
        null,
        true
    );

    // Auth CSS
    wp_enqueue_style(
        'truyenqq-auth-pages',
        get_template_directory_uri() . '/css/auth-pages.css',
        array(),
        '2.1.0'
    );

    // Auth JS
    wp_enqueue_script(
        'truyenqq-auth-pages',
        get_template_directory_uri() . '/js/auth-pages.js',
        array('jquery'),
        '2.1.0',
        true
    );

    // Localize script
    wp_localize_script('truyenqq-auth-pages', 'truyenqqAuth', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('truyenqq_auth_nonce'),
        'is_logged_in' => is_user_logged_in(),
        'current_user' => TruyenQQ_Auth_Handler::get_current_user()
    ));

}

// Google reCAPTCHA