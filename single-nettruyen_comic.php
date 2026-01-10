<?php
get_header();

$chapter_slug = get_query_var('chapter', '');

if (empty($chapter_slug)) {
    // Show comic detail page
    get_template_part('template-parts/comic', 'detail');
} else {
    // Show chapter reader
    $post_id = get_the_ID();
    $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
    $manifest = json_decode($manifest_json, true);

    // Find chapter
    $current_chapter = null;
    foreach ($manifest['chapters'] as $chapter) {
        if ($chapter['slug'] === $chapter_slug) {
            $current_chapter = $chapter;
            break;
        }
    }

    if (!$current_chapter): ?>
<p>Chapter không tìm thấy!</p>
<?php else: ?>
<!-- DATA ATTRIBUTES ĐỂ JS TỰ TRACK -->
<div class="chapter-reader" data-comic-id="<?php echo esc_attr($post_id); ?>"
    data-chapter-slug="<?php echo esc_attr($chapter_slug); ?>">

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
</div>
<?php endif;
}

get_footer();
?>