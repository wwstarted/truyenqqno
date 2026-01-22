<?php
/**
 * Template Name: User Settings (SPA)
 * Description: Quản lý tài khoản & Đổi mật khẩu - Single Page App
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/dang-nhap'));
    exit;
}

get_header();

$current_user = wp_get_current_user();
$is_oauth_user = get_user_meta($current_user->ID, 'oauth_provider', true);
?>

<div class="content">
    <div class="div_middle">
        <!-- Alert Note -->
        <!-- <div class="alert-note">
            <p class="text-center" style="font-weight: bold;font-size: 16px;color:black">
                Đạo hữu ơi! Trước khi mua hàng Shopee, truy cập TruyenQQNo.Com bấm vào link này:
                <a rel="nofollow" href="https://bitly.group/2NG6xh5w" target="_blank"
                    style="font-weight: bold;font-size: 16px;color:black">shopee.vn</a>
                giúp tụi mình nhé. Mỗi lượt click nhỏ giúp QQ có thêm doanh thu để duy trì website miễn phí cho cộng
                đồng.
            </p>
        </div> -->

        <div class="main_content">
            <section class="main-content user-settings-page">
                <div class="container">
                    <div class="messages columns search-option-with-frame">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/tet/tet-search-frame.svg" alt=""
                            class="frame-search-option">

                        <!-- SIDEBAR - Left Navigation -->
                        <div class="column is-narrow col-md-3 col-sm-12">
                            <ul class="nav-user">
                                <li>
                                    <a class="li01 tab-link active" href="#thong-tin" data-tab="user-info">
                                        <i class="fa fa-user-circle"></i> Quản lý tài khoản
                                    </a>
                                </li>
                                <li>
                                    <a class="li03 tab-link" href="#doi-mat-khau" data-tab="change-password">
                                        <i class="fa fa-key"></i> Đổi mật khẩu
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- CONTENT AREA - Dynamic Load -->
                        <div class="column columns col-md-9 col-sm-12">
                            <div id="settings-content-area">
                                <!-- Content will be loaded via AJAX -->
                                <div class="loading-spinner">
                                    <i class="fa fa-spinner fa-spin"></i>
                                    <p>Đang tải...</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>
        </div>
    </div>
    <div class="clear"></div>
</div>

<!-- Hidden data for JavaScript -->
<script type="text/javascript">
var userSettingsData = {
    userId: <?php echo $current_user->ID; ?>,
    isOAuthUser: <?php echo $is_oauth_user ? 'true' : 'false'; ?>,
    oauthProvider: '<?php echo $is_oauth_user ? esc_js($is_oauth_user) : ''; ?>',
    ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
    restUrl: '<?php echo rest_url('nettruyen/v1'); ?>',
    nonce: '<?php echo wp_create_nonce('wp_rest'); ?>',
    templateUrl: '<?php echo get_template_directory_uri(); ?>'
};
</script>

<?php get_footer(); ?>