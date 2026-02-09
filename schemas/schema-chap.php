<?php
/**
 * Schema for Chapter Reading Page
 * Article/Chapter + ImageObject + BreadcrumbList
 * 
 * WebSite & Organization schema already loaded in schema-common.php
 */

global $post;

// Get chapter info from query vars
$chapter_slug = get_query_var('chapter');
$comic_slug = get_query_var('nettruyen_comic');

if (empty($chapter_slug) || empty($comic_slug)) {
    return;
}

$post_id = $post->ID;
$comic_title = get_the_title();
$comic_url = get_permalink();

// Get thumbnail
$thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);
if (empty($thumbnail)) {
    $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
}
if (empty($thumbnail)) {
    $thumbnail = home_url('/files/images/no_image.jpg');
}

// Get author
$author_terms = get_the_terms($post_id, 'nettruyen_author');
$author_name = 'Đang Cập Nhật';
if ($author_terms && !is_wp_error($author_terms)) {
    $author_name = $author_terms[0]->name;
}

// Get chapters manifest
$chapters_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
$manifest = json_decode($chapters_json, true);
$chapters = isset($manifest['chapters']) ? $manifest['chapters'] : array();

// Find current chapter
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
    return;
}

// Get chapter details
$chapter_name = isset($current_chapter['name']) ? $current_chapter['name'] : $chapter_slug;
$chapter_updated = isset($current_chapter['updated_at']) && !empty($current_chapter['updated_at'])
    ? $current_chapter['updated_at']
    : get_the_modified_time('U');

// Get chapter images
$image_domain = isset($current_chapter['image_domain']) ? $current_chapter['image_domain'] : '';
$image_path = isset($current_chapter['image_path']) ? $current_chapter['image_path'] : '';
$image_files = isset($current_chapter['image_files']) ? $current_chapter['image_files'] : array();

$chapter_images = array();
if (!empty($image_domain) && !empty($image_path) && !empty($image_files)) {
    foreach ($image_files as $filename) {
        $chapter_images[] = trailingslashit($image_domain) . trailingslashit($image_path) . $filename;
    }
}

// Build current chapter URL
$current_url = home_url("/truyen-tranh/{$comic_slug}-chap-{$chapter_slug}.html");

// Get navigation
$nav = nettruyen_get_chapter_navigation($post_id, $chapter_slug);
$prev_url = $nav['prev'];
$next_url = $nav['next'];

// Calculate position
$current_position = $current_index + 1;
$total_chapters = count($chapters);

// Dates
$published = date('c', strtotime($chapter_updated));
$modified = $published; // Chapters usually don't get modified after upload

// Description
$description = "Đọc truyện tranh {$comic_title} Chương {$chapter_name} tiếng việt. Mới nhất nhanh nhất tại TruyenQQ";
?>

<!-- BreadcrumbList Schema -->
<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [{
            "@type": "ListItem",
            "position": 1,
            "item": {
                "@id": "<?php echo esc_url(home_url()); ?>",
                "name": "Trang Chủ"
            }
        },
        {
            "@type": "ListItem",
            "position": 2,
            "item": {
                "@id": "<?php echo esc_url($comic_url); ?>",
                "name": "<?php echo esc_js($comic_title); ?>"
            }
        },
        {
            "@type": "ListItem",
            "position": 3,
            "item": {
                "@id": "<?php echo esc_url($current_url); ?>",
                "name": "Chương <?php echo esc_js($chapter_name); ?>"
            }
        }
    ]
}
</script>

<!-- Article Schema (Main Content) -->
<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "Article",
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "<?php echo esc_url($current_url); ?>"
    },
    "headline": "<?php echo esc_js($comic_title . ' - Chương ' . $chapter_name); ?>",
    "description": "<?php echo esc_js($description); ?>",
    "image": <?php echo !empty($chapter_images) ? json_encode(array_slice($chapter_images, 0, 3), JSON_UNESCAPED_SLASHES) : json_encode(array($thumbnail), JSON_UNESCAPED_SLASHES); ?>,
    "datePublished": "<?php echo esc_attr($published); ?>",
    "dateModified": "<?php echo esc_attr($modified); ?>",
    "author": {
        "@type": "Person",
        "name": "<?php echo esc_js($author_name); ?>"
    },
    "publisher": {
        "@type": "Organization",
        "name": "TruyenQQ",
        "logo": {
            "@type": "ImageObject",
            "url": "<?php echo esc_url(get_template_directory_uri() . '/images/logo.png'); ?>",
            "width": 237,
            "height": 54
        }
    },
    "isPartOf": {
        "@type": "Book",
        "@id": "<?php echo esc_url($comic_url); ?>",
        "name": "<?php echo esc_js($comic_title); ?>"
    },
    "position": <?php echo $current_position; ?>,
    "about": {
        "@type": "Thing",
        "name": "<?php echo esc_js($comic_title); ?>",
        "url": "<?php echo esc_url($comic_url); ?>"
    }
}
</script>

<?php if (!empty($chapter_images)): ?>
<!-- ImageGallery Schema -->
<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "ImageGallery",
    "name": "<?php echo esc_js($comic_title . ' - Chương ' . $chapter_name); ?>",
    "description": "Tất cả ảnh của chương <?php echo esc_js($chapter_name); ?>",
    "url": "<?php echo esc_url($current_url); ?>",
    "image": <?php echo json_encode($chapter_images, JSON_UNESCAPED_SLASHES); ?>,
    "associatedMedia": [
        <?php foreach ($chapter_images as $index => $image_url): ?> {
            "@type": "ImageObject",
            "contentUrl": "<?php echo esc_url($image_url); ?>",
            "name": "<?php echo esc_js($comic_title . ' Chương ' . $chapter_name . ' - Trang ' . ($index + 1)); ?>",
            "position": <?php echo $index + 1; ?>
        }
        <?php echo ($index < count($chapter_images) - 1) ? ',' : ''; ?>

        <?php endforeach; ?>
    ]
}
</script>
<?php endif; ?>

<!-- Chapter Navigation Schema (For better understanding) -->
<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "Thing",
    "@id": "<?php echo esc_url($current_url); ?>",
    "name": "<?php echo esc_js($comic_title . ' - Chương ' . $chapter_name); ?>",
    "url": "<?php echo esc_url($current_url); ?>",
    "position": <?php echo $current_position; ?>,
    "isPartOf": {
        "@type": "Book",
        "@id": "<?php echo esc_url($comic_url); ?>",
        "name": "<?php echo esc_js($comic_title); ?>"
    }
    <?php if ($prev_url): ?>,
    "previousItem": {
        "@type": "Thing",
        "url": "<?php echo esc_url($prev_url); ?>"
    }
    <?php endif; ?>
    <?php if ($next_url): ?>,
    "nextItem": {
        "@type": "Thing",
        "url": "<?php echo esc_url($next_url); ?>"
    }
    <?php endif; ?>
}
</script>