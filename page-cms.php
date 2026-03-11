<?php
/**
 * Template Name: CMS Page
 * Description: Dùng cho các trang thông tin: About, Chính Sách, Điều Khoản,...
 *
 * @package TruyenQQ
 */

get_header();
?>

<main class="cms-page">

    <?php while (have_posts()):
        the_post(); ?>

    <!-- Breadcrumb -->
    <?php if (function_exists('rank_math_the_breadcrumbs')): ?>
    <div class="cms-breadcrumb">
        <?php rank_math_the_breadcrumbs(); ?>
    </div>
    <?php endif; ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class('cms-article'); ?>>

        <!-- Main Content — title được render từ Gutenberg/editor -->
        <div class="cms-content">
            <?php the_content(); ?>
        </div>

        <!-- CMS Footer Nav — Quick links đến các trang chính của site -->
        <div class="cms-footer-nav">
            <div class="cms-footer-nav-label">
                <i class="fa fa-compass"></i>
                <span>Khám phá TruyenQQ</span>
            </div>
            <div class="cms-footer-nav-links">
                <a href="<?php echo esc_url(home_url('/truyen-moi-cap-nhat')); ?>" class="cms-nav-link">
                    <i class="fa fa-cloud-download"></i>
                    <span>Mới cập nhật</span>
                </a>
                <a href="<?php echo esc_url(home_url('/top-ngay')); ?>" class="cms-nav-link">
                    <i class="fa fa-fire"></i>
                    <span>Top ngày</span>
                </a>
                <a href="<?php echo esc_url(home_url('/top-tuan')); ?>" class="cms-nav-link">
                    <i class="fa fa-bar-chart"></i>
                    <span>Top tuần</span>
                </a>
                <a href="<?php echo esc_url(home_url('/top-thang')); ?>" class="cms-nav-link">
                    <i class="fa fa-trophy"></i>
                    <span>Top tháng</span>
                </a>
                <a href="<?php echo esc_url(home_url('/truyen-moi')); ?>" class="cms-nav-link">
                    <i class="fa fa-star"></i>
                    <span>Truyện mới</span>
                </a>
                <a href="<?php echo esc_url(home_url('/truyen-full')); ?>" class="cms-nav-link">
                    <i class="fa fa-check-circle"></i>
                    <span>Truyện full</span>
                </a>
                <a href="<?php echo esc_url(home_url('/tim-kiem-nang-cao')); ?>" class="cms-nav-link">
                    <i class="fa fa-filter"></i>
                    <span>Tìm nâng cao</span>
                </a>
                <a href="<?php echo esc_url(home_url('/ngau-nhien')); ?>" class="cms-nav-link cms-nav-link--accent">
                    <i class="fa fa-random"></i>
                    <span>Ngẫu nhiên</span>
                </a>
            </div>
        </div>

    </article>

    <?php endwhile; ?>

</main>

<?php get_footer(); ?>