<?php
/**
 * Header Template - TruyenQQ
 * Updated with User Menu Logic
 */

// Lấy thông tin user hiện tại để hiển thị ngay lập tức
$is_user_logged_in = is_user_logged_in();
$current_user = wp_get_current_user();
$display_name = $is_user_logged_in ? $current_user->display_name : 'Khách';
$user_email = $is_user_logged_in ? $current_user->user_email : '';

// Logic lấy Avatar (giống với file api-user-auth.php của bạn)
$avatar_url = get_avatar_url($current_user->ID, array('size' => 100));
if (!$is_user_logged_in || empty($avatar_url) || strpos($avatar_url, 'gravatar') !== false) {
    $avatar_url = 'https://th.bing.com/th/id/OIP.ItvA9eX1ZIYT8NHePqeuCgHaHa?w=159&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3';
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<?php if (is_front_page()): ?>
<link rel="preload" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" as="style"
    onload="this.onload=null;this.rel='stylesheet'">
<noscript>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
</noscript>

<style>
/* Critical CSS - Hide carousel until Swiper loads */
.truyen-hay-swiper,
.exclusive-swiper {
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.truyen-hay-swiper.swiper-initialized,
.exclusive-swiper.swiper-initialized {
    visibility: visible;
    opacity: 1;
}

/* Skeleton loader (optional) */
.homepage-suggest .swiper-wrapper,
.homepage-exclusive .swiper-wrapper {
    min-height: 340px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s ease-in-out infinite;
}

@keyframes skeleton-loading {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}
</style>
<?php endif; ?>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <header class="site-header">
        <div class="header-top">
            <div class="container">
                <div class="header-logo">
                    <a href="<?php echo home_url('/'); ?>" title="Truyện tranh online">
                        <img src="https://st.truyenqqno.com/template/frontend/images/logo.png" alt="TruyenQQ"
                            class="logo-desktop">
                        <img src="https://st.truyenqqno.com/template/frontend/images/logo-icon.png" alt="TruyenQQ"
                            class="logo-mobile">
                    </a>
                </div>

                <button class="dark-mode-toggle" id="darkModeToggle" title="Chế độ tối/sáng">
                    <i class="fa fa-lightbulb-o"></i>
                </button>

                <div class="header-actions">
                    <button class="search-icon-btn mobile-tablet-only" id="mobileSearchToggle">
                        <i class="fa fa-search"></i>
                    </button>

                    <div class="auth-buttons" id="authButtons"
                        style="<?php echo $is_user_logged_in ? 'display: none;' : 'display: flex;'; ?>">
                        <button class="btn-register"
                            onclick="window.location.href='<?php echo home_url('/dang-ky'); ?>'">
                            Đăng ký
                        </button>
                        <button class="btn-login"
                            onclick="window.location.href='<?php echo home_url('/dang-nhap'); ?>'">
                            Đăng nhập
                        </button>
                    </div>

                    <div class="user-menu" id="userMenu"
                        style="<?php echo $is_user_logged_in ? 'display: flex;' : 'display: none;'; ?>">
                        <ul class="user-menu-list">
                            <li class="notification-bell">
                                <div class="icon-notification">
                                    <i class="fa fa-bell" aria-hidden="true"></i>
                                    <span class="notification-badge" id="notificationBadge"
                                        style="display: none;">0</span>
                                </div>
                                <div class="notification-dropdown" id="notificationDropdown">
                                    <div class="notification-header">
                                        <h4>Thông báo</h4>
                                    </div>
                                    <ul class="notification-list" id="notificationList">
                                        <li class="no-notification">Không có thông báo nào!</li>
                                    </ul>
                                </div>
                            </li>

                            <li class="user-profile">
                                <div class="user-avatar" id="userAvatar">
                                    <img src="<?php echo esc_url($avatar_url); ?>" alt="User Avatar" id="userAvatarImg">
                                </div>
                                <div class="user-dropdown" id="userDropdown">
                                    <div class="user-info">
                                        <img src="<?php echo esc_url($avatar_url); ?>" alt="User Avatar"
                                            id="userDropdownAvatar">
                                        <div class="user-details">
                                            <div class="user-name" id="userName"><?php echo esc_html($display_name); ?>
                                            </div>
                                            <div class="user-email" id="userEmail"><?php echo esc_html($user_email); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <ul class="user-menu-links">
                                        <li>
                                            <a href="<?php echo home_url('/truyen-dang-theo-doi'); ?>">
                                                <i class="fa fa-heart"></i> Danh sách theo dõi
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo home_url('/lich-su'); ?>">
                                                <i class="fa fa-history"></i> Lịch sử đọc truyện
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo home_url('/quan-ly-tai-khoan'); ?>">
                                                <i class="fa fa-cog"></i> Cài đặt thông tin
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo wp_logout_url(home_url('/dang-nhap')); ?>">
                                                <i class="fa fa-sign-out"></i> Đăng xuất
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="search-form desktop-only">
                    <input type="text" class="search-input" id="searchInput" placeholder="Bạn muốn tìm truyện gì">
                    <button class="search-submit">
                        <i class="fa fa-search"></i>
                    </button>
                    <div class="search-results" id="searchResults"></div>
                </div>
            </div>
        </div>

        <div class="search-mobile-expand" id="mobileSearchExpand">
            <div class="container">
                <input type="text" class="search-input" id="mobileSearchInput" placeholder="Bạn muốn tìm truyện gì">
                <button class="search-submit">
                    <i class="fa fa-search"></i>
                </button>
            </div>
            <div class="search-results-fullscreen" id="mobileSearchResults"></div>
        </div>

        <div class="header-bottom">
            <div class="container">
                <nav class="main-navigation">
                    <ul class="nav-menu" id="mainMenu">
                        <li>
                            <a href="<?php echo home_url('/'); ?>">Trang chủ</a>
                            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                                <i class="fa fa-bars"></i>
                            </button>
                        </li>
                        <li class="has-dropdown">
                            <a href="#" class="dropdown-toggle">Thể Loại <i class="fa fa-caret-down"></i></a>
                            <div class="mega-menu">
                                <div class="mega-menu-content" id="genresList">
                                    <div class="loading-genres"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>
                                </div>
                            </div>
                        </li>
                        <li class="has-dropdown">
                            <a href="#" class="dropdown-toggle">Xếp Hạng <i class="fa fa-caret-down"></i></a>
                            <div class="mega-menu">
                                <div class="mega-menu-content">
                                    <a href="<?php echo home_url('/top-ngay'); ?>">Top Ngày</a>
                                    <a href="<?php echo home_url('/top-tuan'); ?>">Top Tuần</a>
                                    <a href="<?php echo home_url('/top-thang'); ?>">Top Tháng</a>
                                    <a href="<?php echo home_url('/yeu-thich'); ?>">Yêu Thích</a>
                                    <a href="<?php echo home_url('/moi-cap-nhat'); ?>">Mới Cập Nhật</a>
                                    <a href="<?php echo home_url('/truyen-moi'); ?>">Truyện Mới</a>
                                    <a href="<?php echo home_url('/truyen-full'); ?>">Truyện Full</a>
                                    <a href="<?php echo home_url('/truyen-ngau-nhien'); ?>">Truyện Ngẫu Nhiên</a>
                                </div>
                            </div>
                        </li>
                        <li><a href="#">Con Gái</a></li>
                        <li><a href="#">Con Trai</a></li>
                        <li><a href="<?php echo esc_url(home_url('/tim-kiem-nang-cao')); ?>">Tìm Truyện</a></li>
                        <li><a href="<?php echo esc_url(home_url('/lich-su')); ?>">Lịch Sử</a></li>
                        <li><a href="<?php echo esc_url(home_url('/theo-doi')); ?>">Theo Dõi</a></li>
                        <li><a href="https://discord.gg/t8dQUwsrsj" target="_blank">Discord</a></li>
                        <li><a href="https://www.facebook.com/truyenqqq" target="_blank">Fanpage</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <?php wp_footer(); ?>
</body>

</html>