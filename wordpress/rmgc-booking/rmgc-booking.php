<?php
/**
 * Plugin Name: RMGC Booking System
 * Plugin URI: https://royalmelbourne.com.au
 * Description: Booking system for Royal Melbourne Golf Club
 * Version: 1.0
 * Author: RMGC
 * Author URI: https://royalmelbourne.com.au
 * Text Domain: rmgc-booking
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RMGC_BOOKING_VERSION', '1.0.0');
define('RMGC_BOOKING_PATH', plugin_dir_path(__FILE__));
define('RMGC_BOOKING_URL', plugin_dir_url(__FILE__));

// Include required files - order matters!
require_once RMGC_BOOKING_PATH . 'includes/init.php';
require_once RMGC_BOOKING_PATH . 'includes/security.php';  // Load security first for logging
require_once RMGC_BOOKING_PATH . 'includes/database.php';
require_once RMGC_BOOKING_PATH . 'includes/api.php';       // API depends on security and database
require_once RMGC_BOOKING_PATH . 'includes/admin-menu.php';
require_once RMGC_BOOKING_PATH . 'includes/shortcodes.php';
require_once RMGC_BOOKING_PATH . 'includes/settings.php';
require_once RMGC_BOOKING_PATH . 'includes/email.php';

// Register activation hook
register_activation_hook(__FILE__, 'rmgc_activate_plugin');

function rmgc_activate_plugin() {
    // Create database tables
    rmgc_create_bookings_table();
    
    // Create logs directory
    $log_dir = WP_CONTENT_DIR . '/rmgc-logs';
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
        // Create .htaccess to prevent direct access
        file_put_contents($log_dir . '/.htaccess', 'deny from all');
    }
    
    // Test log write access
    rmgc_log_error('Testing RMGC Booking log write access');
    
    // Set default options if not already set
    if (!get_option('rmgc_admin_notification_emails')) {
        update_option('rmgc_admin_notification_emails', get_option('admin_email'));
    }
}

// Initialize nonce for AJAX
function rmgc_add_nonce() {
    wp_localize_script('rmgc-booking-js', 'rmgcAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('rmgc_booking_nonce'),
        'maxPlayers' => 4,
        'maxHandicap' => 24,
        'allowedDays' => array(1, 2, 5), // Monday, Tuesday, Friday
        'messages' => array(
            'invalidDate' => 'Please select a valid date (Monday, Tuesday, or Friday)',
            'invalidHandicap' => 'Handicap must be between 0 and 24',
            'invalidPlayers' => 'Number of players must be between 1 and 4',
            'rateLimitExceeded' => 'Too many booking attempts. Please try again later.'
        )
    ));
}
add_action('wp_enqueue_scripts', 'rmgc_add_nonce');

// Add admin nonce for backend operations
function rmgc_add_admin_nonce() {
    wp_localize_script('rmgc-admin-js', 'rmgcAdmin', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('rmgc_admin_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'rmgc_add_admin_nonce');

// Register deactivation hook
register_deactivation_hook(__FILE__, 'rmgc_deactivate_plugin');

function rmgc_deactivate_plugin() {
    // Clear any scheduled tasks
    wp_clear_scheduled_hook('rmgc_cleanup_old_logs');
}

// Schedule log cleanup
add_action('rmgc_cleanup_old_logs', 'rmgc_cleanup_old_logs');
function rmgc_cleanup_old_logs() {
    $log_dir = WP_CONTENT_DIR . '/rmgc-logs';
    $log_file = $log_dir . '/rmgc-errors.log';
    
    if (file_exists($log_file)) {
        // Keep only the last 1000 lines
        $lines = file($log_file);
        if (count($lines) > 1000) {
            $lines = array_slice($lines, -1000);
            file_put_contents($log_file, implode('', $lines));
        }
    }
}

// Schedule log cleanup to run daily
if (!wp_next_scheduled('rmgc_cleanup_old_logs')) {
    wp_schedule_event(time(), 'daily', 'rmgc_cleanup_old_logs');
}