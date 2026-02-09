<?php
/**
 * Schema for Genre/Taxonomy Page
 * ItemList of comics in specific genre with pagination support
 * 
 * WebSite & Organization schema already loaded in schema-common.php
 */

$current_genre = get_queried_object();
$genre_slug = $current_genre->slug;
$genre_name = $current_genre->name;
$genre_description = $current_genre->description;

// Get current page
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$posts_per_page = 42;

// Get filter parameters
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$country = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
$sort = isset($_GET['sort']) ? absint($_GET['sort']) : 2;

// Build query args (same as in template)
$sort_options = array(
    0 => array('orderby' => 'date', 'order' => 'DESC'),
    1 => array('orderby' => 'date', 'order' => 'ASC'),
    2 => array('orderby' => 'modified', 'order' => 'DESC'),
    3 => array('orderby' => 'modified', 'order' => 'ASC'),
    4 => array('orderby' => 'meta_value_num', 'order' => 'DESC'),
    5 => array('orderby' => 'meta_value_num', 'order' => 'ASC'),
);

$sort_config = isset($sort_options[$sort]) ? $sort_options[$sort] : $sort_options[2];

$args = array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'tax_query' => array(
        array(
            'taxonomy' => 'nettruyen_genre',
            'field' => 'slug',
            'terms' => $genre_slug
        )
    ),
    'orderby' => $sort_config['orderby'],
    'order' => $sort_config['order']
);

// Apply filters
if ($status !== '') {
    $args['meta_query'][] = array(
        'key' => '_nettruyen_status',
        'value' => $status,
        'compare' => '='
    );
}

if ($country !== '') {
    $args['tax_query'][] = array(
        'taxonomy' => 'nettruyen_country',
        'field' => 'slug',
        'terms' => $country
    );
}

if ($sort == 4 || $sort == 5) {
    $args['meta_key'] = '_nettruyen_view_count';
}

// Get comics
$genre_comics = get_posts($args);

// Build ItemList
$comic_items = array();
foreach ($genre_comics as $index => $comic) {
    $comic_items[] = array(
        '@type' => 'ListItem',
        'position' => $index + 1,
        'url' => get_permalink($comic->ID)
    );
}

// Build schema name based on filters
$schema_name = 'Truyện ' . $genre_name;
if ($status) {
    $status_labels = array(
        'ongoing' => 'Đang tiến hành',
        'completed' => 'Hoàn thành',
        'coming_soon' => 'Sắp ra mắt'
    );
    $schema_name .= ' - ' . ($status_labels[$status] ?? $status);
}
if ($country) {
    $schema_name .= ' - ' . $country;
}

// Build description
$schema_description = !empty($genre_description)
    ? wp_strip_all_tags($genre_description)
    : 'Danh sách truyện tranh thể loại ' . $genre_name . ' tại TruyenQQ';

if ($paged > 1) {
    $schema_description .= ' - Trang ' . $paged;
}
?>

<?php if (!empty($comic_items)): ?>
<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "ItemList",
    "name": "<?php echo esc_js($schema_name); ?>",
    "description": "<?php echo esc_js($schema_description); ?>",
    "numberOfItems": <?php echo count($comic_items); ?>,
    "itemListElement": <?php echo json_encode($comic_items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
}
</script>
<?php endif; ?>

<?php
// CollectionPage schema for better taxonomy understanding
?>
<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "CollectionPage",
    "name": "<?php echo esc_js($genre_name); ?>",
    "description": "<?php echo esc_js($schema_description); ?>",
    "url": "<?php echo esc_url(get_term_link($current_genre)); ?>",
    "isPartOf": {
        "@type": "WebSite",
        "name": "TruyenQQ",
        "url": "<?php echo esc_url(home_url()); ?>"
    }
}
</script>

<?php if (!empty($genre_description)): ?>
<!-- Genre/Category schema -->
<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "Thing",
    "@id": "<?php echo esc_url(get_term_link($current_genre)); ?>",
    "name": "<?php echo esc_js($genre_name); ?>",
    "description": "<?php echo esc_js(wp_strip_all_tags($genre_description)); ?>",
    "url": "<?php echo esc_url(get_term_link($current_genre)); ?>"
}
</script>
<?php endif; ?>