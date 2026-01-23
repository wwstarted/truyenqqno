<?php
/**
 * 404 Page Template - TruyenQQ
 * Trang lỗi 404 với Blue Theme
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

get_header('auth');
?>

<div class="error-404-page">
    <!-- Video Background -->
    <video class="error-404-video-bg" autoplay muted loop playsinline>
        <source src="<?php echo get_template_directory_uri(); ?>/images/402v2.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <!-- Video Overlay -->
    <div class="error-404-video-overlay"></div>

    <div class="error-404-container">
        <!-- Animated 404 Number -->
        <div class="error-404-number">
            <span class="number-4 animate-float">4</span>
            <span class="number-0 animate-bounce">
                <i class="fa fa-book"></i>
            </span>
            <span class="number-4-second animate-float-delay">4</span>
        </div>

        <!-- Title & Description -->
        <div class="error-404-content">
            <h1 class="error-title">Oops! Trang không tồn tại</h1>
            <p class="error-description">
                Có vẻ như trang bạn đang tìm kiếm đã bị mất hoặc không tồn tại.
                Đừng lo, hãy thử tìm kiếm truyện yêu thích của bạn nhé!
            </p>
        </div>

        <!-- Search Bar -->
        <div class="error-404-search">
            <div class="search-wrapper">
                <i class="fa fa-search search-icon"></i>
                <input type="text" class="search-input-404" id="search404Input" placeholder="Tìm kiếm truyện tranh..."
                    autocomplete="off">
                <button class="search-btn-404" id="search404Btn">
                    <span>Tìm kiếm</span>
                    <i class="fa fa-arrow-right"></i>
                </button>
            </div>
            <div class="search-results-404" id="searchResults404"></div>
        </div>

        <!-- Action Buttons -->
        <div class="error-404-actions">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-404 btn-home">
                <i class="fa fa-home"></i>
                <span>Về Trang Chủ</span>
            </a>

            <a href="<?php echo esc_url(home_url('/the-loai')); ?>" class="btn-404 btn-genres">
                <i class="fa fa-th-large"></i>
                <span>Thể Loại Truyện</span>
            </a>

            <a href="<?php echo esc_url(home_url('/truyen-ngau-nhien')); ?>" class="btn-404 btn-random">
                <i class="fa fa-random"></i>
                <span>Truyện Ngẫu Nhiên</span>
            </a>
        </div>

        <!-- Decorative Elements -->
        <div class="error-404-decorations">
            <div class="floating-book floating-book-1">
                <i class="fa fa-book"></i>
            </div>
            <div class="floating-book floating-book-2">
                <i class="fa fa-bookmark"></i>
            </div>
            <div class="floating-book floating-book-3">
                <i class="fa fa-heart"></i>
            </div>
            <div class="floating-circle circle-1"></div>
            <div class="floating-circle circle-2"></div>
            <div class="floating-circle circle-3"></div>
        </div>
    </div>
</div>

<?php
get_footer('auth');
?>