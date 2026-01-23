<?php
/**
 * Schema for Single Comic Page
 * Comic/Book + ItemList (chapters) + BreadcrumbList
 * 
 * WebSite & Organization schema already loaded in schema-common.php
 */

global $post;
$post_id = $post->ID;

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

// Get genres
$genre_terms = get_the_terms($post_id, 'nettruyen_genre');
$genres = array();
if ($genre_terms && !is_wp_error($genre_terms)) {
    foreach ($genre_terms as $genre) {
        $genres[] = $genre->name;
    }
}

// Get status
$status = get_post_meta($post_id, '_nettruyen_status', true);
$status_text = '';
switch ($status) {
    case 'ongoing':
        $status_text = 'Đang tiến hành';
        break;
    case 'completed':
        $status_text = 'Hoàn thành';
        break;
    case 'coming_soon':
        $status_text = 'Sắp ra mắt';
        break;
    default:
        $status_text = 'Đang Cập Nhật';
}

// Get other data
$other_names = get_post_meta($post_id, '_nettruyen_other_name', true);
$description = get_post_meta($post_id, '_nettruyen_short_description', true);
if (empty($description)) {
    $description = wp_strip_all_tags(get_the_excerpt());
}

// Get dates
$published = get_the_date('c');
$modified = get_the_modified_date('c');

// Get stats
global $wpdb;
$stats_table = $wpdb->prefix . 'nettruyen_view_stats';
$view_stats = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT total_display_views FROM {$stats_table} WHERE post_id = %d",
        $post_id
    )
);
$view_count = $view_stats ? $view_stats->total_display_views : 0;
$follow_count = get_post_meta($post_id, '_nettruyen_follow_count', true) ?: 0;

// Get chapters for ItemList
$chapters_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
$chapters = array();

if (!empty($chapters_json)) {
    $manifest = json_decode($chapters_json, true);
    if (isset($manifest['chapters']) && is_array($manifest['chapters'])) {
        $chapters = $manifest['chapters'];
    }
}

// Fallback to old format
if (empty($chapters)) {
    $chapters_data = get_post_meta($post_id, '_nettruyen_chapter_manifest', true);
    if (!empty($chapters_data)) {
        $unserialized = maybe_unserialize($chapters_data);
        if (is_array($unserialized)) {
            $chapters = $unserialized;
        }
    }
}

// Reverse to latest first
if (!empty($chapters)) {
    $chapters = array_reverse($chapters);
}

// Build chapter ItemList
$comic_slug = get_post_field('post_name', $post_id);
$chapter_items = array();
foreach ($chapters as $index => $chapter) {
    if (!is_array($chapter) || empty($chapter['slug'])) {
        continue;
    }

    $chapter_url = home_url("/truyen-tranh/{$comic_slug}-chap-{$chapter['slug']}.html");
    $chapter_items[] = array(
        '@type' => 'ListItem',
        'position' => $index + 1,
        'url' => $chapter_url
    );
}

// Calculate aggregate rating (mock data - you can replace with real rating system)
$rating_value = 4.5;
$rating_count = max(10, floor($view_count / 100));
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
                "@id": "<?php echo esc_url(get_permalink()); ?>",
                "name": "<?php echo esc_js(get_the_title()); ?>"
            }
        }
    ]
}
</script>

<!-- Comic/Book Schema -->
<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "Book",
    "name": "<?php echo esc_js(get_the_title()); ?>",
    <?php if ($other_names): ?> "alternateName": "<?php echo esc_js($other_names); ?>",
    <?php endif; ?> "url": "<?php echo esc_url(get_permalink()); ?>",
    "image": "<?php echo esc_url($thumbnail); ?>",
    "description": "<?php echo esc_js($description); ?>",
    "author": {
        "@type": "Person",
        "name": "<?php echo esc_js($author_name); ?>"
    },
    "publisher": {
        "@type": "Organization",
        "name": "TruyenQQ",
        "logo": {
            "@type": "ImageObject",
            "url": "<?php echo esc_url(get_template_directory_uri() . '/images/logo.png'); ?>"
        }
    },
    "datePublished": "<?php echo esc_attr($published); ?>",
    "dateModified": "<?php echo esc_attr($modified); ?>",
    <?php if (!empty($genres)): ?> "genre": <?php echo json_encode($genres, JSON_UNESCAPED_UNICODE); ?>,
    <?php endif; ?> "bookFormat": "http://schema.org/GraphicNovel",
    "inLanguage": "vi",
    "numberOfPages": <?php echo count($chapters); ?>,
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": <?php echo $rating_value; ?>,
        "ratingCount": <?php echo $rating_count; ?>,
        "bestRating": 5,
        "worstRating": 1
    },
    "interactionStatistic": [{
            "@type": "InteractionCounter",
            "interactionType": "http://schema.org/ReadAction",
            "userInteractionCount": <?php echo $view_count; ?>
        },
        {
            "@type": "InteractionCounter",
            "interactionType": "http://schema.org/FollowAction",
            "userInteractionCount": <?php echo $follow_count; ?>
        }
    ]
}
</script>

<?php if (!empty($chapter_items)): ?>
    <!-- Chapters ItemList Schema -->
    <script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "ItemList",
        "name": "Danh sách chương - <?php echo esc_js(get_the_title()); ?>",
        "description": "Tất cả các chương của truyện <?php echo esc_js(get_the_title()); ?>",
        "numberOfItems": <?php echo count($chapter_items); ?>,
        "itemListElement": <?php echo json_encode($chapter_items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
    }
    </script>
<?php endif; ?>

<!-- Article Schema (for SEO) -->
<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "Article",
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "<?php echo esc_url(get_permalink()); ?>"
    },
    "headline": "<?php echo esc_js(get_the_title()); ?>",
    "image": {
        "@type": "ImageObject",
        "url": "<?php echo esc_url($thumbnail); ?>",
        "width": 190,
        "height": 247
    },
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
    "description": "<?php echo esc_js($description); ?>"
}
</script>