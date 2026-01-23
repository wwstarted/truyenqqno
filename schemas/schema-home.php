<?php
/**
 * Schema for Homepage - Multiple ItemLists
 * 1. Hot Comics (16 truyện hay)
 * 2. Exclusive Comics (16 truyện độc quyền)
 * 3. New Update Comics (42 truyện mới cập nhật)
 * 
 * WebSite & Organization schema already loaded in schema-common.php
 */

// ============================================
// 1. HOT COMICS - Truyện Hay (16 items)
// ============================================
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
    $hot_comics = get_posts(array(
        'post_type' => 'nettruyen_comic',
        'post_status' => 'publish',
        'posts_per_page' => 16,
        'orderby' => 'date',
        'order' => 'DESC'
    ));
} else {
    $hot_comics = get_posts(array(
        'post_type' => 'nettruyen_comic',
        'post_status' => 'publish',
        'post__in' => $hot_comics_ids,
        'orderby' => 'post__in',
        'posts_per_page' => 16
    ));
}

$hot_items = array();
foreach ($hot_comics as $index => $comic) {
    $hot_items[] = array(
        '@type' => 'ListItem',
        'position' => $index + 1,
        'url' => get_permalink($comic->ID)
    );
}

// ============================================
// 2. EXCLUSIVE COMICS - Độc Quyền (16 items)
// ============================================
$exclusive_comics = get_posts(array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => 16,
    'orderby' => 'rand',
    'order' => 'DESC'
));

$exclusive_items = array();
foreach ($exclusive_comics as $index => $comic) {
    $exclusive_items[] = array(
        '@type' => 'ListItem',
        'position' => $index + 1,
        'url' => get_permalink($comic->ID)
    );
}

// ============================================
// 3. NEW UPDATE COMICS - Mới Cập Nhật (42 items)
// ============================================
$new_comics = get_posts(array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => 42,
    'orderby' => 'modified',
    'order' => 'DESC'
));

$new_items = array();
foreach ($new_comics as $index => $comic) {
    $new_items[] = array(
        '@type' => 'ListItem',
        'position' => $index + 1,
        'url' => get_permalink($comic->ID)
    );
}
?>

<?php if (!empty($hot_items)): ?>
    <!-- Schema: Truyện Hay -->
    <script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "ItemList",
        "name": "Truyện Hay",
        "description": "16 bộ truyện tranh hot nhất được yêu thích nhất tại TruyenQQ",
        "numberOfItems": <?php echo count($hot_items); ?>,
        "itemListElement": <?php echo json_encode($hot_items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
    }
    </script>
<?php endif; ?>

<?php if (!empty($exclusive_items)): ?>
    <!-- Schema: Độc Quyền Truyện QQ -->
    <script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "ItemList",
        "name": "Độc Quyền Truyện QQ",
        "description": "16 bộ truyện tranh độc quyền tại TruyenQQ",
        "numberOfItems": <?php echo count($exclusive_items); ?>,
        "itemListElement": <?php echo json_encode($exclusive_items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
    }
    </script>
<?php endif; ?>

<?php if (!empty($new_items)): ?>
    <!-- Schema: Truyện Mới Cập Nhật -->
    <script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "ItemList",
        "name": "Truyện Mới Cập Nhật",
        "description": "42 bộ truyện tranh mới cập nhật gần đây nhất tại TruyenQQ",
        "numberOfItems": <?php echo count($new_items); ?>,
        "itemListElement": <?php echo json_encode($new_items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
    }
    </script>
<?php endif; ?>