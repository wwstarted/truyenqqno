<?php

add_action('admin_menu', 'nettruyen_migration_debug_menu');

function nettruyen_migration_debug_menu()
{
    add_management_page(
        'NetTruyen Migration Debug',
        'NetTruyen Migration',
        'manage_options',
        'nettruyen-migration-debug',
        'nettruyen_migration_debug_page'
    );
}

function nettruyen_migration_debug_page()
{
    global $wpdb;

    if (isset($_POST['force_migration']) && check_admin_referer('nettruyen_migration_debug')) {
        delete_option('nettruyen_view_migration_version');
        nettruyen_run_view_migration();
        echo '<div class="notice notice-info"><p>Migration re-run triggered!</p></div>';
    }

    if (isset($_POST['drop_tables']) && check_admin_referer('nettruyen_migration_debug')) {
        NetTruyen_View_Migration::rollback();
        echo '<div class="notice notice-warning"><p>Tables dropped!</p></div>';
    }

    $needs_migration = NetTruyen_View_Migration::needs_migration();
    $version = get_option('nettruyen_view_migration_version', 'Not installed');
    $date = get_option('nettruyen_view_migration_date', 'N/A');

    $tables = array(
        'Chapter Views' => $wpdb->prefix . 'nettruyen_chapter_views',
        'Comic Views' => $wpdb->prefix . 'nettruyen_comic_views',
        'View Stats' => $wpdb->prefix . 'nettruyen_view_stats',
    );
    ?>
<div class="wrap">
    <h1>🔧 NetTruyen View System - Migration Debug</h1>

    <div class="card" style="max-width:800px;margin:20px 0">
        <h2>📊 Migration Status</h2>
        <table class="form-table">
            <tr>
                <th style="width:200px">Status:</th>
                <td>
                    <?php if ($needs_migration): ?>
                    <span style="color:orange;font-weight:bold">NOT INSTALLED</span>
                    <?php else: ?>
                    <span style="color:green;font-weight:bold">INSTALLED</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Version:</th>
                <td><code><?php echo esc_html($version); ?></code></td>
            </tr>
            <tr>
                <th>Install Date:</th>
                <td><?php echo esc_html($date); ?></td>
            </tr>
            <tr>
                <th>Migration File:</th>
                <td>
                    <?php $fp = get_template_directory() . '/inc/nettruyen-view-migration.php'; ?>
                    <?php if (file_exists($fp)): ?>
                    <span style="color:green">Found</span> <code><?php echo esc_html($fp); ?></code>
                    <?php else: ?>
                    <span style="color:red">NOT FOUND</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="card" style="max-width:800px;margin:20px 0">
        <h2>🗄️ Database Tables</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Table Name</th>
                    <th style="width:100px">Status</th>
                    <th style="width:100px">Rows</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tables as $name => $table):
                        $exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;
                        $rows = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM {$table}") : 0;
                        ?>
                <tr>
                    <td><strong><?php echo esc_html($name); ?></strong><br>
                        <code style="font-size:11px"><?php echo esc_html($table); ?></code>
                    </td>
                    <td><?php echo $exists
                                ? '<span style="color:green;font-weight:bold">OK</span>'
                                : '<span style="color:red;font-weight:bold">❌ Missing</span>'; ?></td>
                    <td><?php echo number_format($rows); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="max-width:800px;margin:20px 0">
        <h2>⚡ Actions</h2>
        <form method="post" style="margin-bottom:15px">
            <?php wp_nonce_field('nettruyen_migration_debug'); ?>
            <button type="submit" name="force_migration" class="button button-primary">🚀 Force Run Migration</button>
            <p class="description">Chạy lại migration (tạo hoặc update tables)</p>
        </form>
        <form method="post" onsubmit="return confirm('This will DELETE all tables and data! Continue?')">
            <?php wp_nonce_field('nettruyen_migration_debug'); ?>
            <button type="submit" name="drop_tables" class="button button-secondary">🗑️ Drop All Tables</button>
            <p class="description" style="color:red">Xóa toàn bộ tables (chỉ dùng khi cần reset)</p>
        </form>
    </div>

    <div class="card" style="max-width:800px;margin:20px 0">
        <h2>🐛 Debug Info</h2>
        <pre style="background:#f5f5f5;padding:15px;overflow-x:auto;font-size:12px"><?php
            echo "WordPress Version: " . get_bloginfo('version') . "\n";
            echo "PHP Version: " . PHP_VERSION . "\n";
            echo "MySQL Version: " . $wpdb->db_version() . "\n";
            echo "Theme Directory: " . get_template_directory() . "\n";
            echo "Database Prefix: " . $wpdb->prefix . "\n";
            echo "\nWP Options:\n";
            echo "- nettruyen_view_migration_version: " . get_option('nettruyen_view_migration_version', 'NULL') . "\n";
            echo "- nettruyen_view_migration_date: " . get_option('nettruyen_view_migration_date', 'NULL') . "\n";
            ?></pre>
    </div>
</div>
<?php
}


// ══════════════════════════════════════════════════════════════════════════════
// 2. VIEW POPULATION TOOL
// ══════════════════════════════════════════════════════════════════════════════

add_action('admin_menu', 'nettruyen_population_tool_menu');

function nettruyen_population_tool_menu()
{
    add_management_page(
        'NetTruyen View Population',
        'View Population',
        'manage_options',
        'nettruyen-view-population',
        'nettruyen_population_tool_page'
    );
}

// ── AJAX: Batch (JS tự loop) ──────────────────────────────────────────────────
add_action('wp_ajax_nettruyen_populate_batch', function () {
    check_ajax_referer('nettruyen_population', 'nonce');
    if (!current_user_can('manage_options'))
        wp_send_json_error(['message' => 'Unauthorized']);
    require_once get_template_directory() . '/inc/nettruyen-view-population.php';
    $batch_size = isset($_POST['batch_size']) ? max(50, min(500, absint($_POST['batch_size']))) : 200;
    wp_send_json_success(NetTruyen_View_Population::populate_all($batch_size));
});

// ── AJAX: Sync (fake hết trong 1 request) ────────────────────────────────────
add_action('wp_ajax_nettruyen_populate_sync', function () {
    check_ajax_referer('nettruyen_population', 'nonce');
    if (!current_user_can('manage_options'))
        wp_send_json_error(['message' => 'Unauthorized']);
    require_once get_template_directory() . '/inc/nettruyen-view-population.php';
    wp_send_json_success(NetTruyen_View_Population::populate_all_sync());
});

// ── AJAX: Reset ───────────────────────────────────────────────────────────────
add_action('wp_ajax_nettruyen_reset_population', function () {
    check_ajax_referer('nettruyen_population', 'nonce');
    if (!current_user_can('manage_options'))
        wp_send_json_error(['message' => 'Unauthorized']);
    require_once get_template_directory() . '/inc/nettruyen-view-population.php';
    wp_send_json_success(['reset' => NetTruyen_View_Population::reset_all()]);
});

function nettruyen_population_tool_page()
{
    global $wpdb;
    require_once get_template_directory() . '/inc/nettruyen-view-population.php';

    $stats_table = $wpdb->prefix . 'nettruyen_view_stats';
    $total_comics = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts}
         WHERE post_type = 'nettruyen_comic'
           AND post_status IN ('publish','private')"
    );
    $total_faked = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$stats_table} WHERE fake_updated_at IS NOT NULL");
    $total_fake_views = (int) $wpdb->get_var("SELECT SUM(total_fake_views) FROM {$stats_table}");
    $avg_fake_views = $total_faked > 0 ? round($total_fake_views / $total_faked) : 0;
    $pending = max(0, $total_comics - $total_faked);
    $nonce = wp_create_nonce('nettruyen_population');
    ?>
<div class="wrap">
    <h1>📊 NetTruyen View Population Tool</h1>

    <!-- Status -->
    <div class="card" style="max-width:820px;margin:20px 0">
        <h2>Current Status</h2>
        <table class="form-table">
            <tr>
                <th style="width:260px">Total Comics (publish/private):</th>
                <td><strong><?php echo number_format($total_comics); ?></strong></td>
            </tr>
            <tr>
                <th>Comics with Fake Views:</th>
                <td>
                    <strong><?php echo number_format($total_faked); ?></strong>
                    <?php if ($pending > 0): ?>
                    <span style="color:orange">(<?php echo number_format($pending); ?> chưa fake)</span>
                    <?php else: ?>
                    <span style="color:green">✅ All populated</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Total Fake Views:</th>
                <td><strong><?php echo number_format($total_fake_views); ?></strong></td>
            </tr>
            <tr>
                <th>Average Fake Views / Comic:</th>
                <td><strong><?php echo number_format($avg_fake_views); ?></strong></td>
            </tr>
        </table>
    </div>

    <!-- Progress -->
    <div id="pop-progress-wrap" class="card" style="max-width:820px;margin:20px 0;display:none">
        <h2>⏳ Progress</h2>
        <div style="background:#e0e0e0;border-radius:4px;height:26px;overflow:hidden;margin-bottom:10px">
            <div id="pop-bar" style="background:#0073aa;height:100%;width:0%;transition:width .3s;border-radius:4px">
            </div>
        </div>
        <p id="pop-text" style="margin:0;font-weight:600;font-size:14px">–</p>
        <div id="pop-log" style="margin-top:12px;max-height:200px;overflow-y:auto;background:#1e1e1e;
             color:#ccc;padding:10px;border-radius:4px;font-size:12px;font-family:monospace"></div>
    </div>

    <!-- Actions -->
    <div class="card" style="max-width:820px;margin:20px 0">
        <h2>Actions</h2>

        <p style="margin-bottom:6px">
            <button id="btn-sync" class="button button-primary button-large">🚀 Run All At Once</button>
            <span style="font-size:12px;color:#666;margin-left:8px">VPS — fake hết trong 1 request (set_time_limit
                0)</span>
        </p>
        <p class="description" style="margin-bottom:24px">Khuyến nghị cho VPS. Nếu server timeout → dùng Batch Loop.</p>

        <p style="margin-bottom:6px">
            <button id="btn-batch" class="button button-secondary button-large">🔄 Batch Loop</button>
            <input id="batch-size-input" type="number" value="200" min="50" max="500"
                style="width:72px;text-align:center;margin-left:8px">
            <span style="font-size:12px;color:#666;margin-left:4px">truyện/lần</span>
            <button id="btn-cancel" class="button" style="display:none;margin-left:12px">⏹ Cancel</button>
        </p>
        <p class="description" style="margin-bottom:24px">JS tự động loop đến khi hết. Dùng cho shared hosting.</p>

        <hr style="margin:24px 0">

        <p style="margin-bottom:6px">
            <button id="btn-reset" class="button" style="color:#b32d2e;border-color:#b32d2e">🗑️ Reset All Data</button>
        </p>
        <p class="description" style="color:#b32d2e">Xóa toàn bộ fake views. Real views KHÔNG bị xóa.</p>
    </div>

    <!-- Sample data -->
    <div class="card" style="max-width:820px;margin:20px 0">
        <h2>📈 Sample Data (Top 10 by Fake Views)</h2>
        <?php $samples = $wpdb->get_results(
                "SELECT s.post_id, s.total_real_views, s.total_fake_views,
                    s.total_display_views, s.fake_updated_at, p.post_title
             FROM {$stats_table} s
             LEFT JOIN {$wpdb->posts} p ON s.post_id = p.ID
             WHERE s.fake_updated_at IS NOT NULL
             ORDER BY s.total_fake_views DESC LIMIT 10"
            ); ?>
        <?php if ($samples): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Comic</th>
                    <th style="width:110px">Real Views</th>
                    <th style="width:110px">Fake Views</th>
                    <th style="width:120px">Display Views</th>
                    <th style="width:155px">Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($samples as $row): ?>
                <tr>
                    <td><strong><?php echo esc_html($row->post_title); ?></strong>
                        <br><small style="color:#999">ID: <?php echo (int) $row->post_id; ?></small>
                    </td>
                    <td><?php echo number_format($row->total_real_views); ?></td>
                    <td><?php echo number_format($row->total_fake_views); ?></td>
                    <td><strong><?php echo number_format($row->total_display_views); ?></strong></td>
                    <td style="font-size:12px"><?php echo esc_html($row->fake_updated_at); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>Chưa có dữ liệu. Bấm "Run All At Once" hoặc "Batch Loop".</p>
        <?php endif; ?>
    </div>
</div>

<script>
(function($) {
    'use strict';
    const AJAX_URL = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
    const NONCE = '<?php echo esc_js($nonce); ?>';
    let running = false,
        totalProcessed = 0,
        startTime = null;

    const $wrap = $('#pop-progress-wrap'),
        $bar = $('#pop-bar'),
        $text = $('#pop-text'),
        $log = $('#pop-log');
    const $sync = $('#btn-sync'),
        $batch = $('#btn-batch'),
        $cancel = $('#btn-cancel'),
        $reset = $('#btn-reset');

    function log(msg, type) {
        const c = {
            info: '#aaa',
            success: '#4caf50',
            error: '#f44336',
            warn: '#ff9800'
        };
        const t = new Date().toLocaleTimeString();
        $log.prepend('<div style="color:' + (c[type] || '#aaa') + ';margin-bottom:3px">[' + t + '] ' + msg +
            '</div>');
    }

    function setProgress(done, total) {
        const pct = total > 0 ? Math.round(done / total * 100) : 0;
        $bar.css('width', pct + '%');
        $text.text(done.toLocaleString() + ' / ' + total.toLocaleString() + ' (' + pct + '%)');
    }

    function setRunning(on) {
        running = on;
        $sync.prop('disabled', on);
        $batch.prop('disabled', on);
        $reset.prop('disabled', on);
        $cancel.toggle(on);
        if (on) $wrap.show();
    }

    function elapsed() {
        if (!startTime) return '';
        const s = Math.round((Date.now() - startTime) / 1000);
        return s < 60 ? s + 's' : Math.floor(s / 60) + 'm ' + (s % 60) + 's';
    }

    function ajax(action, extra) {
        return $.ajax({
            url: AJAX_URL,
            method: 'POST',
            timeout: 600000,
            data: Object.assign({
                action: action,
                nonce: NONCE
            }, extra || {})
        });
    }

    // ── Run All At Once ──────────────────────────────────────────────────────
    $sync.on('click', function() {
        if (running) return;
        if (!confirm(
                'Fake views cho TẤT CẢ comic trong 1 request.\nServer cần set_time_limit(0). Tiếp tục?'))
            return;
        totalProcessed = 0;
        startTime = Date.now();
        setRunning(true);
        log('▶ Sync mode — đang xử lý toàn bộ…', 'info');
        ajax('nettruyen_populate_sync')
            .done(function(res) {
                if (!res.success) {
                    log('✗ Lỗi: ' + ((res.data && res.data.message) || 'Unknown'), 'error');
                    return;
                }
                const d = res.data;
                setProgress(d.processed, d.total);
                log('✔ Xong! ' + d.processed + '/' + d.total + ' comics. ' + elapsed(), 'success');
            })
            .fail(function(xhr, status) {
                log('✗ AJAX failed: ' + status + ' — thử Batch Loop.', 'error');
            })
            .always(function() {
                setRunning(false);
            });
    });

    // ── Batch Loop ───────────────────────────────────────────────────────────
    $batch.on('click', function() {
        if (running) return;
        totalProcessed = 0;
        startTime = Date.now();
        setRunning(true);
        log('▶ Batch loop bắt đầu…', 'info');
        runBatch();
    });

    function runBatch() {
        if (!running) return;
        const bs = parseInt($('#batch-size-input').val(), 10) || 200;
        ajax('nettruyen_populate_batch', {
                batch_size: bs
            })
            .done(function(res) {
                if (!res.success) {
                    log('✗ ' + ((res.data && res.data.message) || 'Error'), 'error');
                    setRunning(false);
                    return;
                }
                const d = res.data;
                totalProcessed += (d.processed || 0);
                setProgress(d.total - d.remaining, d.total);
                log('Batch +' + d.processed + ' | Còn lại: ' + d.remaining + '/' + d.total, 'info');
                if (d.is_complete) {
                    log('✔ Hoàn tất! Tổng: ' + totalProcessed + ' comics. ' + elapsed(), 'success');
                    setRunning(false);
                    return;
                }
                setTimeout(runBatch, 200);
            })
            .fail(function(xhr, status) {
                log('⚠ Batch lỗi: ' + status + '. Retry 3s…', 'warn');
                setTimeout(runBatch, 3000);
            });
    }

    // ── Cancel ───────────────────────────────────────────────────────────────
    $cancel.on('click', function() {
        running = false;
        setRunning(false);
        log('⏹ Đã hủy.', 'warn');
    });

    // ── Reset ────────────────────────────────────────────────────────────────
    $reset.on('click', function() {
        if (!confirm('⚠️ XÓA TOÀN BỘ fake views?\n(Real views KHÔNG bị xóa)\nKhông thể hoàn tác!')) return;
        ajax('nettruyen_reset_population')
            .done(function(res) {
                log(res.success ? '✔ Reset xong.' : '✗ Reset thất bại.', res.success ? 'success' :
                    'error');
                setProgress(0, 0);
                $bar.css('width', '0%');
                $text.text('–');
            });
    });

})(jQuery);
</script>
<?php
}


// ══════════════════════════════════════════════════════════════════════════════
// 3. COUNTRY MIGRATION TOOL
// ══════════════════════════════════════════════════════════════════════════════

add_action('admin_menu', 'nettruyen_country_migration_menu');

function nettruyen_country_migration_menu()
{
    add_management_page(
        'Country Migration',
        'Country Migration',
        'manage_options',
        'nettruyen-country-migration',
        'nettruyen_country_migration_page'
    );
}

function nettruyen_country_migration_page()
{
    $migrated = get_option('nettruyen_country_migrated', false);
    $stats = get_option('nettruyen_country_migration_stats', array());
    $date = get_option('nettruyen_country_migration_date', 'N/A');

    if (isset($_POST['rerun_migration']) && check_admin_referer('nettruyen_country_migration')) {
        delete_option('nettruyen_country_migrated');
        $stats = nettruyen_auto_migrate_country();
        echo '<div class="notice notice-success"><p>✅ Migration completed!</p></div>';
    }

    if (isset($_POST['force_update']) && check_admin_referer('nettruyen_country_migration')) {
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->term_relationships}
             WHERE term_taxonomy_id IN (
                 SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy}
                 WHERE taxonomy = 'nettruyen_country'
             )"
        );
        delete_option('nettruyen_country_migrated');
        $stats = nettruyen_auto_migrate_country();
        echo '<div class="notice notice-success"><p>✅ Force update completed!</p></div>';
    }
    ?>
<div class="wrap">
    <h1>🌏 Country Migration Tool</h1>

    <div class="card" style="max-width:800px;margin:20px 0">
        <h2>Migration Status</h2>
        <table class="form-table">
            <tr>
                <th style="width:200px">Status:</th>
                <td>
                    <?php if ($migrated): ?>
                    <span style="color:green;font-weight:bold">✅ Completed</span>
                    <?php else: ?>
                    <span style="color:orange;font-weight:bold">⚠️ Not Run</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Last Run:</th>
                <td><?php echo esc_html($date); ?></td>
            </tr>
        </table>
    </div>

    <?php if (!empty($stats)): ?>
    <div class="card" style="max-width:800px;margin:20px 0">
        <h2>📊 Migration Statistics</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Country</th>
                    <th style="width:150px">Comics</th>
                    <th style="width:150px">Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php
                        $rows = [
                            '🇨🇳 Trung Quốc (China)' => 'china',
                            '🇰🇷 Hàn Quốc (Korea)' => 'korea',
                            '🇯🇵 Nhật Bản (Japan)' => 'japan',
                            '🇻🇳 Việt Nam (Vietnam)' => 'vietnam',
                            '❓ Unknown' => 'unknown',
                        ];
                        foreach ($rows as $label => $key):
                            $count = $stats[$key] ?? 0;
                            $pct = round($count / max($stats['total'] ?? 1, 1) * 100, 1);
                            ?>
                <tr <?php echo $key === 'unknown' ? 'style="background:#fff3cd"' : ''; ?>>
                    <td><strong><?php echo $label; ?></strong></td>
                    <td><?php echo number_format($count); ?></td>
                    <td><?php echo $pct; ?>%</td>
                </tr>
                <?php endforeach; ?>
                <tr style="background:#e7f3ff;font-weight:bold">
                    <td>TOTAL</td>
                    <td><?php echo number_format($stats['total'] ?? 0); ?></td>
                    <td>100%</td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="card" style="max-width:800px;margin:20px 0">
        <h2>⚡ Actions</h2>
        <form method="post" style="margin-bottom:15px">
            <?php wp_nonce_field('nettruyen_country_migration'); ?>
            <button type="submit" name="rerun_migration" class="button button-primary">🔄 Re-run Migration (Skip
                existing)</button>
            <p class="description">Chỉ cập nhật truyện chưa có country</p>
        </form>
        <form method="post" onsubmit="return confirm('⚠️ This will RESET all country data and re-detect. Continue?')">
            <?php wp_nonce_field('nettruyen_country_migration'); ?>
            <button type="submit" name="force_update" class="button button-secondary">🚀 Force Update All</button>
            <p class="description" style="color:red">Xóa toàn bộ country cũ và detect lại từ đầu</p>
        </form>
    </div>
</div>
<?php
}


// ══════════════════════════════════════════════════════════════════════════════
// 4. FOLLOW POPULATION TOOL
// ══════════════════════════════════════════════════════════════════════════════

add_action('admin_menu', 'nettruyen_follow_population_menu');

function nettruyen_follow_population_menu()
{
    add_management_page(
        'NetTruyen Follow Population',
        'Follow Population',
        'manage_options',
        'nettruyen-follow-population',
        'nettruyen_follow_population_page'
    );
}

// ── AJAX: Batch ───────────────────────────────────────────────────────────────
add_action('wp_ajax_nettruyen_follow_populate_batch', function () {
    check_ajax_referer('nettruyen_follow_population', 'nonce');
    if (!current_user_can('manage_options'))
        wp_send_json_error(['message' => 'Unauthorized']);
    require_once get_template_directory() . '/inc/nettruyen-follow-population.php';
    require_once get_template_directory() . '/inc/class-nettruyen-view-calculator.php';
    $batch_size = isset($_POST['batch_size']) ? max(50, min(500, absint($_POST['batch_size']))) : 200;
    wp_send_json_success(NetTruyen_Follow_Population::populate_all($batch_size));
});

// ── AJAX: Recalculate single ──────────────────────────────────────────────────
add_action('wp_ajax_nettruyen_follow_recalc_single', function () {
    check_ajax_referer('nettruyen_follow_population', 'nonce');
    if (!current_user_can('manage_options'))
        wp_send_json_error(['message' => 'Unauthorized']);
    $post_id = absint($_POST['post_id'] ?? 0);
    if (!$post_id)
        wp_send_json_error(['message' => 'Missing post_id']);
    require_once get_template_directory() . '/inc/nettruyen-follow-population.php';
    require_once get_template_directory() . '/inc/class-nettruyen-view-calculator.php';
    $ok = NetTruyen_Follow_Population::recalculate_single($post_id);
    wp_send_json_success(['recalculated' => $ok, 'post_id' => $post_id]);
});

// ── AJAX: Reset ───────────────────────────────────────────────────────────────
add_action('wp_ajax_nettruyen_follow_reset', function () {
    check_ajax_referer('nettruyen_follow_population', 'nonce');
    if (!current_user_can('manage_options'))
        wp_send_json_error(['message' => 'Unauthorized']);
    require_once get_template_directory() . '/inc/nettruyen-follow-population.php';
    wp_send_json_success(['reset' => NetTruyen_Follow_Population::reset_all()]);
});

function nettruyen_follow_population_page()
{
    global $wpdb;

    require_once get_template_directory() . '/inc/nettruyen-follow-population.php';
    require_once get_template_directory() . '/inc/class-nettruyen-view-calculator.php';

    $follow_stats_table = $wpdb->prefix . 'nettruyen_follow_stats';
    // Xử lý tạo bảng thủ công
if (isset($_POST['create_follow_table']) && check_admin_referer('nettruyen_follow_create_table')) {
    require_once get_template_directory() . '/inc/nettruyen-follow-migration.php';
    NetTruyen_Follow_Migration::run();
    echo '<div class="notice notice-success"><p>✅ Tạo bảng thành công! Reload lại trang.</p></div>';
}
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$follow_stats_table}'") === $follow_stats_table;

    $total_comics = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts}
         WHERE post_type = 'nettruyen_comic'
           AND post_status IN ('publish','private')"
    );
    $total_faked = $table_exists
        ? (int) $wpdb->get_var("SELECT COUNT(*) FROM {$follow_stats_table} WHERE fake_updated_at IS NOT NULL")
        : 0;
    $total_fake_follows = $table_exists
        ? (int) $wpdb->get_var("SELECT SUM(total_fake_follows) FROM {$follow_stats_table}")
        : 0;
    $total_real_follows = $table_exists
        ? (int) $wpdb->get_var("SELECT SUM(total_real_follows) FROM {$follow_stats_table}")
        : 0;
    $avg_fake_follows = $total_faked > 0 ? round($total_fake_follows / $total_faked) : 0;
    $pending = max(0, $total_comics - $total_faked);
    $nonce = wp_create_nonce('nettruyen_follow_population');
    ?>
<div class="wrap">
    <h1>📌 NetTruyen Follow Population Tool</h1>

    <?php if (!$table_exists): ?>
    <div class="notice notice-error">
        <?php if (!$table_exists): ?>
        <div class="notice notice-error">
            <p>❌ Bảng <code><?php echo esc_html($follow_stats_table); ?></code> chưa tồn tại.</p>
            <form method="post">
                <?php wp_nonce_field('nettruyen_follow_create_table'); ?>
                <button type="submit" name="create_follow_table" class="button button-primary">
                    🛠️ Tạo bảng ngay
                </button>
            </form>
        </div>
        <?php endif; ?>

    </div>
    <?php endif; ?>

    <!-- Status -->
    <div class="card" style="max-width:820px;margin:20px 0">
        <h2>Current Status</h2>
        <table class="form-table">
            <tr>
                <th style="width:260px">Follow Stats Table:</th>
                <td>
                    <?php if ($table_exists): ?>
                    <span style="color:green;font-weight:bold">✅ OK</span>
                    <code style="margin-left:8px;font-size:12px"><?php echo esc_html($follow_stats_table); ?></code>
                    <?php else: ?>
                    <span style="color:red;font-weight:bold">❌ Missing</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Total Comics (publish/private):</th>
                <td><strong><?php echo number_format($total_comics); ?></strong></td>
            </tr>
            <tr>
                <th>Comics with Fake Follows:</th>
                <td>
                    <strong><?php echo number_format($total_faked); ?></strong>
                    <?php if ($pending > 0): ?>
                    <span style="color:orange">(<?php echo number_format($pending); ?> chưa fake)</span>
                    <?php else: ?>
                    <span style="color:green">✅ All populated</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Total Fake Follows:</th>
                <td><strong><?php echo number_format($total_fake_follows); ?></strong></td>
            </tr>
            <tr>
                <th>Total Real Follows:</th>
                <td><strong><?php echo number_format($total_real_follows); ?></strong>
                    <span style="color:#666;font-size:12px;margin-left:6px">(từ wp_nettruyen_bookmarks)</span>
                </td>
            </tr>
            <tr>
                <th>Average Fake Follows / Comic:</th>
                <td><strong><?php echo number_format($avg_fake_follows); ?></strong></td>
            </tr>
        </table>
    </div>

    <!-- Progress -->
    <div id="flw-progress-wrap" class="card" style="max-width:820px;margin:20px 0;display:none">
        <h2>⏳ Progress</h2>
        <div style="background:#e0e0e0;border-radius:4px;height:26px;overflow:hidden;margin-bottom:10px">
            <div id="flw-bar" style="background:#46b450;height:100%;width:0%;transition:width .3s;border-radius:4px">
            </div>
        </div>
        <p id="flw-text" style="margin:0;font-weight:600;font-size:14px">–</p>
        <div id="flw-log" style="margin-top:12px;max-height:200px;overflow-y:auto;background:#1e1e1e;
             color:#ccc;padding:10px;border-radius:4px;font-size:12px;font-family:monospace"></div>
    </div>

    <!-- Actions -->
    <div class="card" style="max-width:820px;margin:20px 0">
        <h2>Actions</h2>

        <p style="margin-bottom:6px">
            <button id="flw-btn-batch" class="button button-primary button-large"
                <?php echo !$table_exists ? 'disabled' : ''; ?>>🔄 Batch Loop</button>
            <input id="flw-batch-size" type="number" value="200" min="50" max="500"
                style="width:72px;text-align:center;margin-left:8px">
            <span style="font-size:12px;color:#666;margin-left:4px">truyện/lần</span>
            <button id="flw-btn-cancel" class="button" style="display:none;margin-left:12px">⏹ Cancel</button>
        </p>
        <p class="description" style="margin-bottom:24px">Fake follow count cho tất cả truyện chưa được xử lý. Chỉ
            populate comic CHƯA có fake_updated_at — real follows KHÔNG bị xóa.</p>

        <hr style="margin:24px 0">

        <p style="margin-bottom:6px">
            <button id="flw-btn-reset" class="button" style="color:#b32d2e;border-color:#b32d2e"
                <?php echo !$table_exists ? 'disabled' : ''; ?>>🗑️ Reset All Fake Follows</button>
        </p>
        <p class="description" style="color:#b32d2e">Reset fake follows về 0 để tính lại. Real follows KHÔNG bị xóa.</p>
    </div>

    <!-- Sample data -->
    <?php if ($table_exists): ?>
    <div class="card" style="max-width:820px;margin:20px 0">
        <h2>📈 Sample Data (Top 10 by Display Follows)</h2>
        <?php $samples = $wpdb->get_results(
                    "SELECT fs.post_id, fs.total_real_follows, fs.total_fake_follows,
                    fs.total_display_follows, fs.fake_updated_at, p.post_title
             FROM {$follow_stats_table} fs
             LEFT JOIN {$wpdb->posts} p ON fs.post_id = p.ID
             WHERE fs.fake_updated_at IS NOT NULL
             ORDER BY fs.total_display_follows DESC LIMIT 10"
                ); ?>
        <?php if ($samples): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Comic</th>
                    <th style="width:120px">Real Follows</th>
                    <th style="width:120px">Fake Follows</th>
                    <th style="width:130px">Display Follows</th>
                    <th style="width:155px">Faked At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($samples as $row): ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($row->post_title); ?></strong>
                        <br><small style="color:#999">ID: <?php echo (int) $row->post_id; ?></small>
                    </td>
                    <td><?php echo number_format($row->total_real_follows); ?></td>
                    <td><?php echo number_format($row->total_fake_follows); ?></td>
                    <td><strong><?php echo number_format($row->total_display_follows); ?></strong></td>
                    <td style="font-size:12px"><?php echo esc_html($row->fake_updated_at); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>Chưa có dữ liệu. Bấm "Batch Loop" để populate.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
(function($) {
    'use strict';
    const AJAX_URL = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
    const NONCE = '<?php echo esc_js($nonce); ?>';
    let running = false,
        totalProcessed = 0,
        startTime = null;

    const $wrap = $('#flw-progress-wrap'),
        $bar = $('#flw-bar'),
        $text = $('#flw-text'),
        $log = $('#flw-log');
    const $batch = $('#flw-btn-batch'),
        $cancel = $('#flw-btn-cancel'),
        $reset = $('#flw-btn-reset');

    function log(msg, type) {
        const c = {
            info: '#aaa',
            success: '#4caf50',
            error: '#f44336',
            warn: '#ff9800'
        };
        const t = new Date().toLocaleTimeString();
        $log.prepend('<div style="color:' + (c[type] || '#aaa') + ';margin-bottom:3px">[' + t + '] ' + msg +
            '</div>');
    }

    function setProgress(done, total) {
        const pct = total > 0 ? Math.round(done / total * 100) : 0;
        $bar.css('width', pct + '%');
        $text.text(done.toLocaleString() + ' / ' + total.toLocaleString() + ' (' + pct + '%)');
    }

    function setRunning(on) {
        running = on;
        $batch.prop('disabled', on);
        $reset.prop('disabled', on);
        $cancel.toggle(on);
        if (on) $wrap.show();
    }

    function elapsed() {
        if (!startTime) return '';
        const s = Math.round((Date.now() - startTime) / 1000);
        return s < 60 ? s + 's' : Math.floor(s / 60) + 'm ' + (s % 60) + 's';
    }

    function ajax(action, extra) {
        return $.ajax({
            url: AJAX_URL,
            method: 'POST',
            timeout: 600000,
            data: Object.assign({
                action: action,
                nonce: NONCE
            }, extra || {})
        });
    }

    $batch.on('click', function() {
        if (running) return;
        totalProcessed = 0;
        startTime = Date.now();
        setRunning(true);
        log('▶ Batch loop bắt đầu…', 'info');
        runBatch();
    });

    function runBatch() {
        if (!running) return;
        const bs = parseInt($('#flw-batch-size').val(), 10) || 200;
        ajax('nettruyen_follow_populate_batch', {
                batch_size: bs
            })
            .done(function(res) {
                if (!res.success) {
                    log('✗ ' + ((res.data && res.data.message) || 'Error'), 'error');
                    setRunning(false);
                    return;
                }
                const d = res.data;
                totalProcessed += (d.processed || 0);
                setProgress(d.total - d.remaining, d.total);
                log('Batch +' + d.processed + ' | Còn lại: ' + d.remaining + '/' + d.total, 'info');
                if (d.errors && d.errors.length) {
                    d.errors.forEach(function(e) {
                        log('⚠ ' + e, 'warn');
                    });
                }
                if (d.is_complete) {
                    log('✔ Hoàn tất! Tổng: ' + totalProcessed + ' comics. ' + elapsed(), 'success');
                    setRunning(false);
                    return;
                }
                setTimeout(runBatch, 200);
            })
            .fail(function(xhr, status) {
                log('⚠ Batch lỗi: ' + status + '. Retry 3s…', 'warn');
                setTimeout(runBatch, 3000);
            });
    }

    // ── Cancel ───────────────────────────────────────────────────────────────
    $cancel.on('click', function() {
        running = false;
        setRunning(false);
        log('⏹ Đã hủy.', 'warn');
    });

    // ── Reset ────────────────────────────────────────────────────────────────
    $reset.on('click', function() {
        // MỚI — mô tả đúng hành vi
        if (!confirm(
                '⚠️ Reset fake follows?\n\nFake follows sẽ bị xóa để tính lại.\nReal follows KHÔNG bị ảnh hưởng.\n\nTiếp tục?'
            )) return;
        ajax('nettruyen_follow_reset')
            .done(function(res) {
                log(res.success ? '✔ Reset xong.' : '✗ Reset thất bại.', res.success ? 'success' :
                    'error');
                setProgress(0, 0);
                $bar.css('width', '0%');
                $text.text('–');
            });
    });

})(jQuery);
</script>
<?php
}