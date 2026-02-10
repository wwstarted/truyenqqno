<?php
/**
 * Template Name: Truyện Full
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

get_header();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$posts_per_page = 42;

$country = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';

$args = array(
    'post_type' => 'nettruyen_comic',
    'post_status' => 'publish',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'meta_query' => array(
        array(
            'key' => '_nettruyen_status',
            'value' => 'completed',
            'compare' => '='
        )
    ),
    'orderby' => 'modified',
    'order' => 'DESC'
);

if ($country !== '') {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'nettruyen_country',
            'field' => 'slug',
            'terms' => $country
        )
    );
}

$comics_query = new WP_Query($args);

$total_pages = $comics_query->max_num_pages;
$current_page = max(1, $paged);
?>

<div id="main_homepage" data-ajax-enabled="true" data-filter-type="truyen-full">
    <div class="homepage_tags">
        <h1>
            <p class="text_list_update">
                <i class="fa fa-check-circle" aria-hidden="true"></i> Truyện Hoàn Thành
            </p>
        </h1>
        <div class="clear"></div>
    </div>

    <div class="story-list-bl01 box">
        <table>
            <tbody>
                <tr>
                    <th>Quốc gia</th>
                    <td>
                        <ul class="choose">
                            <li>
                                <a class="<?php echo ($country === '') ? 'active' : ''; ?>"
                                    href="<?php echo get_permalink(); ?>">Tất cả</a>
                            </li>
                            <li>
                                <a class="<?php echo ($country === 'China') ? 'active' : ''; ?>"
                                    href="<?php echo add_query_arg('country', 'China'); ?>">Trung Quốc</a>
                            </li>
                            <li>
                                <a class="<?php echo ($country === 'Vietnam') ? 'active' : ''; ?>"
                                    href="<?php echo add_query_arg('country', 'Vietnam'); ?>">Việt Nam</a>
                            </li>
                            <li>
                                <a class="<?php echo ($country === 'Korea') ? 'active' : ''; ?>"
                                    href="<?php echo add_query_arg('country', 'Korea'); ?>">Hàn Quốc</a>
                            </li>
                            <li>
                                <a class="<?php echo ($country === 'Japan') ? 'active' : ''; ?>"
                                    href="<?php echo add_query_arg('country', 'Japan'); ?>">Nhật Bản</a>
                            </li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="list_grid_out">
        <ul class="list_grid grid">
            <?php
            if ($comics_query->have_posts()):
                while ($comics_query->have_posts()):
                    $comics_query->the_post();
                    $post_id = get_the_ID();

                    $thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);
                    if (empty($thumbnail)) {
                        $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
                    }
                    if (empty($thumbnail)) {
                        $thumbnail = 'https://via.placeholder.com/190x247?text=No+Image';
                    }

                    $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
                    $manifest = !empty($manifest_json) ? json_decode($manifest_json, true) : null;

                    $chapter_count = 0;
                    $latest_chapter = 'Hoàn thành';
                    if (!empty($manifest['chapters'])) {
                        $chapters = $manifest['chapters'];
                        $chapter_count = count($chapters);
                        $latest = end($chapters);
                        $latest_chapter = 'Chapter ' . $latest['name'] . ' (End)';
                    }


                    $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_the_modified_date('Y-m-d H:i:s');


                    $time_ago = truyenqq_time_ago_vietnamese($updated_at);

                    $follow_count = get_post_meta($post_id, '_nettruyen_follow_count', true) ?: 0;

                    global $wpdb;
                    $stats_table = $wpdb->prefix . 'nettruyen_view_stats';
                    $view_stats = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT total_display_views FROM {$stats_table} WHERE post_id = %d",
                            $post_id
                        )
                    );
                    $view_count = $view_stats ? $view_stats->total_display_views : 0;
                    ?>

            <li>
                <div class="book_avatar">
                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                        <img class="center" src="<?php echo esc_url($thumbnail); ?>"
                            alt="<?php the_title_attribute(); ?>" loading="lazy">
                    </a>

                    <?php truyenqq_render_bookmark_badge($post_id); ?>

                    <div class="top-notice">
                        <span class="time-ago">
                            <?php echo esc_html($time_ago); ?>
                        </span>
                        <span class="type-label label-full">Full</span>
                    </div>
                </div>

                <div class="book_info">
                    <div class="book_name">
                        <h3>
                            <a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h3>
                    </div>
                    <div class="clear"></div>

                    <div class="text_detail">
                        <span><i class="fa fa-bookmark"></i>
                            <?php echo number_format($follow_count); ?>
                        </span>
                        <span><i class="fa fa-eye"></i>
                            <?php echo number_format($view_count); ?>
                        </span>
                        <?php if ($chapter_count > 0): ?>
                        <span><i class="fa fa-book"></i>
                            <?php echo $chapter_count; ?> Ch
                        </span>
                        <?php endif; ?>
                    </div>

                    <div class="last_chapter">
                        <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr($latest_chapter); ?>">
                            <?php echo esc_html($latest_chapter); ?>
                        </a>
                    </div>
                </div>

                <div class="clear"></div>
            </li>

            <?php
                endwhile;
                wp_reset_postdata();
            else:
                echo '<li class="no-results"><p>Chưa có truyện hoàn thành.</p></li>';
            endif;
            ?>
        </ul>
    </div>

    <div class="clear"></div>

    <?php if ($total_pages > 1): ?>
    <div class="page_redirect">
        <?php if ($current_page > 1): ?>
        <a href="<?php echo get_pagenum_link($current_page - 1); ?>">
            <p><span aria-hidden="true">‹</span></p>
        </a>
        <?php endif; ?>

        <?php
            $range = 2;
            $start = max(1, $current_page - $range);
            $end = min($total_pages, $current_page + $range);

            if ($start > 1):
                ?>
        <a href="<?php echo get_pagenum_link(1); ?>">
            <p>1</p>
        </a>
        <?php if ($start > 2): ?>
        <span class="dots">...</span>
        <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start; $i <= $end; $i++): ?>
        <?php if ($i == $current_page): ?>
        <a href="javascript:void(0)">
            <p class="active">
                <?php echo $i; ?>
            </p>
        </a>
        <?php else: ?>
        <a href="<?php echo get_pagenum_link($i); ?>">
            <p>
                <?php echo $i; ?>
            </p>
        </a>
        <?php endif; ?>
        <?php endfor; ?>

        <?php if ($end < $total_pages): ?>
        <?php if ($end < $total_pages - 1): ?>
        <span class="dots">...</span>
        <?php endif; ?>
        <a href="<?php echo get_pagenum_link($total_pages); ?>">
            <p>
                <?php echo $total_pages; ?>
            </p>
        </a>
        <?php endif; ?>

        <?php if ($current_page < $total_pages): ?>
        <a href="<?php echo get_pagenum_link($current_page + 1); ?>">
            <p><span aria-hidden="true">›</span></p>
        </a>
        <a href="<?php echo get_pagenum_link($total_pages); ?>">
            <p><span aria-hidden="true">»</span></p>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php get_footer(); ?>