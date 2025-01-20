<?php
// Initialize logging
function rmgc_init_logging() {
    $log_dir = WP_CONTENT_DIR . '/rmgc-logs';
    $log_file = $log_dir . '/rmgc-errors.log';
    
    // Create logs directory if it doesn't exist
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
    }
    
    // Create .htaccess to prevent direct access
    $htaccess_file = $log_dir . '/.htaccess';
    if (!file_exists($htaccess_file)) {
        file_put_contents($htaccess_file, 'deny from all');
    }
    
    // Create or check log file
    if (!file_exists($log_file)) {
        file_put_contents($log_file, "=== RMGC Booking Log Initialized ===\n");
    }
    
    // Test write permissions
    error_log("Testing RMGC Booking log write access\n", 3, $log_file);
}

// Initialize plugin
function rmgc_init() {
    // Initialize logging
    rmgc_init_logging();
    
    // Enqueue scripts and styles
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-style', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css');
    
    wp_enqueue_script(
        'rmgc-booking',
        plugin_dir_url(dirname(__FILE__)) . 'js/booking.js',
        array('jquery', 'jquery-ui-datepicker'),
        filemtime(plugin_dir_path(dirname(__FILE__)) . 'js/booking.js'),
        true
    );
    
    // Add AJAX URL and nonce for our local endpoints
    wp_localize_script('rmgc-booking', 'rmgcAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('rmgc_booking_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'rmgc_init');

// Logging helper function
function rmgc_log($message, $data = null, $force_log = false) {
    $log_file = WP_CONTENT_DIR . '/rmgc-logs/rmgc-errors.log';
    
    $timestamp = date('[Y-m-d H:i:s] ');
    $log_message = $timestamp . $message;
    
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $log_message .= "\nData: " . print_r($data, true);
        } else {
            $log_message .= "\nData: " . strval($data);
        }
    }
    
    $log_message .= "\n";
    
    if ($force_log || WP_DEBUG) {
        // Try WordPress error log first
        error_log('RMGC Booking: ' . $message);
        
        // Then try our custom log file
        @file_put_contents($log_file, $log_message, FILE_APPEND);
    }
}
