<?php
/**
 * Footer Template - TruyenQQ
 * Updated: dùng wp_nav_menu() cho 3 cột links
 *
 * @package TruyenQQ
 * @version 1.1.0
 */

/**
 * Helper: render một cột footer
 *
 * Nếu menu location đã được assign → dùng wp_nav_menu()
 * Nếu chưa assign → fallback về hardcode
 *
 * Heading lấy từ tên menu nếu có, fallback về $default_heading
 */
function truyenqq_footer_menu_column($location, $default_heading, $fallback_links)
{
    $has_menu = has_nav_menu($location);

    // Lấy heading từ tên menu (nếu admin đặt tên menu)
    $heading = $default_heading;
    if ($has_menu) {
        $locations = get_nav_menu_locations();
        if (!empty($locations[$location])) {
            $menu_obj = wp_get_nav_menu_object($locations[$location]);
            if ($menu_obj && !empty($menu_obj->name)) {
                $heading = $menu_obj->name;
            }
        }
    }
    ?>
<div class="footer-col">
    <h4 class="footer-heading"><?php echo esc_html($heading); ?></h4>

    <?php if ($has_menu): ?>
    <?php
            wp_nav_menu([
                'theme_location' => $location,
                'container' => false,
                'menu_class' => 'footer-links',
                'items_wrap' => '<ul class="%2$s">%3$s</ul>',
                'depth' => 1,         // chỉ render 1 cấp, không cần sub-menu
                'fallback_cb' => false,
                'link_before' => '',
                'link_after' => '',
            ]);
            ?>
    <?php else: ?>
    <!-- Fallback hardcode khi chưa assign menu -->
    <ul class="footer-links">
        <?php foreach ($fallback_links as $link): ?>
        <li>
            <a href="<?php echo esc_url($link['url']); ?>"
                <?php echo !empty($link['target']) ? 'target="' . esc_attr($link['target']) . '"' : ''; ?>>
                <?php echo esc_html($link['label']); ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>
<?php
}
?>

</div><!-- Close main content container if exists -->

<footer class="truyenqq-footer">

    <!-- ── NEWSLETTER ───────────────────────────────────────── -->
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

    <!-- ── FOOTER MAIN ──────────────────────────────────────── -->
    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">

                <!-- Cột About (hardcode) -->
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

                <!-- Cột Thể Loại (dynamic) -->
                <?php truyenqq_footer_menu_column(
                    'footer-genres',
                    'Thể Loại Nổi Bật',
                    [
                        ['url' => home_url('/the-loai/action'), 'label' => 'Action'],
                        ['url' => home_url('/the-loai/romance'), 'label' => 'Romance'],
                        ['url' => home_url('/the-loai/comedy'), 'label' => 'Comedy'],
                        ['url' => home_url('/the-loai/fantasy'), 'label' => 'Fantasy'],
                        ['url' => home_url('/the-loai/horror'), 'label' => 'Horror'],
                        ['url' => home_url('/the-loai/drama'), 'label' => 'Drama'],
                        ['url' => home_url('/the-loai'), 'label' => 'Xem tất cả'],
                    ]
                ); ?>

                <!-- Cột Xếp Hạng (dynamic) -->
                <?php truyenqq_footer_menu_column(
                    'footer-ranking',
                    'Xếp Hạng &amp; Danh Sách',
                    [
                        ['url' => home_url('/top-ngay'), 'label' => 'Top Ngày'],
                        ['url' => home_url('/top-tuan'), 'label' => 'Top Tuần'],
                        ['url' => home_url('/top-thang'), 'label' => 'Top Tháng'],
                        ['url' => home_url('/yeu-thich'), 'label' => 'Yêu Thích Nhất'],
                        ['url' => home_url('/truyen-moi'), 'label' => 'Truyện Mới'],
                        ['url' => home_url('/truyen-full'), 'label' => 'Truyện Full'],
                        ['url' => home_url('/truyen-ngau-nhien'), 'label' => 'Ngẫu Nhiên'],
                    ]
                ); ?>

                <!-- Cột Hỗ Trợ (dynamic) -->
                <?php truyenqq_footer_menu_column(
                    'footer-support',
                    'Hỗ Trợ &amp; Thông Tin',
                    [
                        ['url' => home_url('/gioi-thieu'), 'label' => 'Giới Thiệu'],
                        ['url' => home_url('/lien-he'), 'label' => 'Liên Hệ'],
                        ['url' => home_url('/dieu-khoan'), 'label' => 'Điều Khoản'],
                        ['url' => home_url('/chinh-sach'), 'label' => 'Chính Sách'],
                        ['url' => home_url('/dmca'), 'label' => 'DMCA'],
                        ['url' => home_url('/huong-dan'), 'label' => 'Hướng Dẫn'],
                        ['url' => home_url('/faq'), 'label' => 'FAQ'],
                    ]
                ); ?>

            </div><!-- /.footer-grid -->
        </div><!-- /.container -->
    </div><!-- /.footer-main -->

    <!-- ── FOOTER BOTTOM ────────────────────────────────────── -->
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-content">
                <div class="copyright">
                    <p>&copy; <?php echo date('Y'); ?> <strong>TruyenQQ</strong>. Đọc truyện tranh online miễn phí.
                    </p>
                </div>

                <div class="social-links">
                    <a href="https://www.facebook.com/truyenqqq" target="_blank" rel="noopener" class="social-link"
                        title="Facebook">
                        <i class="fa fa-facebook"></i>
                    </a>
                    <a href="https://discord.gg/t8dQUwsrsj" target="_blank" rel="noopener"
                        class="social-link discord-link" title="Discord">
                        <i class="fa fa-comments"></i>
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