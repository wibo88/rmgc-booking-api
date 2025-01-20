<?php
// Add menu pages
function rmgc_booking_admin_menu() {
    add_menu_page(
        'RMGC Booking',
        'RMGC Booking',
        'manage_options',
        'rmgc-booking',
        'rmgc_booking_requests_page',
        'dashicons-calendar-alt'
    );
    
    add_submenu_page(
        'rmgc-booking',
        'Booking Requests',
        'Booking Requests',
        'manage_options',
        'rmgc-booking',
        'rmgc_booking_requests_page'
    );
    
    add_submenu_page(
        'rmgc-booking',
        'System Logs',
        'System Logs',
        'manage_options',
        'rmgc-booking-logs',
        'rmgc_booking_logs_page'
    );
    
    add_submenu_page(
        'rmgc-booking',
        'Settings',
        'Settings',
        'manage_options',
        'rmgc-booking-settings',
        'rmgc_booking_settings_page'
    );
}
add_action('admin_menu', 'rmgc_booking_admin_menu');

function rmgc_booking_logs_page() {
    // Security check
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    
    $log_file = WP_CONTENT_DIR . '/rmgc-logs/rmgc-errors.log';
    $log_content = 'No logs found.';
    
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        // Get last 1000 lines (or less if file is smaller)
        $lines = explode("\n", $log_content);
        $lines = array_slice($lines, -1000);
        $log_content = implode("\n", $lines);
    }
    
    // Clear logs if requested
    if (isset($_POST['clear_logs']) && check_admin_referer('rmgc_clear_logs')) {
        file_put_contents($log_file, '');
        $log_content = 'Logs cleared.';
        echo '<div class="notice notice-success"><p>Logs have been cleared.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>System Logs</h1>
        <div class="rmgc-logs-actions" style="margin: 20px 0;">
            <form method="post" style="display: inline-block;">
                <?php wp_nonce_field('rmgc_clear_logs'); ?>
                <input type="submit" name="clear_logs" class="button" value="Clear Logs">
            </form>
        </div>
        <div class="rmgc-logs-viewer" style="background: #fff; padding: 20px; border: 1px solid #ccc;">
            <pre style="white-space: pre-wrap; word-wrap: break-word; max-height: 500px; overflow-y: auto;"><?php echo esc_html($log_content); ?></pre>
        </div>
    </div>
    <?php
}

// [Rest of the admin-menu.php file remains the same]