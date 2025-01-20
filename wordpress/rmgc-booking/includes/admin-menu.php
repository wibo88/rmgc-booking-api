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

// Add admin scripts and styles
function rmgc_admin_enqueue_scripts($hook) {
    // Only load on our plugin pages
    if (strpos($hook, 'rmgc-booking') === false) {
        return;
    }
    
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style('wp-jquery-ui-dialog');
    wp_enqueue_style('jquery-ui-style', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css');
    
    wp_enqueue_script(
        'rmgc-admin-js',
        plugins_url('../js/admin.js', __FILE__),
        array('jquery', 'jquery-ui-datepicker', 'jquery-ui-dialog'),
        RMGC_BOOKING_VERSION,
        true
    );
    
    wp_localize_script('rmgc-admin-js', 'rmgcAdmin', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('rmgc_admin_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'rmgc_admin_enqueue_scripts');

// Include admin templates
require_once RMGC_BOOKING_PATH . 'templates/admin/booking-list.php';

// System logs page
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
