<?php
/**
 * Template Name: Chính Sách Bảo Mật
 * Description: Trang chính sách bảo mật TruyenQQ
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

get_header();
?>

<div class="info-page-wrapper chinh-sach-page">
    <!-- Hero Section -->
    <section class="info-hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Chính Sách Bảo Mật</h1>
                <p class="hero-subtitle">Cam kết bảo vệ thông tin cá nhân của bạn</p>
                <div class="hero-breadcrumb">
                    <a href="<?php echo home_url('/'); ?>">Trang chủ</a>
                    <span class="separator">/</span>
                    <span>Chính sách</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="terms-content-section">
        <div class="container">
            <div class="terms-layout">
                <!-- Sidebar TOC -->
                <aside class="terms-sidebar">
                    <div class="toc-wrapper sticky-toc">
                        <h3>Mục Lục</h3>
                        <ul class="toc-list">
                            <li><a href="#section-1" class="toc-link active">1. Giới Thiệu</a></li>
                            <li><a href="#section-2" class="toc-link">2. Thu Thập Thông Tin</a></li>
                            <li><a href="#section-3" class="toc-link">3. Sử Dụng Thông Tin</a></li>
                            <li><a href="#section-4" class="toc-link">4. Chia Sẻ Thông Tin</a></li>
                            <li><a href="#section-5" class="toc-link">5. Cookies & Tracking</a></li>
                            <li><a href="#section-6" class="toc-link">6. Bảo Mật Dữ Liệu</a></li>
                            <li><a href="#section-7" class="toc-link">7. Quyền Của Bạn</a></li>
                            <li><a href="#section-8" class="toc-link">8. Trẻ Em</a></li>
                            <li><a href="#section-9" class="toc-link">9. Thay Đổi Chính Sách</a></li>
                            <li><a href="#section-10" class="toc-link">10. Liên Hệ</a></li>
                        </ul>
                    </div>
                </aside>

                <!-- Main Content -->
                <div class="terms-main-content">
                    <div class="terms-intro">
                        <div class="update-info">
                            <i class="fa fa-calendar"></i>
                            Cập nhật lần cuối: <strong>
                                <?php echo date('d/m/Y'); ?>
                            </strong>
                        </div>
                        <p class="lead-text">
                            TruyenQQ cam kết bảo vệ quyền riêng tư và thông tin cá nhân của bạn.
                            Chính sách bảo mật này giải thích cách chúng tôi thu thập, sử dụng,
                            lưu trữ và bảo vệ thông tin của bạn khi sử dụng dịch vụ.
                        </p>
                    </div>

                    <!-- Section 1 -->
                    <div id="section-1" class="terms-section">
                        <h2>
                            <span class="section-number">1.</span>
                            Giới Thiệu
                        </h2>
                        <div class="section-content">
                            <p>
                                Chính sách bảo mật này áp dụng cho tất cả người dùng truy cập và sử dụng
                                website TruyenQQ. Bằng việc sử dụng dịch vụ, bạn đồng ý với các điều khoản
                                được nêu trong chính sách này.
                            </p>
                            <div class="highlight-box">
                                <h3>
                                    <i class="fa fa-shield"></i>
                                    Cam Kết Của Chúng Tôi
                                </h3>
                                <ul>
                                    <li>Bảo vệ thông tin cá nhân của bạn</li>
                                    <li>Minh bạch về cách thu thập và sử dụng dữ liệu</li>
                                    <li>Không bán thông tin cá nhân cho bên thứ ba</li>
                                    <li>Tuân thủ các quy định pháp luật về bảo vệ dữ liệu</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2 -->
                    <div id="section-2" class="terms-section">
                        <h2>
                            <span class="section-number">2.</span>
                            Thu Thập Thông Tin
                        </h2>
                        <div class="section-content">
                            <h3>2.1. Thông Tin Bạn Cung Cấp</h3>
                            <p>Khi đăng ký tài khoản hoặc sử dụng dịch vụ, chúng tôi có thể thu thập:</p>
                            <ul>
                                <li><strong>Thông tin cá nhân:</strong> Tên, email, ngày sinh, giới tính</li>
                                <li><strong>Thông tin tài khoản:</strong> Username, mật khẩu (đã mã hóa)</li>
                                <li><strong>Thông tin liên hệ:</strong> Khi bạn gửi form liên hệ hoặc phản hồi</li>
                                <li><strong>Nội dung tương tác:</strong> Bình luận, đánh giá, góp ý</li>
                            </ul>

                            <h3>2.2. Thông Tin Tự Động Thu Thập</h3>
                            <p>Khi bạn sử dụng website, chúng tôi tự động thu thập:</p>
                            <ul>
                                <li><strong>Thông tin thiết bị:</strong> IP address, loại trình duyệt, hệ điều hành</li>
                                <li><strong>Dữ liệu sử dụng:</strong> Trang xem, thời gian truy cập, lịch sử đọc</li>
                                <li><strong>Cookies:</strong> Để ghi nhớ tùy chọn và cải thiện trải nghiệm</li>
                                <li><strong>Analytics:</strong> Dữ liệu thống kê qua Google Analytics</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Section 3 -->
                    <div id="section-3" class="terms-section">
                        <h2>
                            <span class="section-number">3.</span>
                            Sử Dụng Thông Tin
                        </h2>
                        <div class="section-content">
                            <p>Chúng tôi sử dụng thông tin thu thập được cho các mục đích sau:</p>

                            <div class="usage-grid">
                                <div class="usage-item">
                                    <i class="fa fa-user-circle"></i>
                                    <h4>Quản Lý Tài Khoản</h4>
                                    <p>Tạo, duy trì và quản lý tài khoản người dùng</p>
                                </div>

                                <div class="usage-item">
                                    <i class="fa fa-cog"></i>
                                    <h4>Cải Thiện Dịch Vụ</h4>
                                    <p>Phân tích hành vi để cải thiện chất lượng dịch vụ</p>
                                </div>

                                <div class="usage-item">
                                    <i class="fa fa-bell"></i>
                                    <h4>Gửi Thông Báo</h4>
                                    <p>Thông báo chapter mới, cập nhật hệ thống</p>
                                </div>

                                <div class="usage-item">
                                    <i class="fa fa-shield"></i>
                                    <h4>Bảo Mật</h4>
                                    <p>Phát hiện và ngăn chặn gian lận, lạm dụng</p>
                                </div>

                                <div class="usage-item">
                                    <i class="fa fa-envelope"></i>
                                    <h4>Liên Lạc</h4>
                                    <p>Phản hồi yêu cầu hỗ trợ và câu hỏi</p>
                                </div>

                                <div class="usage-item">
                                    <i class="fa fa-bar-chart"></i>
                                    <h4>Phân Tích</h4>
                                    <p>Thống kê sử dụng và xu hướng đọc</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4 -->
                    <div id="section-4" class="terms-section">
                        <h2>
                            <span class="section-number">4.</span>
                            Chia Sẻ Thông Tin
                        </h2>
                        <div class="section-content">
                            <p>
                                TruyenQQ <strong>KHÔNG</strong> bán, cho thuê hoặc trao đổi thông tin cá nhân
                                của bạn với bên thứ ba vì mục đích thương mại.
                            </p>

                            <h3>Chúng Tôi Chỉ Chia Sẻ Khi:</h3>
                            <ul>
                                <li>
                                    <strong>Nhà cung cấp dịch vụ:</strong> Google Analytics, CDN, hosting
                                    (các bên này có nghĩa vụ bảo mật)
                                </li>
                                <li>
                                    <strong>Yêu cầu pháp lý:</strong> Khi có lệnh của cơ quan chức năng
                                </li>
                                <li>
                                    <strong>Bảo vệ quyền lợi:</strong> Ngăn chặn gian lận, vi phạm điều khoản
                                </li>
                                <li>
                                    <strong>Có sự đồng ý:</strong> Khi bạn cho phép chia sẻ thông tin
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Section 5 -->
                    <div id="section-5" class="terms-section">
                        <h2>
                            <span class="section-number">5.</span>
                            Cookies & Tracking Technologies
                        </h2>
                        <div class="section-content">
                            <h3>5.1. Cookies Là Gì?</h3>
                            <p>
                                Cookies là các file văn bản nhỏ được lưu trữ trên thiết bị của bạn khi
                                truy cập website. Chúng giúp website ghi nhớ thông tin về chuyến thăm của bạn.
                            </p>

                            <h3>5.2. Chúng Tôi Sử Dụng Cookies Để:</h3>
                            <ul>
                                <li>Duy trì phiên đăng nhập của bạn</li>
                                <li>Ghi nhớ tùy chọn (dark mode, ngôn ngữ...)</li>
                                <li>Phân tích lưu lượng truy cập</li>
                                <li>Cải thiện trải nghiệm người dùng</li>
                            </ul>

                            <h3>5.3. Quản Lý Cookies</h3>
                            <p>
                                Bạn có thể xóa hoặc chặn cookies thông qua cài đặt trình duyệt.
                                Tuy nhiên, điều này có thể ảnh hưởng đến một số tính năng của website.
                            </p>

                            <div class="info-box">
                                <i class="fa fa-info-circle"></i>
                                <p>
                                    <strong>Lưu ý:</strong> Chúng tôi sử dụng Google Analytics để theo dõi
                                    lượng truy cập. Bạn có thể từ chối bằng cách cài đặt
                                    <a href="https://tools.google.com/dlpage/gaoptout" target="_blank">
                                        Google Analytics Opt-out Browser Add-on
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 6 -->
                    <div id="section-6" class="terms-section">
                        <h2>
                            <span class="section-number">6.</span>
                            Bảo Mật Dữ Liệu
                        </h2>
                        <div class="section-content">
                            <p>
                                Chúng tôi áp dụng các biện pháp bảo mật kỹ thuật và tổ chức hợp lý để
                                bảo vệ thông tin của bạn khỏi truy cập trái phép, mất mát hoặc lạm dụng.
                            </p>

                            <h3>Các Biện Pháp Bảo Mật:</h3>
                            <ul>
                                <li>
                                    <i class="fa fa-lock"></i>
                                    <strong>Mã hóa SSL/TLS:</strong> Bảo vệ dữ liệu khi truyền tải
                                </li>
                                <li>
                                    <i class="fa fa-database"></i>
                                    <strong>Mã hóa mật khẩu:</strong> Sử dụng bcrypt hash
                                </li>
                                <li>
                                    <i class="fa fa-server"></i>
                                    <strong>Server bảo mật:</strong> Firewall, DDoS protection
                                </li>
                                <li>
                                    <i class="fa fa-user-secret"></i>
                                    <strong>Kiểm soát truy cập:</strong> Giới hạn quyền truy cập nội bộ
                                </li>
                                <li>
                                    <i class="fa fa-refresh"></i>
                                    <strong>Sao lưu định kỳ:</strong> Backup dữ liệu thường xuyên
                                </li>
                            </ul>

                            <div class="warning-box">
                                <i class="fa fa-exclamation-triangle"></i>
                                <p>
                                    <strong>Quan trọng:</strong> Không có hệ thống nào an toàn 100%.
                                    Chúng tôi khuyến khích bạn sử dụng mật khẩu mạnh và không chia sẻ
                                    thông tin đăng nhập của mình.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 7 -->
                    <div id="section-7" class="terms-section">
                        <h2>
                            <span class="section-number">7.</span>
                            Quyền Của Bạn
                        </h2>
                        <div class="section-content">
                            <p>Theo pháp luật về bảo vệ dữ liệu cá nhân, bạn có các quyền sau:</p>

                            <div class="rights-grid">
                                <div class="right-item">
                                    <i class="fa fa-eye"></i>
                                    <h4>Quyền Truy Cập</h4>
                                    <p>Xem thông tin cá nhân chúng tôi lưu trữ về bạn</p>
                                </div>

                                <div class="right-item">
                                    <i class="fa fa-edit"></i>
                                    <h4>Quyền Chỉnh Sửa</h4>
                                    <p>Cập nhật, sửa đổi thông tin cá nhân không chính xác</p>
                                </div>

                                <div class="right-item">
                                    <i class="fa fa-trash"></i>
                                    <h4>Quyền Xóa</h4>
                                    <p>Yêu cầu xóa tài khoản và dữ liệu cá nhân</p>
                                </div>

                                <div class="right-item">
                                    <i class="fa fa-ban"></i>
                                    <h4>Quyền Phản Đối</h4>
                                    <p>Từ chối xử lý dữ liệu cho mục đích marketing</p>
                                </div>

                                <div class="right-item">
                                    <i class="fa fa-download"></i>
                                    <h4>Quyền Xuất Dữ Liệu</h4>
                                    <p>Tải xuống dữ liệu cá nhân của bạn</p>
                                </div>

                                <div class="right-item">
                                    <i class="fa fa-pause"></i>
                                    <h4>Quyền Hạn Chế</h4>
                                    <p>Yêu cầu tạm dừng xử lý dữ liệu cá nhân</p>
                                </div>
                            </div>

                            <p>
                                Để thực hiện các quyền trên, vui lòng liên hệ:
                                <a href="mailto:marcander.tvd11@gmail.com">marcander.tvd11@gmail.com</a>
                            </p>
                        </div>
                    </div>

                    <!-- Section 8 -->
                    <div id="section-8" class="terms-section">
                        <h2>
                            <span class="section-number">8.</span>
                            Chính Sách Đối Với Trẻ Em
                        </h2>
                        <div class="section-content">
                            <p>
                                Dịch vụ của chúng tôi không nhắm đến trẻ em dưới 13 tuổi. Chúng tôi không
                                cố ý thu thập thông tin cá nhân từ trẻ em dưới 13 tuổi.
                            </p>
                            <p>
                                Nếu bạn là phụ huynh/người giám hộ và phát hiện con em mình đã cung cấp
                                thông tin cá nhân cho chúng tôi, vui lòng liên hệ để chúng tôi xóa thông tin đó.
                            </p>
                            <div class="info-box">
                                <i class="fa fa-child"></i>
                                <p>
                                    <strong>Khuyến nghị:</strong> Trẻ em từ 13-18 tuổi nên sử dụng dịch vụ
                                    dưới sự giám sát của phụ huynh.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 9 -->
                    <div id="section-9" class="terms-section">
                        <h2>
                            <span class="section-number">9.</span>
                            Thay Đổi Chính Sách
                        </h2>
                        <div class="section-content">
                            <p>
                                Chúng tôi có thể cập nhật Chính sách Bảo mật này theo thời gian để phản ánh
                                những thay đổi trong hoạt động hoặc yêu cầu pháp lý.
                            </p>
                            <p>
                                Mọi thay đổi quan trọng sẽ được thông báo qua email (nếu bạn đã đăng ký)
                                hoặc thông báo trên website. Ngày "Cập nhật lần cuối" ở đầu trang sẽ được
                                thay đổi khi có cập nhật.
                            </p>
                        </div>
                    </div>

                    <!-- Section 10 -->
                    <div id="section-10" class="terms-section">
                        <h2>
                            <span class="section-number">10.</span>
                            Liên Hệ
                        </h2>
                        <div class="section-content">
                            <p>
                                Nếu bạn có bất kỳ câu hỏi, thắc mắc hoặc yêu cầu nào liên quan đến
                                Chính sách Bảo mật này, vui lòng liên hệ với chúng tôi:
                            </p>
                            <div class="contact-box">
                                <div class="contact-item">
                                    <i class="fa fa-envelope"></i>
                                    <strong>Email:</strong>
                                    <a href="mailto:marcander.tvd11@gmail.com">marcander.tvd11@gmail.com</a>
                                </div>
                                <div class="contact-item">
                                    <i class="fa fa-globe"></i>
                                    <strong>Website:</strong>
                                    <a href="<?php echo home_url('/'); ?>">TruyenQQ.com</a>
                                </div>
                                <div class="contact-item">
                                    <i class="fa fa-facebook"></i>
                                    <strong>Fanpage:</strong>
                                    <a href="https://www.facebook.com/truyenqqq"
                                        target="_blank">facebook.com/truyenqqq</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Print Button -->
                    <div class="terms-footer">
                        <button onclick="window.print()" class="btn btn-secondary">
                            <i class="fa fa-print"></i>
                            In Chính Sách
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>