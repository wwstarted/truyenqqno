<?php
/**
 * Header Template - TruyenQQ
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Font Awesome 4.7 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <header class="site-header">
        <!-- Top Header -->
        <div class="header-top">
            <div class="container">
                <!-- Logo -->
                <div class="header-logo">
                    <a href="<?php echo home_url('/'); ?>" title="Truyện tranh online">
                        <img src="https://st.truyenqqno.com/template/frontend/images/logo.png" alt="TruyenQQ"
                            class="logo-desktop">
                        <img src="https://st.truyenqqno.com/template/frontend/images/logo-icon.png" alt="TruyenQQ"
                            class="logo-mobile">
                    </a>
                </div>

                <!-- Dark Mode Toggle (Right of Logo) -->
                <button class="dark-mode-toggle" id="darkModeToggle" title="Chế độ tối/sáng">
                    <i class="fa fa-lightbulb-o"></i>
                </button>

                <!-- Right Actions -->
                <div class="header-actions">
                    <!-- Search Icon (Mobile/Tablet) -->
                    <button class="search-icon-btn mobile-tablet-only" id="mobileSearchToggle">
                        <i class="fa fa-search"></i>
                    </button>

                    <!-- Auth Buttons -->
                    <div class="auth-buttons">
                        <button class="btn-register">Đăng ký</button>
                        <button class="btn-login">Đăng nhập</button>
                    </div>
                </div>

                <!-- Search Form (Desktop) -->
                <div class="search-form desktop-only">
                    <input type="text" class="search-input" id="searchInput" placeholder="Bạn muốn tìm truyện gì">
                    <button class="search-submit">
                        <i class="fa fa-search"></i>
                    </button>

                    <!-- Search Dropdown Results -->
                    <div class="search-results" id="searchResults">
                        <!-- Results will be populated by JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile/Tablet Search Expandable -->
        <div class="search-mobile-expand" id="mobileSearchExpand">
            <div class="container">
                <input type="text" class="search-input" id="mobileSearchInput" placeholder="Bạn muốn tìm truyện gì">
                <button class="search-submit">
                    <i class="fa fa-search"></i>
                </button>
            </div>

            <!-- Search Results Fullscreen (Mobile/Tablet) -->
            <div class="search-results-fullscreen" id="mobileSearchResults">
                <!-- Results will be populated by JS -->
            </div>
        </div>

        <!-- Bottom Navigation -->
        <div class="header-bottom">
            <div class="container">
                <nav class="main-navigation">
                    <ul class="nav-menu" id="mainMenu">
                        <li>
                            <a href="<?php echo home_url('/'); ?>">Trang chủ</a>
                            <!-- Mobile Menu Toggle - Inside first li -->
                            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                                <i class="fa fa-bars"></i>
                            </button>
                        </li>
                        <li class="has-dropdown">
                            <a href="#" class="dropdown-toggle">
                                Thể Loại <i class="fa fa-caret-down"></i>
                            </a>
                            <div class="mega-menu">
                                <div class="mega-menu-content" id="genresList">
                                    <!-- Genres will be loaded dynamically -->
                                    <div class="loading-genres">
                                        <i class="fa fa-spinner fa-spin"></i> Đang tải...
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="has-dropdown">
                            <a href="#" class="dropdown-toggle">
                                Xếp Hạng <i class="fa fa-caret-down"></i>
                            </a>
                            <div class="mega-menu">
                                <div class="mega-menu-content">
                                    <a href="<?php echo home_url('/top-ngay'); ?>">Top Ngày</a>
                                    <a href="<?php echo home_url('/top-tuan'); ?>">Top Tuần</a>
                                    <a href="<?php echo home_url('/top-thang'); ?>">Top Tháng</a>
                                    <a href="<?php echo home_url('/yeu-thich'); ?>">Yêu Thích</a>
                                    <a href="<?php echo home_url('/moi-cap-nhat'); ?>">Mới Cập Nhật</a>
                                    <a href="<?php echo home_url('/truyen-moi'); ?>">Truyện Mới</a>
                                    <a href="<?php echo home_url('/truyen-full'); ?>">Truyện Full</a>
                                    <a href="<?php echo home_url('/ngau-nhien'); ?>">Truyện Ngẫu Nhiên</a>
                                </div>
                            </div>
                        </li>
                        <li><a href="#">Con Gái</a></li>
                        <li><a href="#">Con Trai</a></li>
                        <li><a href="#">Tìm Truyện</a></li>
                        <li><a href="#">Lịch Sử</a></li>
                        <li><a href="#">Theo Dõi</a></li>
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