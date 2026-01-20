<?php
/**
 * Database Migration for OAuth Support
 * Tự động thêm cột login_method vào bảng login_history
 * 
 * @package TruyenQQ
 * @version 1.0.0
 * 
 * HƯỚNG DẪN SỬ DỤNG:
 * 1. Copy file này vào thư mục inc/
 * 2. Thêm vào functions.php: require_once get_template_directory() . '/inc/database-migration.php';
 * 3. Chạy 1 lần để migration
 * 4. Sau đó comment lại hoặc xóa dòng require_once
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Run database migration
 */
function truyenqq_run_database_migration()
{
    global $wpdb;

    $login_history_table = $wpdb->prefix . 'nettruyen_login_history';

    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$login_history_table'") != $login_history_table) {
        error_log('TruyenQQ Migration: Table ' . $login_history_table . ' does not exist');
        return;
    }

    // Check if login_method column already exists
    $column_exists = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = %s 
            AND TABLE_NAME = %s 
            AND COLUMN_NAME = 'login_method'",
            DB_NAME,
            $login_history_table
        )
    );

    if (!empty($column_exists)) {
        error_log('TruyenQQ Migration: Column login_method already exists');
        return;
    }

    // Add login_method column
    $sql = "ALTER TABLE `$login_history_table` 
            ADD COLUMN `login_method` VARCHAR(50) NOT NULL DEFAULT 'password' 
            COMMENT 'Login method: password, google_oauth, facebook_oauth, google_oauth_register, facebook_oauth_register'
            AFTER `login_status`";

    $result = $wpdb->query($sql);

    if ($result === false) {
        error_log('TruyenQQ Migration Error: ' . $wpdb->last_error);
        return;
    }

    // Add indexes
    $wpdb->query("ALTER TABLE `$login_history_table` ADD INDEX `idx_login_method` (`login_method`)");
    $wpdb->query("ALTER TABLE `$login_history_table` ADD INDEX `idx_user_status_method` (`user_id`, `login_status`, `login_method`)");

    error_log('TruyenQQ Migration: Successfully added login_method column and indexes');
}

/**
 * Run migration on admin init (only once)
 */
add_action('admin_init', 'truyenqq_check_and_run_migration');
function truyenqq_check_and_run_migration()
{
    $migration_version = '1.0.0';
    $current_version = get_option('truyenqq_db_migration_version', '0.0.0');

    if (version_compare($current_version, $migration_version, '<')) {
        truyenqq_run_database_migration();
        update_option('truyenqq_db_migration_version', $migration_version);

        // Show admin notice
        add_action('admin_notices', function () {
            ?>
<div class="notice notice-success is-dismissible">
    <p><strong>TruyenQQ:</strong> Database migration completed successfully! OAuth support is now enabled.</p>
</div>
<?php
        });
    }
}

/**
 * Manual migration trigger (admin page)
 */
add_action('admin_menu', 'truyenqq_add_migration_page');
function truyenqq_add_migration_page()
{
    add_management_page(
        'TruyenQQ Database Migration',
        'DB Migration',
        'manage_options',
        'truyenqq-db-migration',
        'truyenqq_render_migration_page'
    );
}

function truyenqq_render_migration_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // Handle manual migration trigger
    if (isset($_POST['run_migration'])) {
        check_admin_referer('truyenqq_run_migration');

        truyenqq_run_database_migration();
        update_option('truyenqq_db_migration_version', '1.0.0');

        echo '<div class="notice notice-success"><p>Migration completed!</p></div>';
    }

    global $wpdb;
    $login_history_table = $wpdb->prefix . 'nettruyen_login_history';

    // Check current status
    $column_exists = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = %s 
            AND TABLE_NAME = %s 
            AND COLUMN_NAME = 'login_method'",
            DB_NAME,
            $login_history_table
        )
    );

    $migration_version = get_option('truyenqq_db_migration_version', 'Not set');
    ?>

<div class="wrap">
    <h1>TruyenQQ Database Migration</h1>

    <div class="card" style="max-width: 800px;">
        <h2>Migration Status</h2>

        <table class="widefat">
            <tbody>
                <tr>
                    <th>Migration Version</th>
                    <td><code><?php echo esc_html($migration_version); ?></code></td>
                </tr>
                <tr>
                    <th>Table Name</th>
                    <td><code><?php echo esc_html($login_history_table); ?></code></td>
                </tr>
                <tr>
                    <th>Column 'login_method' Status</th>
                    <td>
                        <?php if (!empty($column_exists)): ?>
                        <span style="color: green;">✓ Exists</span>
                        <?php else: ?>
                        <span style="color: red;">✗ Not Found</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php if (empty($column_exists)): ?>
        <form method="post" style="margin-top: 20px;">
            <?php wp_nonce_field('truyenqq_run_migration'); ?>
            <button type="submit" name="run_migration" class="button button-primary button-large">
                Run Migration Now
            </button>
            <p class="description">This will add the 'login_method' column to the login_history table.</p>
        </form>
        <?php else: ?>
        <div class="notice notice-success inline" style="margin: 20px 0;">
            <p><strong>Migration is complete!</strong> OAuth login tracking is now enabled.</p>
        </div>
        <?php endif; ?>
    </div>

    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2>What does this migration do?</h2>
        <p>This migration adds support for OAuth login tracking by:</p>
        <ul style="list-style: disc; margin-left: 20px;">
            <li>Adding a <code>login_method</code> column to track how users log in</li>
            <li>Creating database indexes for better query performance</li>
            <li>Supporting values: <code>password</code>, <code>google_oauth</code>, <code>facebook_oauth</code></li>
        </ul>

        <p><strong>Note:</strong> This migration is safe and non-destructive. It only adds new columns and does not
            modify existing data.</p>
    </div>
</div>

<?php
}