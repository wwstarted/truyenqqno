<?php
/**
 * Template Name: Liên Hệ
 * Description: Trang liên hệ TruyenQQ
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

get_header();
?>

<div class="info-page-wrapper lien-he-page">
    <!-- Hero Section -->
    <section class="info-hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Liên Hệ Với Chúng Tôi</h1>
                <p class="hero-subtitle">Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn</p>
                <div class="hero-breadcrumb">
                    <a href="<?php echo home_url('/'); ?>">Trang chủ</a>
                    <span class="separator">/</span>
                    <span>Liên hệ</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Info Section -->
    <section class="contact-info-section">
        <div class="container">
            <div class="contact-info-grid">
                <div class="contact-card">
                    <div class="contact-card-icon">
                        <i class="fa fa-envelope"></i>
                    </div>
                    <h3>Email</h3>
                    <p>Gửi email cho chúng tôi</p>
                    <a href="mailto:marcander.tvd11@gmail.com">marcander.tvd11@gmail.com</a>
                </div>

                <div class="contact-card">
                    <div class="contact-card-icon">
                        <i class="fa fa-facebook"></i>
                    </div>
                    <h3>Facebook</h3>
                    <p>Theo dõi Fanpage của chúng tôi</p>
                    <a href="https://www.facebook.com/truyenqqq" target="_blank">facebook.com/truyenqqq</a>
                </div>

                <div class="contact-card">
                    <div class="contact-card-icon">
                        <i class="fa fa-comments"></i>
                    </div>
                    <h3>Discord</h3>
                    <p>Tham gia cộng đồng Discord</p>
                    <a href="https://discord.gg/t8dQUwsrsj" target="_blank">discord.gg/t8dQUwsrsj</a>
                </div>

                <div class="contact-card">
                    <div class="contact-card-icon">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <h3>Thời Gian</h3>
                    <p>Hỗ trợ online</p>
                    <strong>24/7</strong>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="contact-form-section">
        <div class="container">
            <div class="form-wrapper">
                <div class="form-header">
                    <h2>
                        <i class="fa fa-paper-plane"></i>
                        Gửi Tin Nhắn Cho Chúng Tôi
                    </h2>
                    <p>Điền thông tin vào form bên dưới và chúng tôi sẽ phản hồi sớm nhất</p>
                </div>

                <form id="contactForm" class="contact-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">
                                Họ và Tên <span class="required">*</span>
                            </label>
                            <input type="text" id="name" name="name" required placeholder="Nhập họ và tên của bạn">
                        </div>

                        <div class="form-group">
                            <label for="email">
                                Email <span class="required">*</span>
                            </label>
                            <input type="email" id="email" name="email" required placeholder="Nhập email của bạn">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subject">
                            Tiêu Đề <span class="required">*</span>
                        </label>
                        <input type="text" id="subject" name="subject" required placeholder="Tiêu đề tin nhắn">
                    </div>

                    <div class="form-group">
                        <label for="category">
                            Danh Mục
                        </label>
                        <select id="category" name="category">
                            <option value="">Chọn danh mục</option>
                            <option value="general">Thắc mắc chung</option>
                            <option value="technical">Vấn đề kỹ thuật</option>
                            <option value="content">Nội dung truyện</option>
                            <option value="account">Tài khoản</option>
                            <option value="suggestion">Góp ý / Đề xuất</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">
                            Nội Dung <span class="required">*</span>
                        </label>
                        <textarea id="message" name="message" rows="6" required
                            placeholder="Nhập nội dung tin nhắn của bạn..."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane"></i>
                            <span>Gửi Tin Nhắn</span>
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fa fa-refresh"></i>
                            <span>Làm Mới</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2 class="section-title centered">
                <i class="fa fa-question-circle"></i>
                Câu Hỏi Thường Gặp
            </h2>

            <div class="faq-grid">
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fa fa-chevron-right"></i>
                        <h3>Tôi có thể đọc truyện miễn phí không?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Có! Tất cả truyện trên TruyenQQ đều hoàn toàn miễn phí. Bạn không cần trả bất kỳ chi phí nào.
                        </p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fa fa-chevron-right"></i>
                        <h3>Làm sao để theo dõi truyện yêu thích?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Bạn cần đăng ký tài khoản, sau đó click vào icon bookmark (dấu trang) ở mỗi truyện để theo
                            dõi.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fa fa-chevron-right"></i>
                        <h3>Truyện được cập nhật bao lâu một lần?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Chúng tôi cập nhật liên tục 24/7. Tần suất cập nhật tùy thuộc vào tốc độ phát hành của từng
                            bộ truyện.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fa fa-chevron-right"></i>
                        <h3>Tôi gặp lỗi khi đọc truyện, phải làm sao?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Vui lòng liên hệ với chúng tôi qua form trên hoặc email marcander.tvd11@gmail.com để được hỗ
                            trợ nhanh nhất.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fa fa-chevron-right"></i>
                        <h3>Tôi có thể đề xuất truyện mới không?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Có! Hãy gửi đề xuất của bạn qua form liên hệ hoặc tham gia Discord để thảo luận với cộng
                            đồng.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fa fa-chevron-right"></i>
                        <h3>TruyenQQ có ứng dụng mobile không?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Website của chúng tôi được tối ưu hoàn hảo cho mobile. Bạn có thể thêm vào màn hình chính để
                            trải nghiệm như app.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Section -->
    <section class="social-section">
        <div class="container">
            <h2 class="section-title centered">
                <i class="fa fa-share-alt"></i>
                Kết Nối Với Chúng Tôi
            </h2>

            <div class="social-grid">
                <a href="https://www.facebook.com/truyenqqq" target="_blank" class="social-box facebook">
                    <i class="fa fa-facebook"></i>
                    <h3>Facebook</h3>
                    <p>Theo dõi fanpage</p>
                </a>

                <a href="https://discord.gg/t8dQUwsrsj" target="_blank" class="social-box discord">
                    <i class="fa fa-comments"></i>
                    <h3>Discord</h3>
                    <p>Tham gia cộng đồng</p>
                </a>

                <a href="#" target="_blank" class="social-box youtube">
                    <i class="fa fa-youtube-play"></i>
                    <h3>Youtube</h3>
                    <p>Đăng ký kênh</p>
                </a>

                <a href="#" target="_blank" class="social-box telegram">
                    <i class="fa fa-telegram"></i>
                    <h3>Telegram</h3>
                    <p>Nhận thông báo</p>
                </a>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>