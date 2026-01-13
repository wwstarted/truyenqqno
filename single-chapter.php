<?php
/**
 * Template Name: Single Chapter Reading Page
 * Template for reading comic chapters
 * 
 * @package TruyenQQ
 * @version 1.0.0
 * ✅ Fixed: querySelectorAll typo
 * ✅ Added: Dark mode compatibility
 */

get_header();

// Get query vars from rewrite rules
$chapter_slug = get_query_var('chapter');
$comic_slug = get_query_var('nettruyen_comic');

if (empty($chapter_slug) || empty($comic_slug)) {
    wp_die('Invalid chapter URL');
}

// Get comic info (already loaded by template_redirect)
$post_id = get_the_ID();
$comic_title = get_the_title();
$thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);

// Get chapter manifest
$chapters_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
$manifest = json_decode($chapters_json, true);
$chapters = isset($manifest['chapters']) ? $manifest['chapters'] : array();

// Find current chapter data
$current_chapter = null;
$current_index = -1;

foreach ($chapters as $index => $chapter) {
    if ($chapter['slug'] === $chapter_slug) {
        $current_chapter = $chapter;
        $current_index = $index;
        break;
    }
}

if (!$current_chapter) {
    wp_die('Chapter not found');
}

// Get chapter images
$image_domain = isset($current_chapter['image_domain']) ? $current_chapter['image_domain'] : '';
$image_path = isset($current_chapter['image_path']) ? $current_chapter['image_path'] : '';
$image_files = isset($current_chapter['image_files']) ? $current_chapter['image_files'] : array();

// Build full image URLs
$chapter_images = array();
if (!empty($image_domain) && !empty($image_path) && !empty($image_files)) {
    foreach ($image_files as $filename) {
        $chapter_images[] = trailingslashit($image_domain) . trailingslashit($image_path) . $filename;
    }
}

// Get navigation URLs
$nav = nettruyen_get_chapter_navigation($post_id, $chapter_slug);
$prev_url = $nav['prev'];
$next_url = $nav['next'];
$current_position = $current_index + 1;
$total_chapters = count($chapters);

// Get chapter name
$chapter_name = isset($current_chapter['name']) ? $current_chapter['name'] : $chapter_slug;
$chapter_updated = isset($current_chapter['updated_at']) && !empty($current_chapter['updated_at'])
    ? date('H:i d/m/Y', strtotime($current_chapter['updated_at']))
    : 'Mới cập nhật';

// Track view (integrate với view system)
if (class_exists('NetTruyen_View_Tracker')) {
    NetTruyen_View_Tracker::track_chapter_view($post_id, $chapter_slug);
}
?>

<div id="chapter_reader" class="content background-black">
    <div class="div_middle">
        <!-- Alert Notice -->
        <div class="alert-note">
            <p class="text-center">
                <strong>Mẹo:</strong> Sử dụng phím mũi tên ← → để chuyển chapter nhanh.
                Nhấn F11 để đọc toàn màn hình.
            </p>
        </div>

        <div class="main_content">
            <div id="chapter_content">
                <div class="chapter_content_div">
                    <div class="box">
                        <!-- Breadcrumb Top -->
                        <div id="path" class="path-top">
                            <ol class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
                                <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <a itemprop="item" href="<?php echo home_url(); ?>">
                                        <span itemprop="name"><i class="fa fa-home"></i> Trang Chủ</span>
                                    </a>
                                    <meta itemprop="position" content="1">
                                </li>
                                <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <a itemprop="item" href="<?php the_permalink(); ?>">
                                        <span itemprop="name">
                                            <?php echo esc_html($comic_title); ?>
                                        </span>
                                    </a>
                                    <meta itemprop="position" content="2">
                                </li>
                                <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <span itemprop="name">Chương
                                        <?php echo esc_html($chapter_name); ?>
                                    </span>
                                    <meta itemprop="position" content="3">
                                </li>
                            </ol>
                        </div>

                        <!-- Chapter Header -->
                        <div class="chapter-header">
                            <h1 class="detail-title txt-primary">
                                <a href="<?php the_permalink(); ?>">
                                    <?php echo esc_html($comic_title); ?>
                                </a> -
                                Chương
                                <?php echo esc_html($chapter_name); ?>
                            </h1>
                            <time datetime="<?php echo date('c'); ?>">
                                (Cập nhật lúc:
                                <?php echo esc_html($chapter_updated); ?>)
                            </time>
                        </div>

                        <!-- Chapter Controls Top -->
                        <div class="chapter-control">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <em>Sử dụng mũi tên trái (←) hoặc phải (→) để chuyển chapter</em>
                            </div>

                            <div class="chapter-navigation">
                                <?php if ($prev_url): ?>
                                <a class="btn btn-info go-btn prev text-white" href="<?php echo esc_url($prev_url); ?>">
                                    <i class="fa fa-arrow-left"></i> Chap trước
                                </a>
                                <?php else: ?>
                                <button class="btn btn-secondary go-btn prev" disabled>
                                    <i class="fa fa-arrow-left"></i> Chap trước
                                </button>
                                <?php endif; ?>

                                <span class="chapter-info">
                                    Chương
                                    <?php echo $current_position; ?> /
                                    <?php echo $total_chapters; ?>
                                </span>

                                <?php if ($next_url): ?>
                                <a class="btn btn-info go-btn next text-white" href="<?php echo esc_url($next_url); ?>">
                                    Chap sau <i class="fa fa-arrow-right"></i>
                                </a>
                                <?php else: ?>
                                <button class="btn btn-secondary go-btn next" disabled>
                                    Chap sau <i class="fa fa-arrow-right"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Chapter Images -->
                    <div class="chapter_content">
                        <?php if (!empty($chapter_images)): ?>
                        <div class="chapter-images-container">
                            <?php foreach ($chapter_images as $index => $image_url): ?>
                            <div id="page_<?php echo $index; ?>" class="page-chapter">
                                <img class="lazy chapter-image"
                                    src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 1200'%3E%3Crect fill='%23222' width='800' height='1200'/%3E%3Ctext x='50%25' y='50%25' fill='%23666' font-size='24' text-anchor='middle' dominant-baseline='middle'%3EĐang tải...%3C/text%3E%3C/svg%3E"
                                    data-src="<?php echo esc_url($image_url); ?>"
                                    alt="<?php echo esc_attr($comic_title . ' Chương ' . $chapter_name . ' - Trang ' . ($index + 1)); ?>"
                                    loading="lazy">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="no-images-message">
                            <p><i class="fa fa-exclamation-triangle"></i> Không tìm thấy ảnh cho chapter này.</p>
                            <a href="<?php the_permalink(); ?>" class="btn btn-primary">Quay về trang truyện</a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Chapter Controls Bottom -->
                    <div class="box bottom-chap">
                        <div class="chapter-control">
                            <div class="chapter-navigation">
                                <?php if ($prev_url): ?>
                                <a class="btn btn-info go-btn prev text-white" href="<?php echo esc_url($prev_url); ?>">
                                    <i class="fa fa-arrow-left"></i> Chap trước
                                </a>
                                <?php endif; ?>

                                <?php if ($next_url): ?>
                                <a class="btn btn-info go-btn next text-white" href="<?php echo esc_url($next_url); ?>">
                                    Chap sau <i class="fa fa-arrow-right"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Breadcrumb Bottom -->
                        <ol class="breadcrumb">
                            <li>
                                <a href="<?php echo home_url(); ?>">
                                    <i class="fa fa-home"></i> Trang Chủ
                                </a>
                            </li>
                            <li>
                                <a href="<?php the_permalink(); ?>">
                                    <?php echo esc_html($comic_title); ?>
                                </a>
                            </li>
                            <li>Chương
                                <?php echo esc_html($chapter_name); ?>
                            </li>
                        </ol>
                    </div>

                    <!-- Comment Placeholder -->
                    <div class="comment-container box" id="comment_section">
                        <span class="story-detail-title">
                            <i class="fa fa-comments"></i> Bình Luận
                        </span>
                        <div class="notify-fanpage">
                            Vào <a rel="nofollow" href="https://www.facebook.com/truyenqqq" target="_blank">Fanpage</a>
                            like và theo dõi để ủng hộ TruyenQQ nhé.
                        </div>
                        <div class="comments-placeholder">
                            <p>Chức năng bình luận sẽ được cập nhật sớm.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Controls (Sticky) -->
    <div class="chapter-control-fixed" id="chapter_control_sticky">
        <a href="<?php echo home_url(); ?>" class="control-btn home-btn" title="Trang chủ">
            <i class="fa fa-home"></i>
        </a>

        <?php if ($prev_url): ?>
        <a href="<?php echo esc_url($prev_url); ?>" class="control-btn prev-btn" title="Chap trước">
            <i class="fa fa-chevron-left"></i>
        </a>
        <?php endif; ?>

        <button class="control-btn chapter-list-btn" onclick="toggleChapterList()" title="Danh sách chương">
            <!-- <i class="fa fa-list"></i> -->
            <span>
                <?php echo $current_position; ?>/
                <?php echo $total_chapters; ?>
            </span>
        </button>

        <?php if ($next_url): ?>
        <a href="<?php echo esc_url($next_url); ?>" class="control-btn next-btn" title="Chap sau">
            <i class="fa fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>

    <!-- Chapter List Popup -->
    <div class="chapter-list-popup" id="chapter_list_popup">
        <div class="popup-overlay" onclick="toggleChapterList()"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3><i class="fa fa-list"></i> Danh sách chương</h3>
                <button class="close-btn" onclick="toggleChapterList()">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="popup-body">
                <div class="chapter-list-grid">
                    <?php foreach ($chapters as $index => $chap): ?>
                    <?php
                        $chap_url = nettruyen_get_chapter_url($post_id, $chap['slug']);
                        $is_current = $chap['slug'] === $chapter_slug;
                        ?>
                    <a href="<?php echo esc_url($chap_url); ?>"
                        class="chapter-item <?php echo $is_current ? 'current' : ''; ?>">
                        <i class="fa fa-book"></i> Chương
                        <?php echo esc_html($chap['name']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Lazy Load Images with Intersection Observer
document.addEventListener('DOMContentLoaded', function() {
    // ✅ FIXED: querySAll → querySelectorAll
    const lazyImages = document.querySelectorAll('img.lazy');

    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '200px' // Load trước 200px
        });

        lazyImages.forEach(function(img) {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for old browsers
        lazyImages.forEach(function(img) {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
        });
    }
});

// Keyboard Navigation
document.addEventListener('keydown', function(e) {
    // Left Arrow = Previous Chapter
    if (e.key === 'ArrowLeft') {
        const prevBtn = document.querySelector('.go-btn.prev');
        if (prevBtn && !prevBtn.disabled) {
            window.location.href = prevBtn.href;
        }
    }

    // Right Arrow = Next Chapter
    if (e.key === 'ArrowRight') {
        const nextBtn = document.querySelector('.go-btn.next');
        if (nextBtn && !nextBtn.disabled) {
            window.location.href = nextBtn.href;
        }
    }
});

// Toggle Chapter List Popup
function toggleChapterList() {
    const popup = document.getElementById('chapter_list_popup');
    popup.classList.toggle('active');
    document.body.classList.toggle('popup-open');
}

// Close popup when clicking outside
document.addEventListener('click', function(e) {
    const popup = document.getElementById('chapter_list_popup');
    const listBtn = document.querySelector('.chapter-list-btn');

    if (popup.classList.contains('active') &&
        !popup.querySelector('.popup-content').contains(e.target) &&
        !listBtn.contains(e.target)) {
        toggleChapterList();
    }
});

// Sticky Controls on Scroll
window.addEventListener('scroll', function() {
    const stickyControl = document.getElementById('chapter_control_sticky');
    if (window.scrollY > 300) {
        stickyControl.classList.add('visible');
    } else {
        stickyControl.classList.remove('visible');
    }
});
</script>

<?php get_footer(); ?>