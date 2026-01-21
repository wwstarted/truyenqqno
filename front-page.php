<?php get_header(); ?>

<?php

/**
 * Section: Truyện Hay (Hot Comics Carousel)
 * Hiển thị 16 truyện hot nhất theo view count
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

$args = array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => 16,
    'orderby' => 'meta_value_num',
    'meta_key' => '_nettruyen_total_views',
    'order' => 'DESC'
);

global $wpdb;
$stats_table = $wpdb->prefix . 'nettruyen_view_stats';

$hot_comics_ids = $wpdb->get_col(
    "SELECT post_id 
    FROM {$stats_table} 
    WHERE total_display_views > 0 
    ORDER BY total_display_views DESC 
    LIMIT 16"
);

if (empty($hot_comics_ids)) {
    $args = array(
        'post_type' => 'nettruyen_comic',
        'post_status' => 'publish',
        'posts_per_page' => 16,
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $hot_comics = new WP_Query($args);
} else {
    $args = array(
        'post_type' => 'nettruyen_comic',
        'post_status' => 'publish',
        'post__in' => $hot_comics_ids,
        'orderby' => 'post__in',
        'posts_per_page' => 16
    );
    $hot_comics = new WP_Query($args);
}

if (!$hot_comics->have_posts()) {
    return;
}
?>

<section class="homepage-suggest">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa fa-star"></i>
                <span>Truyện hay</span>
            </h2>
        </div>

        <!-- Swiper Carousel -->
        <div class="truyen-hay-carousel">
            <div class="swiper truyen-hay-swiper">
                <div class="swiper-wrapper">
                    <?php
                    $index = 0;
                    while ($hot_comics->have_posts()):
                        $hot_comics->the_post();
                        $post_id = get_the_ID();
                        $index++;

                        $thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);
                        if (empty($thumbnail)) {
                            $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
                        }
                        if (empty($thumbnail)) {
                            $thumbnail = 'https://via.placeholder.com/190x247?text=No+Image';
                        }

                        $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
                        $manifest = !empty($manifest_json) ? json_decode($manifest_json, true) : null;

                        $latest_chapter = 'Đang cập nhật';
                        if (!empty($manifest['chapters'])) {
                            $chapters = $manifest['chapters'];
                            $latest = end($chapters);
                            $latest_chapter = 'Chương ' . $latest['name'];
                        }

                        $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_the_modified_time('U');
                        $time_ago = human_time_diff(strtotime($updated_at), current_time('timestamp')) . ' trước';

                        $is_hot = ($index <= 10);
                        ?>

                    <div class="swiper-slide">
                        <div class="comic-card">
                            <!-- Thumbnail -->
                            <div class="comic-avatar">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?>"
                                        loading="lazy">
                                </a>

                                <!-- Bookmark Button (UI Only) -->
                                <span class="bookmark-badge" title="Theo dõi" data-post-id="<?php echo $post_id; ?>">
                                    <i class="fa fa-bookmark-o"></i>
                                </span>

                                <!-- Top Notice: Time + Hot Badge -->
                                <div class="top-notice">
                                    <span class="time-ago">
                                        <?php echo esc_html($time_ago); ?>
                                    </span>
                                    <?php if ($is_hot): ?>
                                    <span class="hot-badge">Hot</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Comic Info -->
                            <div class="comic-info">
                                <h3 class="comic-name">
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                <div class="latest-chapter">
                                    <a href="<?php the_permalink(); ?>"
                                        title="Đọc <?php echo esc_attr($latest_chapter); ?>">
                                        <?php echo esc_html($latest_chapter); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="swiper-nav">
                <button class="swiper-button-prev">
                    <i class="fa fa-angle-left"></i>
                </button>
                <button class="swiper-button-next">
                    <i class="fa fa-angle-right"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<?php
/**
 * Section: Độc Quyền Truyện QQ
 * Hiển thị 16 truyện ngẫu nhiên (sẽ update logic sau)
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

$args = array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => 16,
    'orderby' => 'rand',
    'order' => 'DESC'
);

$exclusive_comics = new WP_Query($args);

if (!$exclusive_comics->have_posts()) {
    return;
}
?>

<section class="homepage-exclusive">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <h2 class="section-title">
                <a href="/truyen-dich-qq.html" title="Độc Quyền Truyện QQ">
                    <i class="fa fa-book"></i>
                    <span>Độc Quyền Truyện QQ</span>
                </a>
            </h2>
        </div>

        <!-- Swiper Carousel -->
        <div class="exclusive-carousel">
            <div class="swiper exclusive-swiper">
                <div class="swiper-wrapper">
                    <?php
                    $index = 0;
                    while ($exclusive_comics->have_posts()):
                        $exclusive_comics->the_post();
                        $post_id = get_the_ID();
                        $index++;

                        $thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);
                        if (empty($thumbnail)) {
                            $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
                        }
                        if (empty($thumbnail)) {
                            $thumbnail = 'https://via.placeholder.com/190x247?text=No+Image';
                        }

                        $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
                        $manifest = !empty($manifest_json) ? json_decode($manifest_json, true) : null;

                        $latest_chapter = 'Đang cập nhật';
                        if (!empty($manifest['chapters'])) {
                            $chapters = $manifest['chapters'];
                            $latest = end($chapters);
                            $latest_chapter = 'Chương ' . $latest['name'];
                        }

                        $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_the_modified_time('U');
                        $time_ago = human_time_diff(strtotime($updated_at), current_time('timestamp')) . ' trước';

                        $is_hot = ($index <= 10);
                        ?>

                    <div class="swiper-slide">
                        <div class="comic-card">
                            <!-- Thumbnail -->
                            <div class="comic-avatar">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?>"
                                        loading="lazy">
                                </a>

                                <!-- Bookmark Button (UI Only) -->
                                <span class="bookmark-badge" title="Theo dõi" data-post-id="<?php echo $post_id; ?>">
                                    <i class="fa fa-bookmark-o"></i>
                                </span>

                                <!-- Top Notice: Time + Hot Badge -->
                                <div class="top-notice">
                                    <span class="time-ago">
                                        <?php echo esc_html($time_ago); ?>
                                    </span>
                                    <?php if ($is_hot): ?>
                                    <span class="hot-badge">Hot</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Comic Info -->
                            <div class="comic-info">
                                <h3 class="comic-name">
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                <div class="latest-chapter">
                                    <a href="<?php the_permalink(); ?>"
                                        title="Đọc <?php echo esc_attr($latest_chapter); ?>">
                                        <?php echo esc_html($latest_chapter); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="swiper-nav">
                <button class="swiper-button-prev">
                    <i class="fa fa-angle-left"></i>
                </button>
                <button class="swiper-button-next">
                    <i class="fa fa-angle-right"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<?php
/**
 * Section: Truyện Mới Cập Nhật
 * Hiển thị 42 truyện mới nhất theo ngày cập nhật
 * Grid layout: Desktop (6x7), Tablet (4x11), Mobile (2x21)
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

$args = array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => 42,
    'orderby' => 'modified',
    'order' => 'DESC'
);

$new_comics = new WP_Query($args);

if (!$new_comics->have_posts()) {
    return;
}

global $wpdb;
$stats_table = $wpdb->prefix . 'nettruyen_view_stats';
$hot_comic_ids = $wpdb->get_col(
    "SELECT post_id 
    FROM {$stats_table} 
    WHERE total_display_views > 0 
    ORDER BY total_display_views DESC 
    LIMIT 20"
);
?>

<section class="homepage-new-update">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <h2 class="section-title">
                <a href="/truyen-moi-cap-nhat.html" title="Truyện mới cập nhật">
                    <i class="fa fa-cloud-download"></i>
                    <span>Truyện mới cập nhật</span>
                </a>
            </h2>
            <div class="filter-button">
                <a href="<?php echo esc_url(home_url('/tim-kiem-nang-cao')) ?>" title="Lọc truyện">
                    <button type="button">
                        <i class="fa fa-filter"></i>
                    </button>
                </a>
            </div>
        </div>

        <!-- Grid Layout -->
        <div class="comics-grid">
            <?php
            $index = 0;
            while ($new_comics->have_posts()):
                $new_comics->the_post();
                $post_id = get_the_ID();
                $index++;

                $thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);
                if (empty($thumbnail)) {
                    $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
                }
                if (empty($thumbnail)) {
                    $thumbnail = 'https://via.placeholder.com/190x247?text=No+Image';
                }

                $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
                $manifest = !empty($manifest_json) ? json_decode($manifest_json, true) : null;

                $latest_chapter = 'Đang cập nhật';
                if (!empty($manifest['chapters'])) {
                    $chapters = $manifest['chapters'];
                    $latest = end($chapters);
                    $latest_chapter = 'Chapter ' . $latest['name'];
                }

                $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_the_modified_time('U');
                $time_ago = human_time_diff(strtotime($updated_at), current_time('timestamp')) . ' trước';

                $follow_count = get_post_meta($post_id, '_nettruyen_follow_count', true);
                if (empty($follow_count)) {
                    $follow_count = 0;
                }

                $view_count = 0;
                $view_stats = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT total_display_views FROM {$stats_table} WHERE post_id = %d",
                        $post_id
                    )
                );
                if ($view_stats) {
                    $view_count = $view_stats->total_display_views;
                }

                $follow_count_formatted = number_format($follow_count);
                $view_count_formatted = number_format($view_count);

                $is_hot = in_array($post_id, $hot_comic_ids);
                $is_new = (current_time('timestamp') - strtotime($updated_at)) <= (7 * 24 * 60 * 60);
                $badge_type = '';
                $badge_text = '';
                if ($is_hot) {
                    $badge_type = 'hot';
                    $badge_text = 'Hot';
                } elseif ($is_new) {
                    $badge_type = 'new';
                    $badge_text = 'New';
                }
                ?>

            <div class="comic-item">
                <div class="comic-card">
                    <!-- Thumbnail -->
                    <div class="comic-avatar">
                        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?>"
                                loading="lazy">
                        </a>

                        <!-- Bookmark Button -->
                        <span class="bookmark-badge" title="Theo dõi" data-post-id="<?php echo $post_id; ?>">
                            <i class="fa fa-bookmark-o"></i>
                        </span>

                        <!-- Top Notice: Time + Badge -->
                        <div class="top-notice">
                            <span class="time-ago">
                                <?php echo esc_html($time_ago); ?>
                            </span>
                            <?php if ($badge_type): ?>
                            <span class="type-label <?php echo esc_attr($badge_type); ?>">
                                <?php echo esc_html($badge_text); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Comic Info -->
                    <div class="comic-info">
                        <h3 class="comic-name">
                            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h3>

                        <!-- Stats -->
                        <div class="comic-stats">
                            <span class="stat-item">
                                <i class="fa fa-bookmark"></i>
                                <?php echo esc_html($follow_count_formatted); ?>
                            </span>
                            <span class="stat-item">
                                <i class="fa fa-eye"></i>
                                <?php echo esc_html($view_count_formatted); ?>
                            </span>
                        </div>

                        <!-- Latest Chapter -->
                        <div class="latest-chapter">
                            <a href="<?php the_permalink(); ?>" title="Đọc <?php echo esc_attr($latest_chapter); ?>">
                                <?php echo esc_html($latest_chapter); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <?php endwhile;
            wp_reset_postdata(); ?>
        </div>

        <!-- View More Button -->
        <div class="view-more-section">
            <a href="<?php echo esc_url(home_url('/truyen-moi-cap-nhat')); ?>" class="view-more-btn">
                Xem thêm nhiều truyện
            </a>
        </div>
    </div>
</section>


<section class="blog-detail-section">
    <div class="blog-detail-container">
        <!-- ✅ ĐỔI TÊN -->
        <div class="blog-detail-header">
            <!-- ✅ ĐỔI TÊN -->
            <h2 class="blog-detail-section-title">
                <!-- ✅ ĐỔI TÊN -->
                <i class="fa fa-file-text"></i>
                <span>Bài Viết Nổi Bật</span>
            </h2>
        </div>

        <!-- Blog Detail Content -->
        <div class="blog-detail-wrapper" id="blogDetailWrapper">
            <div class="blog-detail-content" id="blogDetailContent">
                <h1 class="blog-detail-title">
                    Hướng Dẫn Đọc Truyện Tranh Online: Top 10 Manhwa/Manga Hay Nhất 2026
                </h1>

                <div class="blog-detail-meta">
                    <span class="meta-item">
                        <i class="fa fa-calendar"></i>
                        18/01/2026
                    </span>
                    <span class="meta-item">
                        <i class="fa fa-user"></i>
                        Admin
                    </span>
                    <span class="meta-item">
                        <i class="fa fa-eye"></i>
                        2,547 lượt xem
                    </span>
                </div>

                <div class="blog-detail-body">
                    <p>
                        Đọc truyện tranh online đã trở thành một trong những hình thức giải trí phổ biến nhất hiện nay.
                        Với sự phát triển của công nghệ, việc tiếp cận các tác phẩm manga, manhwa, manhua chất lượng
                        cao chưa bao giờ dễ dàng đến thế. Trong bài viết này, chúng tôi sẽ giới thiệu đến bạn những
                        bộ truyện tranh xuất sắc nhất năm 2026 và cách để tận hưởng chúng một cách tốt nhất.
                    </p>

                    <h2>1. Tại Sao Nên Đọc Truyện Tranh Online?</h2>
                    <p>
                        Việc đọc truyện tranh online mang lại nhiều lợi ích vượt trội so với việc mua sách giấy truyền
                        thống:
                    </p>
                    <ul>
                        <li><strong>Tiết kiệm chi phí:</strong> Phần lớn các trang web đọc truyện đều miễn phí hoặc có
                            chi phí rất thấp</li>
                        <li><strong>Cập nhật nhanh chóng:</strong> Các chapter mới được đăng tải ngay khi phát hành</li>
                        <li><strong>Kho tàng khổng lồ:</strong> Hàng nghìn bộ truyện từ nhiều thể loại khác nhau</li>
                        <li><strong>Đọc mọi lúc mọi nơi:</strong> Chỉ cần thiết bị có kết nối internet</li>
                        <li><strong>Chất lượng hình ảnh cao:</strong> Nhiều trang web hỗ trợ hình ảnh HD, Full Color
                        </li>
                    </ul>

                    <h2>2. Top 10 Manhwa/Manga Đáng Đọc Nhất 2026</h2>

                    <h3>2.1. Solo Leveling (Tôi Thăng Cấp Một Mình)</h3>
                    <p>
                        Câu chuyện về Sung Jin-Woo, một thợ săn hạng E yếu nhất, đã trải qua một cuộc cách mạng sau khi
                        tỉnh dậy với khả năng đặc biệt - hệ thống thăng cấp. Bộ truyện này đã tạo nên cơn sốt toàn cầu
                        với cốt truyện hấp dẫn, hình ảnh đẹp mắt và nhân vật chính cực kỳ charismatic.
                    </p>

                    <h3>2.2. Tower of God (Tháp Thần)</h3>
                    <p>
                        Một trong những manhwa dài nhất và được yêu thích nhất, Tower of God kể về hành trình của Bam
                        trong việc leo lên tháp bí ẩn để tìm kiếm người bạn quan trọng nhất của mình. Thế giới quan
                        phức tạp, hệ thống ma thuật độc đáo và những twist bất ngờ khiến độc giả không thể rời mắt.
                    </p>

                    <h3>2.3. The Beginning After The End</h3>
                    <p>
                        Câu chuyện về một vị vua quyền năng được tái sinh vào một thế giới ma thuật mới. Với kiến thức
                        và kinh nghiệm từ kiếp trước, anh bắt đầu hành trình trở thành người mạnh nhất một lần nữa.
                        Bộ truyện kết hợp hoàn hảo giữa action, magic và phát triển nhân vật.
                    </p>

                    <h2>3. Các Thể Loại Truyện Tranh Phổ Biến</h2>
                    <p>
                        Thế giới truyện tranh vô cùng đa dạng với nhiều thể loại khác nhau:
                    </p>

                    <h3>3.1. Action/Fantasy</h3>
                    <p>
                        Thể loại hành động và phiêu lưu luôn là lựa chọn hàng đầu của đa số độc giả. Các bộ truyện
                        thuộc thể loại này thường có nhịp độ nhanh, cảnh chiến đấu hoành tráng và hệ thống năng lực độc
                        đáo.
                    </p>

                    <h3>3.2. Romance/Drama</h3>
                    <p>
                        Những câu chuyện tình cảm lãng mạn, drama đời thường cũng chiếm được nhiều cảm tình của người
                        đọc.
                        Các tác phẩm này thường tập trung vào sự phát triển mối quan hệ giữa các nhân vật.
                    </p>

                    <h3>3.3. Isekai/Reincarnation</h3>
                    <p>
                        Thể loại chuyển sinh đang rất hot hiện nay. Nhân vật chính thường được chuyển sinh hoặc tri소환
                        đến một thế giới khác, nơi họ sử dụng kiến thức của mình để trở nên mạnh mẽ.
                    </p>

                    <h2>4. Mẹo Để Tận Hưởng Truyện Tranh Online</h2>
                    <ul>
                        <li>Chọn một trang web uy tín với giao diện thân thiện</li>
                        <li>Sử dụng chế độ đọc phù hợp (từ trái sang phải hoặc từ phải sang trái)</li>
                        <li>Điều chỉnh độ sáng màn hình để bảo vệ mắt</li>
                        <li>Tạo danh sách theo dõi các bộ truyện yêu thích</li>
                        <li>Tham gia cộng đồng để thảo luận và chia sẻ</li>
                    </ul>

                    <h2>5. Kết Luận</h2>
                    <p>
                        Đọc truyện tranh online không chỉ là một hình thức giải trí mà còn là cách để chúng ta khám phá
                        những thế giới mới, những câu chuyện ý nghĩa và kết nối với cộng đồng người hâm mộ trên toàn thế
                        giới.
                        Hãy bắt đầu hành trình khám phá của bạn ngay hôm nay và tìm ra những bộ truyện yêu thích của
                        riêng mình!
                    </p>

                    <p>
                        Chúng tôi hy vọng danh sách này sẽ giúp bạn tìm được những bộ truyện phù hợp với sở thích của
                        mình.
                        Đừng quên theo dõi chúng tôi để cập nhật thêm nhiều bài viết hữu ích khác nhé!
                    </p>
                </div>
            </div>

            <!-- Expand/Collapse Buttons -->
            <div class="blog-detail-actions">
                <button class="btn-expand" id="btnReadMore">
                    <i class="fa fa-angle-down"></i>
                    Đọc thêm
                </button>
                <button class="btn-collapse" id="btnReadLess" style="display: none;">
                    <i class="fa fa-angle-up"></i>
                    Thu gọn
                </button>
            </div>
        </div>
    </div>
</section>

<?php
/**
 * Section: Featured Articles (Blog Carousel)
 * Hiển thị 9 bài viết blog từ CPT trong carousel
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */


$featured_articles = array(
    array(
        'id' => 1,
        'title' => 'Top 10 Manhwa Hành Động Hay Nhất 2026 Bạn Không Thể Bỏ Qua',
        'excerpt' => 'Khám phá những bộ manhwa hành động đỉnh cao với cốt truyện hấp dẫn, nhân vật mạnh mẽ và những trận chiến hoành tráng nhất năm 2026.',
        'thumbnail' => 'https://images.unsplash.com/photo-1618519764620-7403abdbdfe9?w=400&h=300&fit=crop',
        'category' => 'Top 10',
        'date' => '18/01/2026',
        'views' => 3254,
        'featured' => true,
        'url' => '#'
    ),
    array(
        'id' => 2,
        'title' => 'Hướng Dẫn Chọn Manga Phù Hợp Với Sở Thích Của Bạn',
        'excerpt' => 'Bí quyết để tìm ra những bộ manga hoàn hảo dành riêng cho bạn dựa trên thể loại yêu thích và phong cách đọc truyện.',
        'thumbnail' => 'https://images.unsplash.com/photo-1612178991541-c9e8c256bb7d?w=400&h=300&fit=crop',
        'category' => 'Hướng dẫn',
        'date' => '17/01/2026',
        'views' => 2847,
        'featured' => true,
        'url' => '#'
    ),
    array(
        'id' => 3,
        'title' => 'Phân Tích: Sự Khác Biệt Giữa Manga, Manhwa và Manhua',
        'excerpt' => 'Tìm hiểu sâu về những điểm khác biệt về phong cách vẽ, cách kể chuyện và văn hóa của ba dòng truyện tranh châu Á.',
        'thumbnail' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop',
        'category' => 'Review',
        'date' => '16/01/2026',
        'views' => 1923,
        'featured' => true,
        'url' => '#'
    ),
    array(
        'id' => 4,
        'title' => '5 Bộ Manhwa Romance Ngọt Ngào Khiến Bạn "Tan Chảy"',
        'excerpt' => 'Những câu chuyện tình yêu lãng mạn, ngọt ngào nhất trong thế giới manhwa sẽ khiến trái tim bạn rung động.',
        'thumbnail' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&h=300&fit=crop',
        'category' => 'Romance',
        'date' => '15/01/2026',
        'views' => 4521,
        'featured' => false,
        'url' => '#'
    ),
    array(
        'id' => 5,
        'title' => 'Isekai Là Gì? Tổng Hợp Các Bộ Truyện Chuyển Sinh Đỉnh Cao',
        'excerpt' => 'Khám phá thể loại Isekai - xu hướng truyện tranh hot nhất hiện nay với những tác phẩm xuất sắc nhất.',
        'thumbnail' => 'https://images.unsplash.com/photo-1613376023733-0a73315d9b06?w=400&h=300&fit=crop',
        'category' => 'Tin tức',
        'date' => '14/01/2026',
        'views' => 3892,
        'featured' => false,
        'url' => '#'
    ),
    array(
        'id' => 6,
        'title' => 'Cách Tối Ưu Trải Nghiệm Đọc Truyện Trên Điện Thoại',
        'excerpt' => 'Mẹo và thủ thuật giúp bạn đọc truyện tranh online mượt mà hơn, tiết kiệm pin và bảo vệ đôi mắt hiệu quả.',
        'thumbnail' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=400&h=300&fit=crop',
        'category' => 'Hướng dẫn',
        'date' => '13/01/2026',
        'views' => 2156,
        'featured' => false,
        'url' => '#'
    ),
    array(
        'id' => 7,
        'title' => 'Top 7 Nhân Vật Phản Diện Được Yêu Thích Nhất Trong Manga',
        'excerpt' => 'Những kẻ phản diện với chiều sâu tính cách phức tạp đã chiếm trọn trái tim người hâm mộ.',
        'thumbnail' => 'https://images.unsplash.com/photo-1601850494422-3cf14624b0b3?w=400&h=300&fit=crop',
        'category' => 'Top 10',
        'date' => '12/01/2026',
        'views' => 5634,
        'featured' => false,
        'url' => '#'
    ),
    array(
        'id' => 8,
        'title' => 'Xu Hướng Truyện Tranh 2026: Những Thể Loại Đang Lên Ngôi',
        'excerpt' => 'Phân tích các xu hướng mới trong ngành công nghiệp truyện tranh và dự đoán những thể loại sẽ bùng nổ.',
        'thumbnail' => 'https://images.unsplash.com/photo-1535016120720-40c646be5580?w=400&h=300&fit=crop',
        'category' => 'Tin tức',
        'date' => '11/01/2026',
        'views' => 1847,
        'featured' => false,
        'url' => '#'
    ),
    array(
        'id' => 9,
        'title' => 'Bí Mật Đằng Sau Thành Công Của Solo Leveling',
        'excerpt' => 'Phân tích chuyên sâu về những yếu tố khiến Solo Leveling trở thành hiện tượng toàn cầu.',
        'thumbnail' => 'https://images.unsplash.com/photo-1604004555489-723a93d6ce74?w=400&h=300&fit=crop',
        'category' => 'Review',
        'date' => '10/01/2026',
        'views' => 7821,
        'featured' => false,
        'url' => '#'
    )
);
?>

<section class="featured-articles-section">
    <div class="featured-articles-container">
        <!-- ✅ ĐỔI TÊN -->
        <div class="featured-articles-header">
            <!-- ✅ ĐỔI TÊN -->
            <h2 class="featured-articles-section-title">
                <i class="fa fa-newspaper-o"></i>
                <span>Bài Viết Mới Nhất</span>
            </h2>
        </div>

        <!-- Articles Carousel -->
        <div class="articles-carousel">
            <div class="swiper articles-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($featured_articles as $article): ?>
                    <div class="swiper-slide">
                        <article class="article-card">
                            <!-- Thumbnail -->
                            <div class="article-thumbnail">
                                <a href="<?php echo esc_url($article['url']); ?>"
                                    title="<?php echo esc_attr($article['title']); ?>">
                                    <img src="<?php echo esc_url($article['thumbnail']); ?>"
                                        alt="<?php echo esc_attr($article['title']); ?>" loading="lazy">
                                </a>

                                <?php if ($article['featured']): ?>
                                <span class="featured-badge">
                                    <i class="fa fa-star"></i> Nổi bật
                                </span>
                                <?php endif; ?>
                            </div>

                            <!-- Article Content -->
                            <div class="article-content">
                                <!-- Meta -->
                                <div class="article-meta">
                                    <span class="article-category">
                                        <?php echo esc_html($article['category']); ?>
                                    </span>
                                    <span class="article-date">
                                        <i class="fa fa-calendar"></i>
                                        <?php echo esc_html($article['date']); ?>
                                    </span>
                                </div>

                                <!-- Title -->
                                <h3 class="article-title">
                                    <a href="<?php echo esc_url($article['url']); ?>"
                                        title="<?php echo esc_attr($article['title']); ?>">
                                        <?php echo esc_html($article['title']); ?>
                                    </a>
                                </h3>

                                <!-- Excerpt -->
                                <p class="article-excerpt">
                                    <?php echo esc_html($article['excerpt']); ?>
                                </p>

                                <!-- Footer -->
                                <div class="article-footer">
                                    <a href="<?php echo esc_url($article['url']); ?>" class="read-more-link">
                                        Đọc thêm
                                        <i class="fa fa-arrow-right"></i>
                                    </a>
                                    <span class="article-views">
                                        <i class="fa fa-eye"></i>
                                        <?php echo number_format($article['views']); ?>
                                    </span>
                                </div>
                            </div>
                        </article>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Navigation -->
            <div class="swiper-nav">
                <button class="swiper-button-prev">
                    <i class="fa fa-angle-left"></i>
                </button>
                <button class="swiper-button-next">
                    <i class="fa fa-angle-right"></i>
                </button>
            </div>
        </div>

        <!-- Pagination Dots -->
        <div class="swiper-pagination"></div>

        <!-- View All Button -->
        <div class="view-all-articles">
            <a href="<?php echo esc_url(home_url('/blog')); ?>" class="view-all-btn">
                Xem tất cả bài viết
                <i class="fa fa-arrow-right"></i>
            </a>
        </div>
    </div>
    </sect <?php get_footer(); ?>