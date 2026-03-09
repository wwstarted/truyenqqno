<?php
/**
 * Template Name: Single Comic Info Page
 * Template for displaying comic detail information
 * ✅ SEO OPTIMIZED - FIXED CSS ISSUES
 * 
 * @package TruyenQQ
 * @version 1.1.1 - CSS Fixed
 */

get_header();


$post_id = get_the_ID();
$comic_slug = get_post_field('post_name', $post_id);


$thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);
if (empty($thumbnail)) {
    $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
}
if (empty($thumbnail)) {
    $thumbnail = 'https://via.placeholder.com/190x247?text=No+Image';
}


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

$other_names = get_post_meta($post_id, '_nettruyen_other_name', true);
$description = get_post_meta($post_id, '_nettruyen_short_description', true);
$follow_count = get_post_meta($post_id, '_nettruyen_follow_count', true) ?: 0;


global $wpdb;
$stats_table = $wpdb->prefix . 'nettruyen_view_stats';
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


$authors = get_the_terms($post_id, 'nettruyen_author');
$author_name = 'Đang Cập Nhật';
if ($authors && !is_wp_error($authors)) {
    $author_name = $authors[0]->name;
}


$genres = get_the_terms($post_id, 'nettruyen_genre');


$chapters_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
$chapters = [];

if (!empty($chapters_json)) {
    $manifest = json_decode($chapters_json, true);
    if (isset($manifest['chapters']) && is_array($manifest['chapters'])) {
        $chapters = $manifest['chapters'];
    }
}


if (empty($chapters)) {
    $chapters_data = get_post_meta($post_id, '_nettruyen_chapter_manifest', true);
    if (!empty($chapters_data)) {
        $unserialized = maybe_unserialize($chapters_data);
        if (is_array($unserialized)) {
            $chapters = $unserialized;
        }
    }
}


if (!empty($chapters)) {
    $chapters = array_reverse($chapters);
}


$first_chapter_url = '';
$latest_chapter_url = '';

if (!empty($chapters) && is_array($chapters)) {

    $latest_chapter = $chapters[0];
    if (isset($latest_chapter['slug'])) {
        $latest_chapter_url = home_url("/truyen-tranh/{$comic_slug}-chap-{$latest_chapter['slug']}");
    }


    $first_chapter = end($chapters);
    if (isset($first_chapter['slug'])) {
        $first_chapter_url = home_url("/truyen-tranh/{$comic_slug}-chap-{$first_chapter['slug']}");
    }
}


$follow_count_formatted = number_format($follow_count);
$view_count_formatted = number_format($view_count);


$meta_description = wp_strip_all_tags($description);
if (empty($meta_description)) {
    $meta_description = "Đọc truyện " . get_the_title() . " - " . $author_name . " full mới nhất, cập nhật nhanh nhất tại TruyenQQ. Miễn phí, không quảng cáo.";
}
$meta_description = wp_trim_words($meta_description, 30, '...');


$comic_title = get_the_title();
?>

<!-- ✅ SEO: Meta Tags -->
<link rel="canonical" href="<?php the_permalink(); ?>">
<meta name="description" content="<?php echo esc_attr($meta_description); ?>">

<!-- Open Graph -->
<meta property="og:type" content="book">
<meta property="og:title" content="<?php echo esc_attr($comic_title . ' - ' . $author_name); ?>">
<meta property="og:description" content="<?php echo esc_attr($meta_description); ?>">
<meta property="og:url" content="<?php the_permalink(); ?>">
<meta property="og:image" content="<?php echo esc_url($thumbnail); ?>">
<meta property="og:image:width" content="190">
<meta property="og:image:height" content="247">
<meta property="og:site_name" content="TruyenQQ">
<meta property="og:locale" content="vi_VN">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo esc_attr($comic_title); ?>">
<meta name="twitter:description" content="<?php echo esc_attr($meta_description); ?>">
<meta name="twitter:image" content="<?php echo esc_url($thumbnail); ?>">

<!-- Additional Meta -->
<meta property="book:author" content="<?php echo esc_attr($author_name); ?>">
<meta property="book:release_date" content="<?php echo get_the_date('c'); ?>">
<?php
if ($genres && !is_wp_error($genres)):
    foreach ($genres as $genre): ?>
<meta property="book:tag" content="<?php echo esc_attr($genre->name); ?>">
<?php endforeach;
endif; ?>

<div id="main_homepage">
    <!-- Breadcrumb -->
    <ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="<?php echo home_url(); ?>">
                <span itemprop="name"><i class="fa fa-home"></i> Trang Chủ</span>
            </a>
            <meta itemprop="position" content="1">
        </li>
        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="<?php the_permalink(); ?>">
                <span itemprop="name"><?php the_title(); ?></span>
            </a>
            <meta itemprop="position" content="2">
        </li>
    </ol>

    <!-- Comic Info Section -->
    <div class="book_detail">
        <div class="book_info">
            <!-- Thumbnail -->
            <div class="book_avatar">
                <img src="<?php echo esc_url($thumbnail); ?>"
                    alt="<?php echo esc_attr('Đọc truyện ' . $comic_title . ' - ' . $author_name . ' - TruyenQQ'); ?>"
                    width="190" height="247" loading="eager" decoding="async">
            </div>

            <!-- Comic Details -->
            <div class="book_other">
                <h1><?php the_title(); ?></h1>

                <div class="txt">
                    <ul class="list-info">
                        <?php if ($other_names): ?>
                        <li class="othername row">
                            <p class="name col-xs-3">
                                <i class="fa fa-plus"></i> Tên khác
                            </p>
                            <p class="other-name col-xs-9"><?php echo esc_html($other_names); ?></p>
                        </li>
                        <?php endif; ?>

                        <li class="author row">
                            <p class="name col-xs-3">
                                <i class="fa fa-user"></i> Tác giả
                            </p>
                            <p class="col-xs-9">
                                <?php if ($authors && !is_wp_error($authors)): ?>
                                <a href="<?php echo get_term_link($authors[0]); ?>">
                                    <?php echo esc_html($author_name); ?>
                                </a>
                                <?php else: ?>
                                <?php echo esc_html($author_name); ?>
                                <?php endif; ?>
                            </p>
                        </li>

                        <li class="status row">
                            <p class="name col-xs-3">
                                <i class="fa fa-rss"></i> Tình trạng
                            </p>
                            <p class="col-xs-9"><?php echo esc_html($status_text); ?></p>
                        </li>

                        <li class="row">
                            <p class="name col-xs-3">
                                <i class="fa fa-heart"></i> Lượt theo dõi
                            </p>
                            <p class="col-xs-9"><?php echo esc_html($follow_count_formatted); ?></p>
                        </li>

                        <li class="row">
                            <p class="name col-xs-3">
                                <i class="fa fa-eye"></i> Lượt xem
                            </p>
                            <p class="col-xs-9"><?php echo esc_html($view_count_formatted); ?></p>
                        </li>
                    </ul>
                </div>

                <!-- Genres -->
                <?php if ($genres && !is_wp_error($genres)): ?>
                <ul class="list01">
                    <?php foreach ($genres as $genre): ?>
                    <li class="li03">
                        <a href="<?php echo get_term_link($genre); ?>">
                            <?php echo esc_html($genre->name); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <div class="clear"></div>

                <!-- CTA Buttons -->
                <ul class="story-detail-menu">
                    <?php if ($first_chapter_url): ?>
                    <li class="li01">
                        <a href="<?php echo esc_url($first_chapter_url); ?>" class="button is-danger is-rounded"
                            aria-label="Đọc từ đầu">
                            <i class="fa fa-book"></i> Đọc từ đầu
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="li02">
                        <a href="javascript:void(0);" class="button is-danger is-rounded btn-subscribe"
                            data-id="<?php echo $post_id; ?>" aria-label="Theo dõi">
                            <i class="fa fa-heart"></i> Theo dõi
                        </a>
                    </li>

                    <li class="li03">
                        <a href="javascript:void(0);" class="button is-danger is-rounded btn-like"
                            data-id="<?php echo $post_id; ?>" aria-label="Thích">
                            <i class="fa fa-thumbs-up"></i> Thích
                        </a>
                    </li>

                    <?php if ($latest_chapter_url): ?>
                    <li class="li04">
                        <a href="<?php echo esc_url($latest_chapter_url); ?>" class="button is-info is-rounded"
                            aria-label="Đọc tiếp">
                            <i class="fa fa-location-arrow"></i> Đọc tiếp
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="clear"></div>
        </div>

        <!-- Description -->
        <?php if ($description): ?>
        <h3><i class="fa fa-info-circle"></i> Giới thiệu</h3>
        <div class="story-detail-info detail-content">
            <?php echo wpautop($description); ?>
        </div>
        <?php endif; ?>

        <!-- Chapter List -->
        <h3><i class="fa fa-database"></i> Danh sách chương</h3>
        <div class="list_chapter">
            <div class="works-chapter-list">
                <?php if (!empty($chapters) && is_array($chapters)): ?>
                <?php foreach ($chapters as $chapter): ?>
                <?php
                        if (!is_array($chapter))
                            continue;

                        $chapter_slug = isset($chapter['slug']) ? $chapter['slug'] : '';
                        $chapter_name = isset($chapter['name']) ? $chapter['name'] : $chapter_slug;

                        if (empty($chapter_slug))
                            continue;

                        $chapter_url = home_url("/truyen-tranh/{$comic_slug}-chap-{$chapter_slug}");
                        ?>
                <div class="works-chapter-item">
                    <div class="col-md-10 col-sm-10 col-xs-8 name-chap">
                        <a href="<?php echo esc_url($chapter_url); ?>">
                            Chương <?php echo esc_html($chapter_name); ?>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-4 time-chap">
                        <?php
                                if (isset($chapter['updated_at']) && !empty($chapter['updated_at'])) {
                                    echo esc_html(date('d/m/Y', strtotime($chapter['updated_at'])));
                                } else {
                                    echo 'Mới cập nhật';
                                }
                                ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p>Chưa có chương nào.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- WordPress Comments -->
    <div class="comment-container" id="comment_list">
        <span class="story-detail-title">
            <i class="fa fa-comments"></i>Bình Luận (<span class="comment-count">
                <?php echo get_comments_number(); ?>
            </span>)
        </span>

        <div class="notify-fanpage">
            Vào <a rel="nofollow" href="https://www.facebook.com/truyenqqq" target="_blank">Fanpage</a> like và theo
            dõi để ủng hộ TruyenQQ nhé.
        </div>

        <div class="group01 comments-container">
            <?php
            if (comments_open() || get_comments_number()) {
                comments_template();
            }
            ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>