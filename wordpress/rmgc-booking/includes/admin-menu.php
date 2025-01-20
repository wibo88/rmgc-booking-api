<?php
if (!defined('ABSPATH')) {
    exit;
}

// Add menu items
function rmgc_add_admin_menu() {
    add_menu_page(
        'RMGC Bookings',
        'RMGC Bookings',
        'manage_options',
        'rmgc-bookings',
        'rmgc_bookings_page',
        'dashicons-calendar-alt'
    );
    
    add_submenu_page(
        'rmgc-bookings',
        'Settings',
        'Settings',
        'manage_options',
        'rmgc-settings',
        'rmgc_booking_settings_page'
    );
    
    add_submenu_page(
        'rmgc-bookings',
        'System Logs',
        'System Logs',
        'manage_options',
        'rmgc-logs',
        'rmgc_logs_page'
    );
}
add_action('admin_menu', 'rmgc_add_admin_menu');

// Render logs page
function rmgc_logs_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $log_file = WP_CONTENT_DIR . '/rmgc-logs/rmgc-errors.log';
    $log_content = '';
    
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
    }
    
    // Handle log clear action
    if (isset($_POST['clear_logs']) && check_admin_referer('rmgc_clear_logs')) {
        file_put_contents($log_file, '');
        $log_content = '';
        echo '<div class="notice notice-success"><p>Logs cleared successfully!</p></div>';
    }
    
    ?>
    <div class="wrap">
        <h1>System Logs</h1>
        
        <form method="post">
            <?php wp_nonce_field('rmgc_clear_logs'); ?>
            <p>
                <input type="submit" name="clear_logs" class="button" value="Clear Logs">
                <button type="button" class="button" onclick="window.location.reload()">Refresh</button>
            </p>
        </form>
        
        <div class="card">
            <textarea style="width: 100%; height: 500px; font-family: monospace; white-space: pre;" readonly><?php 
                echo !empty($log_content) ? esc_textarea($log_content) : 'No logs available.';
            ?></textarea>
        </div>
    </div>
    <?php
}

// Rest of your admin menu code stays the same
// Included for context but not changed
function rmgc_bookings_page() {
    // Your existing bookings page code
}
