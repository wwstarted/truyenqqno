<?php
/**
 * Auth Pages Header
 * Minimal header for login/register/forgot password pages
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Title -->
    <title>
        <?php wp_title('|', true, 'right');
        bloginfo('name'); ?>
    </title>

    <!-- Favicon -->
    <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/images/favicon.ico" type="image/x-icon">

    <!-- WordPress Head Hook -->
    <?php wp_head(); ?>

    <!-- Dark Mode Detection -->
    <script>
    (function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    })();
    </script>
</head>

<body <?php body_class('auth-page'); ?>>
    <?php wp_body_open(); ?>