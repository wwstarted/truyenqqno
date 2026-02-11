<?php
/**
 * Template Name: Điều Khoản Sử Dụng
 * Description: Trang điều khoản sử dụng TruyenQQ
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

get_header();
?>

<div class="info-page-wrapper dieu-khoan-page">
    <!-- Hero Section -->
    <section class="info-hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Điều Khoản Sử Dụng</h1>
                <p class="hero-subtitle">Quy định và điều khoản khi sử dụng dịch vụ TruyenQQ</p>
                <div class="hero-breadcrumb">
                    <a href="<?php echo home_url('/'); ?>">Trang chủ</a>
                    <span class="separator">/</span>
                    <span>Điều khoản</span>
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
                            <li><a href="#section-1" class="toc-link active">1. Chấp Nhận Điều Khoản</a></li>
                            <li><a href="#section-2" class="toc-link">2. Tài Khoản Người Dùng</a></li>
                            <li><a href="#section-3" class="toc-link">3. Quyền Và Nghĩa Vụ</a></li>
                            <li><a href="#section-4" class="toc-link">4. Nội Dung & Bản Quyền</a></li>
                            <li><a href="#section-5" class="toc-link">5. Hành Vi Cấm</a></li>
                            <li><a href="#section-6" class="toc-link">6. Miễn Trừ Trách Nhiệm</a></li>
                            <li><a href="#section-7" class="toc-link">7. Chấm Dứt Dịch Vụ</a></li>
                            <li><a href="#section-8" class="toc-link">8. Thay Đổi Điều Khoản</a></li>
                            <li><a href="#section-9" class="toc-link">9. Liên Hệ</a></li>
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
                            Chào mừng bạn đến với TruyenQQ. Bằng việc truy cập và sử dụng website của chúng tôi,
                            bạn đồng ý tuân thủ các điều khoản và điều kiện được quy định dưới đây.
                            Vui lòng đọc kỹ trước khi sử dụng dịch vụ.
                        </p>
                    </div>

                    <!-- Section 1 -->
                    <div id="section-1" class="terms-section">
                        <h2>
                            <span class="section-number">1.</span>
                            Chấp Nhận Điều Khoản
                        </h2>
                        <div class="section-content">
                            <p>
                                Khi truy cập và sử dụng website TruyenQQ, bạn xác nhận rằng bạn đã đọc, hiểu và
                                đồng ý bị ràng buộc bởi các điều khoản và điều kiện này.
                            </p>
                            <ul>
                                <li>Bạn phải từ đủ 13 tuổi trở lên để sử dụng dịch vụ</li>
                                <li>Nếu bạn dưới 18 tuổi, cần có sự đồng ý của phụ huynh/người giám hộ</li>
                                <li>Bạn cam kết cung cấp thông tin chính xác và trung thực</li>
                                <li>Bạn chịu trách nhiệm về mọi hoạt động diễn ra dưới tài khoản của mình</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Section 2 -->
                    <div id="section-2" class="terms-section">
                        <h2>
                            <span class="section-number">2.</span>
                            Tài Khoản Người Dùng
                        </h2>
                        <div class="section-content">
                            <h3>2.1. Đăng Ký Tài Khoản</h3>
                            <p>
                                Để sử dụng đầy đủ các tính năng của TruyenQQ, bạn cần tạo tài khoản bằng cách
                                cung cấp thông tin cá nhân như email, tên người dùng và mật khẩu.
                            </p>

                            <h3>2.2. Bảo Mật Tài Khoản</h3>
                            <ul>
                                <li>Bạn có trách nhiệm bảo mật thông tin đăng nhập của mình</li>
                                <li>Không chia sẻ tài khoản cho người khác</li>
                                <li>Thông báo ngay cho chúng tôi nếu phát hiện tài khoản bị xâm nhập</li>
                                <li>Chúng tôi không chịu trách nhiệm về thiệt hại do bạn để lộ thông tin tài khoản</li>
                            </ul>

                            <h3>2.3. Quyền Tạm Ngưng/Xóa Tài Khoản</h3>
                            <p>
                                TruyenQQ có quyền tạm ngưng hoặc xóa tài khoản của bạn nếu phát hiện hành vi
                                vi phạm điều khoản sử dụng mà không cần thông báo trước.
                            </p>
                        </div>
                    </div>

                    <!-- Section 3 -->
                    <div id="section-3" class="terms-section">
                        <h2>
                            <span class="section-number">3.</span>
                            Quyền Và Nghĩa Vụ Người Dùng
                        </h2>
                        <div class="section-content">
                            <div class="highlight-box">
                                <h3>
                                    <i class="fa fa-check-circle"></i>
                                    Quyền Của Người Dùng
                                </h3>
                                <ul>
                                    <li>Truy cập và đọc miễn phí tất cả truyện trên website</li>
                                    <li>Tạo danh sách theo dõi và đánh dấu truyện yêu thích</li>
                                    <li>Tham gia bình luận và thảo luận (nếu có tài khoản)</li>
                                    <li>Nhận thông báo khi có chapter mới của truyện đang theo dõi</li>
                                    <li>Yêu cầu xóa tài khoản và dữ liệu cá nhân bất cứ lúc nào</li>
                                </ul>
                            </div>

                            <div class="highlight-box warning-box">
                                <h3>
                                    <i class="fa fa-exclamation-triangle"></i>
                                    Nghĩa Vụ Của Người Dùng
                                </h3>
                                <ul>
                                    <li>Tuân thủ pháp luật Việt Nam và các điều khoản của TruyenQQ</li>
                                    <li>Không sử dụng dịch vụ cho mục đích bất hợp pháp</li>
                                    <li>Tôn trọng bản quyền và quyền sở hữu trí tuệ</li>
                                    <li>Không spam, quấy rối hoặc gây rối trong cộng đồng</li>
                                    <li>Báo cáo ngay khi phát hiện nội dung vi phạm</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4 -->
                    <div id="section-4" class="terms-section">
                        <h2>
                            <span class="section-number">4.</span>
                            Nội Dung & Bản Quyền
                        </h2>
                        <div class="section-content">
                            <h3>4.1. Quyền Sở Hữu Nội Dung</h3>
                            <p>
                                Tất cả nội dung trên TruyenQQ bao gồm nhưng không giới hạn: truyện tranh, hình ảnh,
                                văn bản, logo, thiết kế giao diện đều thuộc quyền sở hữu của chủ sở hữu bản quyền.
                            </p>

                            <h3>4.2. Sử Dụng Hợp Pháp</h3>
                            <ul>
                                <li>Người dùng chỉ được đọc truyện cho mục đích cá nhân, phi thương mại</li>
                                <li>Nghiêm cấm sao chép, tải xuống, phân phối lại nội dung</li>
                                <li>Không được sử dụng nội dung cho mục đích thương mại</li>
                                <li>Vi phạm sẽ bị xử lý theo pháp luật về bản quyền</li>
                            </ul>

                            <h3>4.3. Khiếu Nại Bản Quyền (DMCA)</h3>
                            <p>
                                Nếu bạn là chủ sở hữu bản quyền và cho rằng nội dung trên website vi phạm quyền của bạn,
                                vui lòng liên hệ: <a
                                    href="mailto:marcander.tvd11@gmail.com">marcander.tvd11@gmail.com</a>
                            </p>
                        </div>
                    </div>

                    <!-- Section 5 -->
                    <div id="section-5" class="terms-section">
                        <h2>
                            <span class="section-number">5.</span>
                            Hành Vi Cấm
                        </h2>
                        <div class="section-content">
                            <p>Người dùng không được phép thực hiện các hành vi sau:</p>

                            <div class="prohibited-grid">
                                <div class="prohibited-item">
                                    <i class="fa fa-ban"></i>
                                    <h4>Spam & Quảng Cáo</h4>
                                    <p>Gửi spam, quảng cáo trái phép trong bình luận hoặc tin nhắn</p>
                                </div>

                                <div class="prohibited-item">
                                    <i class="fa fa-ban"></i>
                                    <h4>Tấn Công Hệ Thống</h4>
                                    <p>Hack, DDoS, hoặc cố gắng phá hoại hệ thống website</p>
                                </div>

                                <div class="prohibited-item">
                                    <i class="fa fa-ban"></i>
                                    <h4>Giả Mạo</h4>
                                    <p>Mạo danh người khác, tổ chức hoặc admin/mod</p>
                                </div>

                                <div class="prohibited-item">
                                    <i class="fa fa-ban"></i>
                                    <h4>Nội Dung Vi Phạm</h4>
                                    <p>Đăng nội dung khiêu dâm, bạo lực, kích động thù hận</p>
                                </div>

                                <div class="prohibited-item">
                                    <i class="fa fa-ban"></i>
                                    <h4>Thu Thập Dữ Liệu</h4>
                                    <p>Crawl, scrape hoặc thu thập dữ liệu tự động</p>
                                </div>

                                <div class="prohibited-item">
                                    <i class="fa fa-ban"></i>
                                    <h4>Phân Phối Lại</h4>
                                    <p>Tải xuống và phân phối lại truyện trên nền tảng khác</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 6 -->
                    <div id="section-6" class="terms-section">
                        <h2>
                            <span class="section-number">6.</span>
                            Miễn Trừ Trách Nhiệm
                        </h2>
                        <div class="section-content">
                            <p>
                                TruyenQQ cung cấp dịch vụ theo nguyên tắc "NGUYÊN TRẠNG" (AS IS) và
                                "KHI CÓ SẴN" (AS AVAILABLE). Chúng tôi không đảm bảo:
                            </p>
                            <ul>
                                <li>Website hoạt động liên tục không gián đoạn</li>
                                <li>Không có lỗi kỹ thuật hoặc virus</li>
                                <li>Tính chính xác, đầy đủ của nội dung</li>
                                <li>Phù hợp cho mục đích cụ thể nào đó</li>
                            </ul>

                            <div class="warning-notice">
                                <i class="fa fa-info-circle"></i>
                                <p>
                                    <strong>Lưu ý:</strong> TruyenQQ không chịu trách nhiệm về bất kỳ thiệt hại nào
                                    phát sinh từ việc sử dụng hoặc không thể sử dụng dịch vụ, bao gồm nhưng không
                                    giới hạn: mất dữ liệu, lỗi hệ thống, hoặc thiệt hại gián tiếp.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 7 -->
                    <div id="section-7" class="terms-section">
                        <h2>
                            <span class="section-number">7.</span>
                            Chấm Dứt Dịch Vụ
                        </h2>
                        <div class="section-content">
                            <h3>7.1. Chấm Dứt Bởi Người Dùng</h3>
                            <p>
                                Bạn có thể ngừng sử dụng dịch vụ bất cứ lúc nào bằng cách xóa tài khoản
                                trong phần cài đặt hoặc liên hệ với chúng tôi.
                            </p>

                            <h3>7.2. Chấm Dứt Bởi TruyenQQ</h3>
                            <p>
                                Chúng tôi có quyền tạm ngưng hoặc chấm dứt quyền truy cập của bạn trong các trường hợp:
                            </p>
                            <ul>
                                <li>Vi phạm điều khoản sử dụng</li>
                                <li>Sử dụng dịch vụ cho mục đích bất hợp pháp</li>
                                <li>Gây thiệt hại cho TruyenQQ hoặc người dùng khác</li>
                                <li>Tài khoản không hoạt động trong thời gian dài (trên 2 năm)</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Section 8 -->
                    <div id="section-8" class="terms-section">
                        <h2>
                            <span class="section-number">8.</span>
                            Thay Đổi Điều Khoản
                        </h2>
                        <div class="section-content">
                            <p>
                                TruyenQQ có quyền chỉnh sửa, cập nhật điều khoản sử dụng bất cứ lúc nào.
                                Các thay đổi sẽ có hiệu lực ngay khi được đăng tải trên website.
                            </p>
                            <p>
                                Chúng tôi khuyến khích bạn thường xuyên kiểm tra trang này để nắm bắt các
                                cập nhật mới nhất. Việc tiếp tục sử dụng dịch vụ sau khi có thay đổi đồng nghĩa
                                với việc bạn chấp nhận các điều khoản mới.
                            </p>
                        </div>
                    </div>

                    <!-- Section 9 -->
                    <div id="section-9" class="terms-section">
                        <h2>
                            <span class="section-number">9.</span>
                            Liên Hệ
                        </h2>
                        <div class="section-content">
                            <p>
                                Nếu bạn có bất kỳ câu hỏi nào về Điều khoản Sử dụng này, vui lòng liên hệ:
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
                            </div>
                        </div>
                    </div>

                    <!-- Print Button -->
                    <div class="terms-footer">
                        <button onclick="window.print()" class="btn btn-secondary">
                            <i class="fa fa-print"></i>
                            In Điều Khoản
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>