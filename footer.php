<?php
/**
 * Footer Template - TruyenQQ
 * FIXED: Proper placement outside containers
 * 
 * @package TruyenQQ
 * @version 1.0.3
 */

// Close any open containers from main content
// IMPORTANT: Phải đóng tất cả containers trước khi mở footer
?>

</div><!-- Close main content container if exists -->

<footer class="truyenqq-footer">
    <div class="footer-top">
        <div class="container">
            <div class="newsletter-section">
                <div class="newsletter-content">
                    <div class="newsletter-icon">
                        <i class="fa fa-envelope-o"></i>
                    </div>
                    <div class="newsletter-text">
                        <h3>Đăng ký nhận thông báo</h3>
                        <p>Nhận thông báo khi có truyện mới và chapter mới cập nhật</p>
                    </div>
                </div>
                <form class="newsletter-form" id="footerNewsletterForm">
                    <input type="email" name="email" placeholder="Nhập email của bạn" class="newsletter-input" required>
                    <button type="submit" class="newsletter-btn">
                        <span>Đăng ký</span>
                        <i class="fa fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col footer-about">
                    <div class="footer-logo">
                        <a href="<?php echo esc_url(home_url('/')); ?>">
                            <img src="https://st.truyenqqno.com/template/frontend/images/logo.png" alt="TruyenQQ">
                        </a>
                    </div>
                    <p class="footer-description">
                        Đọc truyện tranh online miễn phí, cập nhật liên tục với kho truyện khổng lồ.
                        Manga, Manhwa, Manhua chất lượng cao, đa dạng thể loại.
                    </p>

                    <div class="footer-stats">
                        <div class="stat-item">
                            <div class="stat-number">5,000+</div>
                            <div class="stat-label">Truyện</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">10M+</div>
                            <div class="stat-label">Lượt đọc</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">50,000+</div>
                            <div class="stat-label">Thành viên</div>
                        </div>
                    </div>

                    <div class="footer-contact">
                        <div class="contact-item">
                            <i class="fa fa-envelope"></i>
                            <a href="mailto:marcander.tvd11@gmail.com">marcander.tvd11@gmail.com</a>
                        </div>
                    </div>
                </div>

                <div class="footer-col">
                    <h4 class="footer-heading">Thể Loại Nổi Bật</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo esc_url(home_url('/the-loai/action')); ?>">Action</a></li>
                        <li><a href="<?php echo esc_url(home_url('/the-loai/romance')); ?>">Romance</a></li>
                        <li><a href="<?php echo esc_url(home_url('/the-loai/comedy')); ?>">Comedy</a></li>
                        <li><a href="<?php echo esc_url(home_url('/the-loai/fantasy')); ?>">Fantasy</a></li>
                        <li><a href="<?php echo esc_url(home_url('/the-loai/horror')); ?>">Horror</a></li>
                        <li><a href="<?php echo esc_url(home_url('/the-loai/drama')); ?>">Drama</a></li>
                        <li><a href="<?php echo esc_url(home_url('/the-loai')); ?>">Xem tất cả</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4 class="footer-heading">Xếp Hạng &amp; Danh Sách</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo esc_url(home_url('/top-ngay')); ?>">Top Ngày</a></li>
                        <li><a href="<?php echo esc_url(home_url('/top-tuan')); ?>">Top Tuần</a></li>
                        <li><a href="<?php echo esc_url(home_url('/top-thang')); ?>">Top Tháng</a></li>
                        <li><a href="<?php echo esc_url(home_url('/yeu-thich')); ?>">Yêu Thích Nhất</a></li>
                        <li><a href="<?php echo esc_url(home_url('/truyen-moi')); ?>">Truyện Mới</a></li>
                        <li><a href="<?php echo esc_url(home_url('/truyen-full')); ?>">Truyện Full</a></li>
                        <li><a href="<?php echo esc_url(home_url('/truyen-ngau-nhien')); ?>">Ngẫu Nhiên</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4 class="footer-heading">Hỗ Trợ &amp; Thông Tin</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo esc_url(home_url('/gioi-thieu')); ?>">Giới Thiệu</a></li>
                        <li><a href="<?php echo esc_url(home_url('/lien-he')); ?>">Liên Hệ</a></li>
                        <li><a href="<?php echo esc_url(home_url('/dieu-khoan')); ?>">Điều Khoản</a></li>
                        <li><a href="<?php echo esc_url(home_url('/chinh-sach')); ?>">Chính Sách</a></li>
                        <li><a href="<?php echo esc_url(home_url('/dmca')); ?>">DMCA</a></li>
                        <li><a href="<?php echo esc_url(home_url('/huong-dan')); ?>">Hướng Dẫn</a></li>
                        <li><a href="<?php echo esc_url(home_url('/faq')); ?>">FAQ</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-content">
                <div class="copyright">
                    <p>&copy; <?php echo date('Y'); ?> <strong>TruyenQQ</strong>. Đọc truyện tranh online miễn phí.</p>
                </div>

                <div class="social-links">
                    <a href="https://www.facebook.com/truyenqqq" target="_blank" rel="noopener" class="social-link"
                        title="Facebook">
                        <i class="fa fa-facebook"></i>
                    </a>
                    <a href="https://discord.gg/t8dQUwsrsj" target="_blank" rel="noopener"
                        class="social-link discord-link" title="Discord">
                        <i class="fa fa-comments"></i><!-- Icon will be replaced by CSS -->
                    </a>
                    <a href="#" target="_blank" rel="noopener" class="social-link" title="Youtube">
                        <i class="fa fa-youtube-play"></i>
                    </a>
                    <a href="#" target="_blank" rel="noopener" class="social-link" title="Telegram">
                        <i class="fa fa-telegram"></i>
                    </a>
                </div>

                <div class="back-to-top" id="backToTop">
                    <button type="button" title="Về đầu trang">
                        <i class="fa fa-arrow-up"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>