<?php
/**
 * Migration Tool - Chạy thủ công
 */

// Add admin menu
add_action('admin_menu', 'nettruyen_migration_tool_menu');

function nettruyen_migration_tool_menu()
{
    add_management_page(
        'NetTruyen Migration',
        'NetTruyen Migration',
        'manage_options',
        'nettruyen-migration',
        'nettruyen_migration_tool_page'
    );
}

function nettruyen_migration_tool_page()
{
    require_once get_template_directory() . '/inc/nettruyen-view-migration.php';

    // Handle form submission
    if (isset($_POST['run_migration']) && check_admin_referer('nettruyen_migration')) {
        $result = NetTruyen_View_Migration::run();

        if ($result) {
            echo '<div class="notice notice-success"><p>✅ Migration completed successfully!</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>❌ Migration failed. Check error logs.</p></div>';
        }
    }

    if (isset($_POST['rollback']) && check_admin_referer('nettruyen_migration')) {
        NetTruyen_View_Migration::rollback();
        echo '<div class="notice notice-warning"><p>⚠️ Tables dropped successfully!</p></div>';
    }

    $needs_migration = NetTruyen_View_Migration::needs_migration();
    $version = get_option('nettruyen_view_migration_version', 'Not installed');
    $date = get_option('nettruyen_view_migration_date', 'N/A');
    ?>

<div class="wrap">
    <h1>NetTruyen View System - Database Migration</h1>

    <div class="card">
        <h2>Migration Status</h2>
        <table class="form-table">
            <tr>
                <th>Version:</th>
                <td><code><?php echo esc_html($version); ?></code></td>
            </tr>
            <tr>
                <th>Installed Date:</th>
                <td><?php echo esc_html($date); ?></td>
            </tr>
            <tr>
                <th>Status:</th>
                <td>
                    <?php if ($needs_migration): ?>
                    <span style="color: orange;">⚠️ Migration needed</span>
                    <?php else: ?>
                    <span style="color: green;">✅ Up to date</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h2>Actions</h2>

        <form method="post" style="margin-bottom: 10px;">
            <?php wp_nonce_field('nettruyen_migration'); ?>
            <button type="submit" name="run_migration" class="button button-primary">
                <?php echo $needs_migration ? '🚀 Run Migration' : '🔄 Re-run Migration'; ?>
            </button>
            <p class="description">
                Tạo hoặc cập nhật database tables cho hệ thống view count.
            </p>
        </form>

        <form method="post" onsubmit="return confirm('⚠️ This will DELETE all view data! Are you sure?');">
            <?php wp_nonce_field('nettruyen_migration'); ?>
            <button type="submit" name="rollback" class="button button-secondary">
                🗑️ Rollback (Drop Tables)
            </button>
            <p class="description" style="color: red;">
                ⚠️ Xóa tất cả tables và data. Chỉ dùng khi cần reset hoàn toàn!
            </p>
        </form>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h2>Tables Info</h2>
        <?php
                global $wpdb;
                $tables = array(
                    'Chapter Views' => $wpdb->prefix . 'nettruyen_chapter_views',
                    'Comic Views' => $wpdb->prefix . 'nettruyen_comic_views',
                    'View Stats' => $wpdb->prefix . 'nettruyen_view_stats'
                );

                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>Table Name</th><th>Status</th><th>Rows</th></tr></thead><tbody>';

                foreach ($tables as $name => $table) {
                    $exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'") == $table;
                    $rows = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM {$table}") : 0;

                    echo '<tr>';
                    echo '<td><code>' . esc_html($table) . '</code></td>';
                    echo '<td>' . ($exists ? '<span style="color:green;">✅ Exists</span>' : '<span style="color:red;">❌ Not found</span>') . '</td>';
                    echo '<td>' . number_format($rows) . '</td>';
                    echo '</tr>';
                }

                echo '</tbody></table>';
                ?>
    </div>
</div>

<?php
}