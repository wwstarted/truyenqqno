<?php
/**
 * Helper function: Hiển thị thời gian đã qua bằng tiếng Việt
 * 
 * @param mixed $datetime - Có thể là timestamp (số) hoặc date string
 * @return string - Ví dụ: "5 phút trước", "2 giờ trước", "3 ngày trước"
 */
function truyenqq_time_ago_vietnamese($datetime)
{
    // Nếu $datetime rỗng, dùng thời gian hiện tại
    if (empty($datetime)) {
        return 'Vừa xong';
    }

    // Convert sang timestamp
    if (is_numeric($datetime)) {
        // Nếu là timestamp (số)
        $timestamp = (int) $datetime;
    } else {
        // Nếu là date string
        $timestamp = strtotime($datetime);
    }

    // Nếu convert thất bại
    if (!$timestamp) {
        return 'Không rõ';
    }

    $current_time = current_time('timestamp');
    $diff = $current_time - $timestamp;

    // Nếu thời gian trong tương lai (lỗi dữ liệu)
    if ($diff < 0) {
        return 'Vừa xong';
    }

    // Tính toán khoảng thời gian
    $minute = 60;
    $hour = $minute * 60;
    $day = $hour * 24;
    $week = $day * 7;
    $month = $day * 30;
    $year = $day * 365;

    if ($diff < $minute) {
        return 'Vừa xong';
    } elseif ($diff < $hour) {
        $minutes = floor($diff / $minute);
        return $minutes . ' phút trước';
    } elseif ($diff < $day) {
        $hours = floor($diff / $hour);
        return $hours . ' giờ trước';
    } elseif ($diff < $week) {
        $days = floor($diff / $day);
        return $days . ' ngày trước';
    } elseif ($diff < $month) {
        $weeks = floor($diff / $week);
        return $weeks . ' tuần trước';
    } elseif ($diff < $year) {
        $months = floor($diff / $month);
        return $months . ' tháng trước';
    } else {
        $years = floor($diff / $year);
        return $years . ' năm trước';
    }
}