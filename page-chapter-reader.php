<?php
/**
 * ===================================
 * EXAMPLE 1: Single Post Template
 * File: single-nettruyen_comic.php
 * ===================================
 * 
 * Dùng khi chapter reader nằm trong cùng single post
 */

// Get chapter từ URL query (?chapter=1) hoặc query_var
$current_chapter_slug = get_query_var('chapter', '');

// Nếu không có chapter trong URL, show comic detail
if (empty($current_chapter_slug)) {
    // Show comic info, chapter list, etc.
    get_template_part('template-parts/comic', 'detail');
} else {
    // Show chapter reader
    $post_id = get_the_ID();
    $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
    $manifest = json_decode($manifest_json, true);

    // Find current chapter
    $current_chapter = null;
    foreach ($manifest['chapters'] as $chapter) {
        if ($chapter['slug'] === $current_chapter_slug) {
            $current_chapter = $chapter;
            break;
        }
    }

    if (!$current_chapter) {
        echo '<p>Chapter not found!</p>';
        return;
    }

    ?>
<div class="chapter-reader" data-comic-id="<?php echo esc_attr($post_id); ?>"
    data-chapter-slug="<?php echo esc_attr($current_chapter_slug); ?>">

    <h1>
        <?php the_title(); ?> - Chương
        <?php echo esc_html($current_chapter['name']); ?>
    </h1>

    <div class="chapter-images">
        <?php foreach ($current_chapter['image_files'] as $image_file): ?>
        <img src="<?php echo esc_url($current_chapter['image_domain'] . '/' . $current_chapter['image_path'] . '/' . $image_file); ?>"
            alt="Page" loading="lazy">
        <?php endforeach; ?>
    </div>

    <!-- Chapter navigation -->
    <div class="chapter-nav">
        <a href="?chapter=prev">← Chương trước</a>
        <a href="<?php the_permalink(); ?>">Danh sách chương</a>
        <a href="?chapter=next">Chương sau →</a>
    </div>
</div>
<?php
}

?>

<?php
/**
 * ===================================
 * EXAMPLE 2: Custom Page Template
 * File: page-chapter-reader.php
 * ===================================
 * 
 * Template Name: Chapter Reader
 * Dùng khi tạo page riêng cho reader
 */

/*
Template Name: Chapter Reader
*/

get_header();

// Get params từ URL: /reader?comic=7&chapter=1
$comic_id = isset($_GET['comic']) ? absint($_GET['comic']) : 0;
$chapter_slug = isset($_GET['chapter']) ? sanitize_text_field($_GET['chapter']) : '';

if (!$comic_id || !$chapter_slug) {
    echo '<p>Invalid comic or chapter!</p>';
    get_footer();
    return;
}

// Get manga data
$manifest_json = get_post_meta($comic_id, '_nettruyen_chapter_manifest_json', true);
$manifest = json_decode($manifest_json, true);

// Find chapter
$current_chapter = null;
foreach ($manifest['chapters'] as $chapter) {
    if ($chapter['slug'] === $chapter_slug) {
        $current_chapter = $chapter;
        break;
    }
}

if (!$current_chapter) {
    echo '<p>Chapter not found!</p>';
    get_footer();
    return;
}

?>

<div class="chapter-reader" data-comic-id="<?php echo esc_attr($comic_id); ?>"
    data-chapter-slug="<?php echo esc_attr($chapter_slug); ?>">

    <h1>
        <?php echo get_the_title($comic_id); ?> - Chương
        <?php echo esc_html($current_chapter['name']); ?>
    </h1>

    <div class="chapter-images">
        <?php foreach ($current_chapter['image_files'] as $image_file): ?>
        <img src="<?php echo esc_url($current_chapter['image_domain'] . '/' . $current_chapter['image_path'] . '/' . $image_file); ?>"
            alt="Page" loading="lazy">
        <?php endforeach; ?>
    </div>
</div>

<?php get_footer(); ?>


<?php
/**
 * ===================================
 * EXAMPLE 3: AJAX Chapter Loader
 * File: chapter-loader-ajax.php
 * ===================================
 * 
 * Dùng khi load chapter qua AJAX (single page app)
 */

?>
<div id="comic-viewer" data-comic-id="7">
    <div id="chapter-container"></div>

    <select id="chapter-selector">
        <option value="1">Chương 1</option>
        <option value="2">Chương 2</option>
        <option value="3">Chương 3</option>
    </select>
</div>

<script>
document.getElementById('chapter-selector').addEventListener('change', function() {
    const comicId = document.getElementById('comic-viewer').dataset.comicId;
    const chapterSlug = this.value;

    // Track view via JavaScript
    if (window.NettruyenViewTracker) {
        window.NettruyenViewTracker.track(parseInt(comicId), chapterSlug);
    }

    // Load chapter images via your custom logic
    loadChapterImages(comicId, chapterSlug);
});
</script>


<?php
/**
 * ===================================
 * DISPLAY VIEW COUNT ON COMIC CARD
 * ===================================
 */

require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

$stats = NetTruyen_View_Tracker::get_stats(get_the_ID());

if ($stats) {
    echo '<div class="view-count">';
    echo '<i class="icon-eye"></i> ';
    echo number_format($stats['total_display_views']) . ' lượt xem';
    echo '</div>';
}
?>


<?php
/**
 * ===================================
 * SHOW STATS ON COMIC DETAIL PAGE
 * ===================================
 */

require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

$stats = NetTruyen_View_Tracker::get_stats(get_the_ID());

if ($stats) {
    ?>
<div class="comic-stats">
    <div class="stat-item">
        <strong>
            <?php echo number_format($stats['total_display_views']); ?>
        </strong>
        <span>Tổng lượt xem</span>
    </div>
    <div class="stat-item">
        <strong>
            <?php echo number_format($stats['daily_views']); ?>
        </strong>
        <span>Hôm nay</span>
    </div>
    <div class="stat-item">
        <strong>
            <?php echo number_format($stats['weekly_views']); ?>
        </strong>
        <span>7 ngày</span>
    </div>
    <div class="stat-item">
        <strong>
            <?php echo number_format($stats['monthly_views']); ?>
        </strong>
        <span>30 ngày</span>
    </div>
</div>
<?php
}
?>


<?php
/**
 * ===================================
 * MANUAL TRACK (PHP-based fallback)
 * ===================================
 * 
 * Nếu JavaScript bị disable, track trực tiếp bằng PHP
 */

// Trong chapter reader template
require_once get_template_directory() . '/inc/class-nettruyen-view-tracker.php';

$post_id = get_the_ID();
$chapter_slug = get_query_var('chapter');

if ($post_id && $chapter_slug) {
    NetTruyen_View_Tracker::track_chapter_view($post_id, $chapter_slug);
}
?>