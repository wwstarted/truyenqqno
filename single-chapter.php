<?php
/**
 * Template Name: Single Chapter Reading Page - SEO OPTIMIZED
 * Template for reading comic chapters
 * 
 * @package TruyenQQ
 * @version 1.2.0 - SEO OPTIMIZED


 */

$chapter_slug = get_query_var('chapter');
$comic_slug = get_query_var('nettruyen_comic');

 
$has_chapter = isset($chapter_slug) && strlen($chapter_slug) > 0;
$has_comic = isset($comic_slug) && strlen($comic_slug) > 0;

if (!$has_chapter || !$has_comic) {
    wp_die('Invalid chapter URL - Missing chapter or comic slug');
}

$post_id = get_the_ID();
$comic_title = get_the_title();
$thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);

$chapters_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
$manifest = json_decode($chapters_json, true);
$chapters = isset($manifest['chapters']) ? $manifest['chapters'] : array();

$current_chapter = null;
$current_index = -1;

foreach ($chapters as $index => $chapter) {
    if ($chapter['slug'] == $chapter_slug) {
        $current_chapter = $chapter;
        $current_index = $index;
        break;
    }
}

if (!$current_chapter) {
    wp_die('Chapter not found - Chapter slug: ' . esc_html($chapter_slug));
}

$image_domain = isset($current_chapter['image_domain']) ? $current_chapter['image_domain'] : '';
$image_path = isset($current_chapter['image_path']) ? $current_chapter['image_path'] : '';
$image_files = isset($current_chapter['image_files']) ? $current_chapter['image_files'] : array();

$chapter_images = array();
if (!empty($image_domain) && !empty($image_path) && !empty($image_files)) {
    foreach ($image_files as $filename) {
        $chapter_images[] = trailingslashit($image_domain) . trailingslashit($image_path) . $filename;
    }
}

$nav = nettruyen_get_chapter_navigation($post_id, $chapter_slug);
$prev_url = $nav['prev'];
$next_url = $nav['next'];
$current_position = $current_index + 1;
$total_chapters = count($chapters);

$chapter_name = isset($current_chapter['name']) ? $current_chapter['name'] : $chapter_slug;
$chapter_updated = isset($current_chapter['updated_at']) && !empty($current_chapter['updated_at'])
    ? date('H:i d/m/Y', strtotime($current_chapter['updated_at']))
    : 'Mới cập nhật';

if (class_exists('NetTruyen_View_Tracker')) {
    NetTruyen_View_Tracker::track_chapter_view($post_id, $chapter_slug);
}

 
 
 
$current_url = home_url("/truyen-tranh/{$comic_slug}-chap-{$chapter_slug}");
$comic_url = get_permalink($post_id);

 
$meta_description = "Đọc truyện tranh {$comic_title} - Chương {$chapter_name} tiếng Việt. Mới nhất, nhanh nhất, không quảng cáo tại TruyenQQ.";

 
$page_title = "{$comic_title} - Chương {$chapter_name} | TruyenQQ";

 
$author_terms = get_the_terms($post_id, 'nettruyen_author');
$author_name = 'Đang Cập Nhật';
if ($author_terms && !is_wp_error($author_terms)) {
    $author_name = $author_terms[0]->name;
}

 
$og_image = !empty($chapter_images) ? $chapter_images[0] : $thumbnail;
if (empty($og_image)) {
    $og_image = get_template_directory_uri() . '/images/default-og.jpg';
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ✅ SEO: Custom Title -->
    <title><?php echo esc_html($page_title); ?></title>

    <!-- ✅ SEO: Meta Description -->
    <meta name="description" content="<?php echo esc_attr($meta_description); ?>">

    <!-- ✅ SEO: Canonical Link -->
    <link rel="canonical" href="<?php echo esc_url($current_url); ?>">

    <!-- ✅ SEO: Prev/Next Navigation -->
    <?php if ($prev_url): ?>
    <link rel="prev" href="<?php echo esc_url($prev_url); ?>">
    <?php endif; ?>
    <?php if ($next_url): ?>
    <link rel="next" href="<?php echo esc_url($next_url); ?>">
    <?php endif; ?>

    <!-- ✅ SEO: Open Graph (Facebook) -->
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?php echo esc_attr($comic_title . ' - Chương ' . $chapter_name); ?>">
    <meta property="og:description" content="<?php echo esc_attr($meta_description); ?>">
    <meta property="og:url" content="<?php echo esc_url($current_url); ?>">
    <meta property="og:image" content="<?php echo esc_url($og_image); ?>">
    <meta property="og:image:width" content="800">
    <meta property="og:image:height" content="1200">
    <meta property="og:site_name" content="TruyenQQ">
    <meta property="og:locale" content="vi_VN">
    <meta property="article:published_time" content="<?php echo get_the_date('c'); ?>">
    <meta property="article:author" content="<?php echo esc_attr($author_name); ?>">

    <!-- ✅ SEO: Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr($comic_title . ' - Chương ' . $chapter_name); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($meta_description); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($og_image); ?>">

    <!-- ✅ Performance: Preload first chapter image -->
    <?php if (!empty($chapter_images)): ?>
    <link rel="preload" as="image" href="<?php echo esc_url($chapter_images[0]); ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php get_header(); ?>

    <!-- ✅ Semantic HTML: Main content wrapper -->
    <main id="main-content" role="main" aria-label="Nội dung chương truyện">
        <!-- ✅ THÊM DATA ATTRIBUTES ĐỂ JS CÓ THỂ ĐỌC -->
        <div id="chapter_reader" class="content background-black" data-comic-id="<?php echo esc_attr($post_id); ?>"
            data-chapter-slug="<?php echo esc_attr($chapter_slug); ?>">
            <div class="div_middle">
                <!-- Alert Notice -->
                <div class="alert-note">
                    <p class="text-center">
                        <strong>Mẹo:</strong> Sử dụng phím mũi tên ← → để chuyển chapter nhanh.
                        Nhấn F11 để đọc toàn màn hình.
                    </p>
                </div>

                <div class="main_content">
                    <!-- ✅ Semantic HTML: Article wrapper -->
                    <article id="chapter_content" itemscope itemtype="http://schema.org/Article">
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
                                            <a itemprop="item" href="<?php echo esc_url($comic_url); ?>">
                                                <span itemprop="name"><?php echo esc_html($comic_title); ?></span>
                                            </a>
                                            <meta itemprop="position" content="2">
                                        </li>
                                        <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                            <span itemprop="name">Chương <?php echo esc_html($chapter_name); ?></span>
                                            <meta itemprop="position" content="3">
                                        </li>
                                    </ol>
                                </div>

                                <!-- Chapter Header -->
                                <header class="chapter-header">
                                    <h1 class="detail-title txt-primary" itemprop="headline">
                                        <a href="<?php echo esc_url($comic_url); ?>">
                                            <?php echo esc_html($comic_title); ?>
                                        </a> -
                                        Chương <?php echo esc_html($chapter_name); ?>
                                    </h1>
                                    <time datetime="<?php echo date('c'); ?>" itemprop="datePublished">
                                        (Cập nhật lúc: <?php echo esc_html($chapter_updated); ?>)
                                    </time>
                                </header>

                                <!-- Chapter Controls Top -->
                                <div class="chapter-control">
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i>
                                        <em>Sử dụng mũi tên trái (←) hoặc phải (→) để chuyển chapter</em>
                                    </div>

                                    <nav class="chapter-navigation" aria-label="Điều hướng chương">
                                        <?php if ($prev_url): ?>
                                        <a class="btn btn-info go-btn prev text-white"
                                            href="<?php echo esc_url($prev_url); ?>" rel="prev">
                                            <i class="fa fa-arrow-left"></i> Chap trước
                                        </a>
                                        <?php else: ?>
                                        <button class="btn btn-secondary go-btn prev" disabled>
                                            <i class="fa fa-arrow-left"></i> Chap trước
                                        </button>
                                        <?php endif; ?>

                                        <span class="chapter-info">
                                            Chương <?php echo $current_position; ?> / <?php echo $total_chapters; ?>
                                        </span>

                                        <?php if ($next_url): ?>
                                        <a class="btn btn-info go-btn next text-white"
                                            href="<?php echo esc_url($next_url); ?>" rel="next">
                                            Chap sau <i class="fa fa-arrow-right"></i>
                                        </a>
                                        <?php else: ?>
                                        <button class="btn btn-secondary go-btn next" disabled>
                                            Chap sau <i class="fa fa-arrow-right"></i>
                                        </button>
                                        <?php endif; ?>
                                    </nav>
                                </div>
                            </div>

                            <!-- Chapter Images -->
                            <div class="chapter_content" itemprop="articleBody">
                                <?php if (!empty($chapter_images)): ?>
                                <div class="chapter-images-container">
                                    <?php foreach ($chapter_images as $index => $image_url): ?>
                                    <div id="page_<?php echo $index; ?>" class="page-chapter">
                                        <img class="lazy chapter-image"
                                            src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 1200'%3E%3Crect fill='%23222' width='800' height='1200'/%3E%3Ctext x='50%25' y='50%25' fill='%23666' font-size='24' text-anchor='middle' dominant-baseline='middle'%3EĐang tải...%3C/text%3E%3C/svg%3E"
                                            data-src="<?php echo esc_url($image_url); ?>"
                                            alt="<?php echo esc_attr($comic_title . ' Chương ' . $chapter_name . ' - Trang ' . ($index + 1)); ?>"
                                            width="800" height="1200" loading="lazy" itemprop="image">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <div class="no-images-message">
                                    <p><i class="fa fa-exclamation-triangle"></i> Không tìm thấy ảnh cho chapter này.
                                    </p>
                                    <a href="<?php echo esc_url($comic_url); ?>" class="btn btn-primary">Quay về trang
                                        truyện</a>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Chapter Controls Bottom -->
                            <div class="box bottom-chap">
                                <div class="chapter-control">
                                    <nav class="chapter-navigation" aria-label="Điều hướng chương">
                                        <?php if ($prev_url): ?>
                                        <a class="btn btn-info go-btn prev text-white"
                                            href="<?php echo esc_url($prev_url); ?>" rel="prev">
                                            <i class="fa fa-arrow-left"></i> Chap trước
                                        </a>
                                        <?php endif; ?>

                                        <?php if ($next_url): ?>
                                        <a class="btn btn-info go-btn next text-white"
                                            href="<?php echo esc_url($next_url); ?>" rel="next">
                                            Chap sau <i class="fa fa-arrow-right"></i>
                                        </a>
                                        <?php endif; ?>
                                    </nav>
                                </div>

                                <!-- Breadcrumb Bottom -->
                                <ol class="breadcrumb">
                                    <li>
                                        <a href="<?php echo home_url(); ?>">
                                            <i class="fa fa-home"></i> Trang Chủ
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo esc_url($comic_url); ?>">
                                            <?php echo esc_html($comic_title); ?>
                                        </a>
                                    </li>
                                    <li>Chương <?php echo esc_html($chapter_name); ?></li>
                                </ol>
                            </div>

                            <!-- Comment Placeholder -->
                            <div class="comment-container box" id="comment_section">
                                <span class="story-detail-title">
                                    <i class="fa fa-comments"></i> Bình Luận
                                </span>
                                <div class="notify-fanpage">
                                    Vào <a rel="nofollow" href="https://www.facebook.com/truyenqqq"
                                        target="_blank">Fanpage</a>
                                    like và theo dõi để ủng hộ TruyenQQ nhé.
                                </div>
                                <div class="comments-placeholder">
                                    <p>Chức năng bình luận sẽ được cập nhật sớm.</p>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <!-- Floating Controls (Sticky) -->
            <div class="chapter-control-fixed" id="chapter_control_sticky">
                <a href="<?php echo home_url(); ?>" class="control-btn home-btn" title="Trang chủ">
                    <i class="fa fa-home"></i>
                </a>

                <?php if ($prev_url): ?>
                <a href="<?php echo esc_url($prev_url); ?>" class="control-btn prev-btn" title="Chap trước" rel="prev">
                    <i class="fa fa-chevron-left"></i>
                </a>
                <?php endif; ?>

                <button class="control-btn chapter-list-btn" onclick="toggleChapterList()" title="Danh sách chương">
                    <span><?php echo $current_position; ?>/<?php echo $total_chapters; ?></span>
                </button>

                <?php if ($next_url): ?>
                <a href="<?php echo esc_url($next_url); ?>" class="control-btn next-btn" title="Chap sau" rel="next">
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
                            $is_current = $chap['slug'] == $chapter_slug;
                        ?>
                            <a href="<?php echo esc_url($chap_url); ?>"
                                class="chapter-item <?php echo $is_current ? 'current' : ''; ?>"
                                <?php echo $is_current ? 'aria-current="page"' : ''; ?>>
                                <i class="fa fa-book"></i> Chương <?php echo esc_html($chap['name']); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
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
                rootMargin: '200px'
            });

            lazyImages.forEach(function(img) {
                imageObserver.observe(img);
            });
        } else {
            lazyImages.forEach(function(img) {
                img.src = img.dataset.src;
                img.classList.remove('lazy');
            });
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            const prevBtn = document.querySelector('.go-btn.prev');
            if (prevBtn && !prevBtn.disabled) {
                window.location.href = prevBtn.href;
            }
        }

        if (e.key === 'ArrowRight') {
            const nextBtn = document.querySelector('.go-btn.next');
            if (nextBtn && !nextBtn.disabled) {
                window.location.href = nextBtn.href;
            }
        }
    });

    function toggleChapterList() {
        const popup = document.getElementById('chapter_list_popup');
        popup.classList.toggle('active');
        document.body.classList.toggle('popup-open');
    }

    document.addEventListener('click', function(e) {
        const popup = document.getElementById('chapter_list_popup');
        const listBtn = document.querySelector('.chapter-list-btn');

        if (popup.classList.contains('active') &&
            !popup.querySelector('.popup-content').contains(e.target) &&
            !listBtn.contains(e.target)) {
            toggleChapterList();
        }
    });

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
</body>

</html>