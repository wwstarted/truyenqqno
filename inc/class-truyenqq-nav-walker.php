<?php
/**
 * TruyenQQ Custom Nav Walker
 *
 *
 * Cách dùng trong WP Admin:
 * - Appearance → Menus → Screen Options → bật "CSS Classes"
 * - Gõ "has-genres-mega" vào CSS Classes của item "Thể Loại"
 * - Indent sub-items để tạo dropdown thường
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TruyenQQ_Nav_Walker extends Walker_Nav_Menu
{

    private $top_level_count = 0;

    public function start_lvl(&$output, $depth = 0, $args = null)
    {
    }

    public function end_lvl(&$output, $depth = 0, $args = null)
    {
        // Không cần làm gì thêm
    }

    /**
     * Start element - render từng <li>
     */
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {

        // Chỉ xử lý top-level (depth = 0)
        if ($depth !== 0) {
            return;
        }

        $this->top_level_count++;
        $is_first_item = ($this->top_level_count === 1);

        // Classes của item từ WP Admin
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $class_str = implode(' ', array_filter($classes));

        // Lấy title & url
        $title = apply_filters('the_title', $item->title, $item->ID);
        $url = esc_url($item->url);

        // Tìm children (sub-menu items)
        $children = $this->get_children($item->ID, $args);
        $has_children = !empty($children);

        // Xác định loại item
        $has_genres_mega = in_array('has-genres-mega', $classes);
        $has_dropdown = $has_children || in_array('has-dropdown', $classes);

        // ── Build <li> classes ──────────────────────────────────────
        $li_classes = ['menu-item'];

        if ($has_genres_mega) {
            $li_classes[] = 'has-dropdown';
        } elseif ($has_dropdown) {
            $li_classes[] = 'has-dropdown';
        }

        // Giữ lại custom classes admin gõ vào (trừ các WP default)
        $wp_default_classes = [
            'menu-item',
            'menu-item-type-custom',
            'menu-item-type-post_type',
            'menu-item-object-custom',
            'menu-item-object-page',
            'menu-item-has-children',
            'current-menu-item',
            'current_page_item',
            'current-menu-ancestor',
        ];

        foreach ($classes as $cls) {
            $cls = trim($cls);
            if ($cls && !in_array($cls, $wp_default_classes) && !in_array($cls, $li_classes)) {
                $li_classes[] = $cls;
            }
        }

        $li_class_str = implode(' ', $li_classes);

        // ── Bắt đầu render ──────────────────────────────────────────
        $output .= "\n<li class=\"{$li_class_str}\">";

        // Link chính
        $link_class = '';
        if ($has_dropdown) {
            $link_class = ' class="dropdown-toggle"';
        }

        $link_target = !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $link_rel = !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';

        if ($has_dropdown) {
            // Item dropdown: link + caret icon
            $output .= "<a href=\"{$url}\"{$link_class}{$link_target}{$link_rel}>";
            $output .= esc_html($title);
            $output .= ' <i class="fa fa-caret-down"></i>';
            $output .= '</a>';
        } else {
            $output .= "<a href=\"{$url}\"{$link_target}{$link_rel}>";
            $output .= esc_html($title);
            $output .= '</a>';
        }

        // ── Mobile toggle button (chỉ inject vào item ĐẦU TIÊN) ────
        if ($is_first_item) {
            $output .= '<button class="mobile-menu-toggle" id="mobileMenuToggle">';
            $output .= '<i class="fa fa-bars"></i>';
            $output .= '</button>';
        }

        // ── Mega menu genres ────────────────────────────────────────
        if ($has_genres_mega) {
            $output .= '<div class="mega-menu">';
            $output .= '<div class="mega-menu-content" id="genresList">';
            $output .= '<div class="loading-genres"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>';
            $output .= '</div>';
            $output .= '</div>';
        }

        // ── Dropdown thường (có children) ───────────────────────────
        elseif ($has_children) {
            $output .= '<div class="mega-menu">';
            $output .= '<div class="mega-menu-content">';

            foreach ($children as $child) {
                $child_title = apply_filters('the_title', $child->title, $child->ID);
                $child_url = esc_url($child->url);
                $child_target = !empty($child->target) ? ' target="' . esc_attr($child->target) . '"' : '';
                $output .= "<a href=\"{$child_url}\"{$child_target}>" . esc_html($child_title) . '</a>';
            }

            $output .= '</div>';
            $output .= '</div>';
        }
    }

    /**
     * End element - đóng </li>
     */
    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        if ($depth !== 0) {
            return;
        }
        $output .= '</li>';
    }

    /**
     * Lấy danh sách children của một menu item
     *
     * @param int    $parent_id  
     * @param object $args       
     * @return array
     */
    private function get_children($parent_id, $args)
    {
        if (empty($args->_truyenqq_items)) {
            return [];
        }

        $children = [];
        foreach ($args->_truyenqq_items as $item) {
            if ((int) $item->menu_item_parent === (int) $parent_id) {
                $children[] = $item;
            }
        }

        return $children;
    }
}


add_filter('wp_nav_menu_args', function ($args) {
    if ($args['theme_location'] !== 'primary') {
        return $args;
    }

    // Lấy menu object
    $menu_obj = wp_get_nav_menu_object($args['menu'] ?? null);
    if (!$menu_obj) {
        $locations = get_nav_menu_locations();
        if (!empty($locations['primary'])) {
            $menu_obj = wp_get_nav_menu_object($locations['primary']);
        }
    }

    if ($menu_obj) {
        $all_items = wp_get_nav_menu_items($menu_obj->term_id);
        if ($all_items) {
            $args['_truyenqq_items'] = $all_items;
        }
    }

    return $args;
});