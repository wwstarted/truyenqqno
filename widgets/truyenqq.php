<?php
// =====================================================
// LOAD WIDGET CLASSES — base PHẢI đứng đầu tiên
// =====================================================
require_once get_template_directory() . '/inc/class-base-comic-widget.php';
require_once get_template_directory() . '/inc/class-comic-section-widget.php';
require_once get_template_directory() . '/inc/class-hot-comics-widget.php';
require_once get_template_directory() . '/inc/class-new-update-widget.php';

// =====================================================
// REGISTER WIDGET AREAS
// =====================================================
function truyenqq_register_widget_areas()
{
    $wrapper = array(
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '',
        'after_title' => '',
    );
    register_sidebar(array_merge($wrapper, [
        'name' => 'Homepage - Truyện Hay',
        'id' => 'homepage-suggest-section',
        'description' => 'Kéo widget "Truyện Hay" vào đây.',
    ]));
    register_sidebar(array_merge($wrapper, [
        'name' => 'Homepage - Độc Quyền QQ Section',
        'id' => 'homepage-exclusive-section',
        'description' => 'Kéo widget "Độc Quyền" vào đây.',
    ]));
    register_sidebar(array_merge($wrapper, [
        'name' => 'Homepage - Truyện Mới Cập Nhật',
        'id' => 'homepage-new-update-section',
        'description' => 'Kéo widget "Mới Cập Nhật" vào đây.',
    ]));
}
add_action('widgets_init', 'truyenqq_register_widget_areas');

// =====================================================
// REGISTER WIDGETS
// =====================================================
function truyenqq_register_widgets()
{
    register_widget('TruyenQQ_Hot_Comics_Widget');
    register_widget('TruyenQQ_Comic_Section_Widget');
    register_widget('TruyenQQ_New_Update_Widget');
}
add_action('widgets_init', 'truyenqq_register_widgets');