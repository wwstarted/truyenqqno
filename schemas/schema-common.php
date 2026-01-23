<?php
/**
 * Common Schema - WebSite + Organization
 * Load trên mọi trang
 */
?>
<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "WebSite",
    "url": "<?php echo esc_url(home_url()); ?>",
    "potentialAction": {
        "@type": "SearchAction",
        "target": "<?php echo esc_url(home_url('/tim-kiem.html?q={search_term_string}')); ?>",
        "query-input": "required name=search_term_string"
    }
}
</script>
<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "Organization",
    "name": "TruyenQQ",
    "url": "<?php echo esc_url(home_url()); ?>",
    "logo": {
        "@type": "ImageObject",
        "url": "<?php echo esc_url(get_template_directory_uri() . '/images/logo.png'); ?>",
        "width": 237,
        "height": 54
    },
    "sameAs": [
        "https://www.facebook.com/truyenqqq"
    ]
}
</script>