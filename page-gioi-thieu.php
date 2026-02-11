<?php
/**
 * Template Name: Giới Thiệu
 * Description: Trang giới thiệu về TruyenQQ
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

get_header();
?>

<div class="info-page-wrapper gioi-thieu-page">
    <!-- Hero Section -->
    <section class="info-hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Về TruyenQQ</h1>
                <p class="hero-subtitle">Nền tảng đọc truyện tranh online hàng đầu Việt Nam</p>
                <div class="hero-breadcrumb">
                    <a href="<?php echo home_url('/'); ?>">Trang chủ</a>
                    <span class="separator">/</span>
                    <span>Giới thiệu</span>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-grid">
                <div class="about-text">
                    <h2 class="section-title">
                        <i class="fa fa-book"></i>
                        Chúng Tôi Là Ai?
                    </h2>
                    <p>
                        <strong>TruyenQQ</strong> là nền tảng đọc truyện tranh online miễn phí hàng đầu tại Việt Nam,
                        được thành lập với mục tiêu mang đến cho độc giả những trải nghiệm đọc truyện tuyệt vời nhất.
                    </p>
                    <p>
                        Với kho truyện khổng lồ hơn <strong>5,000+</strong> đầu truyện thuộc nhiều thể loại khác nhau
                        từ Manga, Manhwa đến Manhua, chúng tôi tự hào phục vụ hơn <strong>50,000+</strong> thành viên
                        với <strong>10 triệu+</strong> lượt đọc mỗi tháng.
                    </p>
                    <p>
                        Đội ngũ của chúng tôi luôn nỗ lực cập nhật liên tục các chương mới nhất, đảm bảo chất lượng
                        hình ảnh sắc nét và tốc độ tải nhanh chóng để mang lại trải nghiệm đọc truyện tốt nhất.
                    </p>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1618519764620-7403abdbdfe9?w=600&h=400&fit=crop"
                        alt="Về TruyenQQ" loading="lazy">
                    <div class="image-overlay"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa fa-book"></i>
                    </div>
                    <div class="stat-number" data-target="5000">0</div>
                    <div class="stat-label">Đầu Truyện</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <div class="stat-number" data-target="50000">0</div>
                    <div class="stat-label">Thành Viên</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa fa-eye"></i>
                    </div>
                    <div class="stat-number" data-target="10000000">0</div>
                    <div class="stat-label">Lượt Đọc/Tháng</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <div class="stat-number" data-target="24">0</div>
                    <div class="stat-label">Cập Nhật 24/7</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="mission-section">
        <div class="container">
            <h2 class="section-title centered">
                <i class="fa fa-target"></i>
                Sứ Mệnh & Tầm Nhìn
            </h2>

            <div class="mission-grid">
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fa fa-heart"></i>
                    </div>
                    <h3>Sứ Mệnh</h3>
                    <p>
                        Mang đến cho người đọc Việt Nam một nền tảng đọc truyện tranh chất lượng cao,
                        miễn phí và dễ tiếp cận. Chúng tôi cam kết luôn cập nhật nhanh nhất,
                        đầy đủ nhất các bộ truyện hot.
                    </p>
                </div>

                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fa fa-eye"></i>
                    </div>
                    <h3>Tầm Nhìn</h3>
                    <p>
                        Trở thành cộng đồng đọc truyện tranh online lớn nhất Việt Nam,
                        nơi kết nối hàng triệu độc giả yêu thích truyện tranh và
                        tạo ra không gian văn hóa giải trí lành mạnh.
                    </p>
                </div>

                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fa fa-star"></i>
                    </div>
                    <h3>Giá Trị Cốt Lõi</h3>
                    <p>
                        Chất lượng - Tốc độ - Miễn phí. Ba giá trị này là kim chỉ nam
                        trong mọi hoạt động của TruyenQQ, từ việc chọn lựa truyện,
                        cập nhật chapter đến phát triển tính năng.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title centered">
                <i class="fa fa-trophy"></i>
                Tại Sao Chọn TruyenQQ?
            </h2>

            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fa fa-flash"></i>
                    </div>
                    <h3>Cập Nhật Nhanh</h3>
                    <p>Cập nhật chapter mới liên tục 24/7, không để bạn phải chờ đợi</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fa fa-image"></i>
                    </div>
                    <h3>Chất Lượng Cao</h3>
                    <p>Hình ảnh sắc nét, rõ ràng, tối ưu cho mọi thiết bị</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fa fa-mobile"></i>
                    </div>
                    <h3>Responsive</h3>
                    <p>Đọc mượt mà trên mọi thiết bị: PC, Tablet, Mobile</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fa fa-search"></i>
                    </div>
                    <h3>Tìm Kiếm Thông Minh</h3>
                    <p>Tìm truyện nhanh chóng với hệ thống tìm kiếm thông minh</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fa fa-bookmark"></i>
                    </div>
                    <h3>Đánh Dấu Truyện</h3>
                    <p>Theo dõi và quản lý danh sách truyện yêu thích dễ dàng</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fa fa-comments"></i>
                    </div>
                    <h3>Cộng Đồng</h3>
                    <p>Tham gia thảo luận, bình luận cùng hàng ngàn fan truyện</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Section -->
    <section class="timeline-section">
        <div class="container">
            <h2 class="section-title centered">
                <i class="fa fa-history"></i>
                Lịch Sử Phát Triển
            </h2>

            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-year">2020</div>
                        <h3>Ra Mắt</h3>
                        <p>TruyenQQ chính thức ra mắt với 500+ đầu truyện và 5,000 thành viên đầu tiên</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-year">2021</div>
                        <h3>Mở Rộng</h3>
                        <p>Đạt 2,000+ truyện, 20,000+ thành viên. Ra mắt ứng dụng mobile</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-year">2022</div>
                        <h3>Phát Triển Mạnh</h3>
                        <p>Cộng đồng đạt 50,000+ thành viên, 5M+ lượt đọc/tháng</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-year">2024</div>
                        <h3>Hiện Tại</h3>
                        <p>5,000+ truyện, 100,000+ thành viên, 10M+ lượt đọc/tháng</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Bắt Đầu Hành Trình Đọc Truyện Của Bạn</h2>
                <p>Tham gia cộng đồng 100,000+ độc giả đam mê truyện tranh</p>
                <div class="cta-buttons">
                    <a href="<?php echo home_url('/dang-ky'); ?>" class="btn btn-primary">
                        <i class="fa fa-user-plus"></i>
                        Đăng Ký Ngay
                    </a>
                    <a href="<?php echo home_url('/tim-kiem-nang-cao'); ?>" class="btn btn-secondary">
                        <i class="fa fa-search"></i>
                        Khám Phá Truyện
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>