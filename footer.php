<?php
/**
 * Footer Template
 * 
 * @package PixelPerfect
 */
?>

<!-- FOOTER SECTION -->
<footer class="footer-section">
    <div class="blur-orb blur-orb-top"></div>
    <div class="blur-orb blur-orb-bottom"></div>

    <div class="footer-container">
        <div class="newsletter-section">
            <div class="newsletter-grid">
                <div class="newsletter-content">
                    <h3 class="newsletter-title">
                        <span class="title-white">NHẬN TIN TỨC </span>
                        <span class="title-gradient">MỚI NHẤT</span>
                    </h3>
                    <p class="newsletter-description">
                        Đăng ký để nhận những xu hướng thiết kế web mới nhất và insights từ Pixel Perfect.
                    </p>
                </div>

                <div class="newsletter-form-wrapper">
                    <form class="newsletter-form" id="newsletterForm">
                        <input type="email" name="email" placeholder="Email của bạn" class="newsletter-input" required>
                        <button type="submit" class="newsletter-btn">
                            <span>Đăng Ký</span>
                            <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Footer Content -->
        <div class="footer-main">
            <div class="footer-grid">
                <div class="footer-col footer-col-brand">
                    <a href="<?php echo home_url('/'); ?>" class="footer-logo">
                        <div class="logo-icon">
                            <span>PP</span>
                        </div>
                        <span class="logo-text">Pixel Perfect</span>
                    </a>

                    <p class="brand-description">
                        Đơn vị thiết kế website doanh nghiệp chuyên nghiệp hàng đầu Việt Nam với hơn 10 năm kinh nghiệm.
                    </p>

                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                    </path>
                                </svg>
                            </div>
                            <span>1900 xxxx</span>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                </svg>
                            </div>
                            <span>contact@pixelperfect.vn</span>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0">
                                    </path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <span>123 Nguyễn Huệ, Q.1, TP.HCM</span>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Dịch Vụ -->
                <div class="footer-col">
                    <h3 class="footer-heading">Dịch Vụ</h3>
                    <ul class="footer-links">
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>Website Giới Thiệu</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>Website Thương Mại</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>Landing Page</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>SEO Website</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>Bảo Trì Website</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Column 3: Tài Nguyên -->
                <div class="footer-col">
                    <h3 class="footer-heading">Tài Nguyên</h3>
                    <ul class="footer-links">
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>Blog Kiến Thức</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>Case Study</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>Hướng Dẫn</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>FAQ</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>Bảng Giá</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Column 4: Công Ty -->
                <div class="footer-col">
                    <h3 class="footer-heading">Công Ty</h3>
                    <ul class="footer-links">
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>Về Chúng Tôi</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>Đội Ngũ</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>Tuyển Dụng</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>Liên Hệ</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <svg class="link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                                <span>Chính Sách</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Column 5: Social Links -->
                <div class="footer-col">
                    <h3 class="footer-heading">Theo Dõi</h3>
                    <div class="social-links">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                            </svg>
                        </a>
                        <a href="#" class="social-link" aria-label="Youtube">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17">
                                </path>
                                <path d="m10 15 5-3-5-3z"></path>
                            </svg>
                        </a>
                        <a href="#" class="social-link" aria-label="LinkedIn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z">
                                </path>
                                <rect width="4" height="12" x="2" y="9"></rect>
                                <circle cx="4" cy="4" r="2"></circle>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Copyright Section -->
        <div class="footer-bottom">
            <div class="copyright-wrapper">
                <p class="copyright-text">
                    © <?php echo date('Y'); ?> Pixel Perfect Agency. All rights reserved.
                </p>
                <div class="footer-legal">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Cookie Policy</a>
                </div>
            </div>
        </div>

    </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>