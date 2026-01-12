<?php
/**
 * NetTruyen View Calculator
 * Tính toán fake views theo công thức realistic
 * 
 * @package NetTruyen
 * @version 1.0.1 - Fixed fallback for comics without manifest
 */

if (!defined('ABSPATH')) {
    exit;
}

class NetTruyen_View_Calculator
{

    /**
     * Tính fake views cho một truyện (Version 2.1: With Fallback)
     * 
     * Công thức:
     * base_fake = total_chapters * 100
     * + bonus theo số chapter (nhiều chapter = hot hơn)
     * + bonus theo thời gian online
     * + random factor ±20%
     * 
     * ✅ FIX: Thêm fallback cho truyện không có manifest
     * 
     * @param int $post_id ID truyện
     * @return int Fake views
     */
    public static function calculate_fake_views($post_id)
    {
        // 1. Lấy số chapter từ manifest
        $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);

        $total_chapters = 0;

        if (!empty($manifest_json)) {
            $manifest = json_decode($manifest_json, true);
            $total_chapters = !empty($manifest['chapters']) ? count($manifest['chapters']) : 0;
        }

        // ✅ FALLBACK 1: Thử lấy từ meta khác
        if ($total_chapters === 0) {
            $chapter_count = get_post_meta($post_id, '_nettruyen_chapter_count', true);
            if (!empty($chapter_count) && is_numeric($chapter_count)) {
                $total_chapters = (int) $chapter_count;
            }
        }

        // ✅ FALLBACK 2: Check post content/excerpt để estimate
        if ($total_chapters === 0) {
            $post = get_post($post_id);

            // Nếu có nội dung → estimate 15 chapters
            if (!empty($post->post_content) || !empty($post->post_excerpt)) {
                $total_chapters = 15;
            }
        }

        // ✅ FALLBACK 3: Dựa vào tuổi của post
        if ($total_chapters === 0) {
            $post_date = get_post_field('post_date', $post_id);
            $days_online = max(1, (time() - strtotime($post_date)) / DAY_IN_SECONDS);

            // Post cũ hơn → giả định có nhiều chapter hơn
            if ($days_online > 180) {
                $total_chapters = 20; // 6 tháng+
            } elseif ($days_online > 90) {
                $total_chapters = 15; // 3-6 tháng
            } elseif ($days_online > 30) {
                $total_chapters = 10; // 1-3 tháng
            } else {
                $total_chapters = 5; // Mới đăng
            }
        }

        // ✅ MINIMUM: Ít nhất 5 chapters để có views
        $total_chapters = max(5, $total_chapters);

        // 2. Base fake views = chapters * 100
        $base_fake = $total_chapters * 100;

        // 3. Bonus theo số chapter (truyện nhiều chapter = hot hơn)
        if ($total_chapters > 100) {
            $base_fake *= 1.5; // +50% nếu > 100 chapters
        } elseif ($total_chapters > 50) {
            $base_fake *= 1.2; // +20% nếu > 50 chapters
        }

        // 4. Bonus theo thời gian online (càng lâu càng nhiều view)
        $post_date = get_post_field('post_date', $post_id);
        $days_online = max(1, (time() - strtotime($post_date)) / DAY_IN_SECONDS);

        // Mỗi năm online tăng 100% views
        $time_multiplier = 1 + ($days_online / 365);
        $base_fake *= $time_multiplier;

        // 5. Random factor ±20% để không đều quá
        $random_factor = rand(80, 120) / 100;
        $base_fake *= $random_factor;

        // 6. Minimum 500 views cho truyện mới
        $base_fake = max(500, $base_fake);

        // 7. Làm tròn và return
        return (int) round($base_fake);
    }

    /**
     * Phân bổ fake views theo từng chapter (cho chart realistic)
     * 
     * Logic: Chapter đầu có view cao nhất → giảm dần → cuối tăng lại
     * 
     * @param int $post_id ID truyện
     * @return array ['chapter_slug' => fake_views]
     */
    public static function distribute_fake_views_by_chapter($post_id)
    {
        $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);

        if (empty($manifest_json)) {
            // ✅ FALLBACK: Generate dummy chapters
            $total_chapters = 10;
            $distribution = array();

            for ($i = 1; $i <= $total_chapters; $i++) {
                $chapter_slug = 'chapter-' . $i;

                if ($i === 1) {
                    $views = rand(800, 1200);
                } elseif ($i <= 3) {
                    $views = rand(400, 700);
                } elseif ($i <= 7) {
                    $views = rand(200, 400);
                } else {
                    $views = rand(300, 500);
                }

                $distribution[$chapter_slug] = $views;
            }

            return $distribution;
        }

        $manifest = json_decode($manifest_json, true);
        $chapters = !empty($manifest['chapters']) ? $manifest['chapters'] : array();

        if (empty($chapters)) {
            return array();
        }

        $total_chapters = count($chapters);
        $distribution = array();

        foreach ($chapters as $index => $chapter) {
            $chapter_slug = $chapter['slug'];

            // Chapter đầu: 800-1200 views (nhiều người thử đọc)
            if ($index === 0) {
                $views = rand(800, 1200);
            }
            // 30% đầu: Drop rate, giảm dần
            elseif ($index < $total_chapters * 0.3) {
                $views = rand(400, 700);
            }
            // 30-70%: Giữa truyện, ít người đọc
            elseif ($index < $total_chapters * 0.7) {
                $views = rand(200, 400);
            }
            // 30% cuối: Tăng lại (người theo dõi đến cuối)
            else {
                $views = rand(300, 500);
            }

            $distribution[$chapter_slug] = $views;
        }

        return $distribution;
    }

    /**
     * Tính tổng fake views từ distribution
     * 
     * @param array $distribution Output từ distribute_fake_views_by_chapter()
     * @return int Tổng fake views
     */
    public static function sum_distributed_views($distribution)
    {
        return array_sum($distribution);
    }

    /**
     * Check xem truyện có nên dùng fake views không
     * 
     * Logic: 
     * - Truyện mới (< threshold real views) = dùng fake
     * - Truyện cũ (> X ngày) = tắt fake
     * 
     * @param int $post_id ID truyện
     * @param int $real_views View thật hiện tại
     * @return bool True = dùng fake, False = không dùng
     */
    public static function should_use_fake_views($post_id, $real_views = 0)
    {
        // Lấy settings
        $threshold = (int) get_option('nettruyen_fake_views_threshold', 10000);
        $days_limit = (int) get_option('nettruyen_fake_views_days', 180);

        // Tắt nếu real views đã đủ
        if ($real_views >= $threshold) {
            return false;
        }

        // Tắt nếu truyện quá cũ
        $post_date = get_post_field('post_date', $post_id);
        $days_online = (time() - strtotime($post_date)) / DAY_IN_SECONDS;

        if ($days_online > $days_limit) {
            return false;
        }

        return true;
    }

    /**
     * Tính display views (real + fake nếu enabled)
     * 
     * @param int $post_id ID truyện
     * @param int $real_views View thật
     * @param int $fake_views View giả
     * @param bool $use_fake Có dùng fake không
     * @return int Display views
     */
    public static function calculate_display_views($post_id, $real_views, $fake_views, $use_fake)
    {
        if ($use_fake) {
            return $real_views + $fake_views;
        }

        return $real_views;
    }
}