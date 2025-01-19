<?php
/**
 * Plugin Name: RMGC Booking System
 * Plugin URI: https://royalmelbourne.com.au
 * Description: Booking system for Royal Melbourne Golf Club
 * Version: 2.0.0
 * Author: RMGC
 * Author URI: https://royalmelbourne.com.au
 * Text Domain: rmgc-booking
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RMGC_BOOKING_VERSION', '2.0.0');
define('RMGC_BOOKING_PATH', plugin_dir_path(__FILE__));
define('RMGC_BOOKING_URL', plugin_dir_url(__FILE__));
define('RMGC_BOOKING_MIN_WP_VERSION', '5.8');
define('RMGC_BOOKING_MIN_PHP_VERSION', '7.4');

// Include required files
require_once RMGC_BOOKING_PATH . 'includes/database.php';

// Register activation function
function rmgc_activate_plugin() {
    global $wpdb;
    
    // Create required tables
    $charset_collate = $wpdb->get_charset_collate();
    
    // Create bookings table
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        date date NOT NULL,
        first_name varchar(100) NOT NULL,
        last_name varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        phone varchar(50),
        state varchar(100),
        country varchar(100),
        club_name varchar(100),
        club_state varchar(100),
        club_country varchar(100),
        handicap int(3) NOT NULL,
        players int(1) NOT NULL,
        time_preferences text NOT NULL,
        status varchar(20) DEFAULT 'pending',
        admin_notes text,
        assigned_time time DEFAULT NULL,
        last_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        modified_by bigint(20) DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY idx_status (status),
        KEY idx_date (date),
        KEY idx_email (email)
    ) $charset_collate;";

    // Create notes table
    $notes_table = $wpdb->prefix . 'rmgc_booking_notes';
    $sql .= "CREATE TABLE IF NOT EXISTS $notes_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        booking_id bigint(20) NOT NULL,
        note text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        created_by bigint(20) NOT NULL,
        PRIMARY KEY  (id),
        KEY booking_id (booking_id),
        FOREIGN KEY (booking_id) REFERENCES $table_name(id) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Create logs directory
    $log_dir = WP_CONTENT_DIR . '/rmgc-logs';
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
        file_put_contents($log_dir . '/.htaccess', 'deny from all');
    }
    
    // Set default options
    if (!get_option('rmgc_admin_notification_emails')) {
        update_option('rmgc_admin_notification_emails', get_option('admin_email'));
    }

    // Clear and reschedule tasks
    wp_clear_scheduled_hook('rmgc_cleanup_old_logs');
    if (!wp_next_scheduled('rmgc_cleanup_old_logs')) {
        wp_schedule_event(time(), 'daily', 'rmgc_cleanup_old_logs');
    }

    // Update version
    update_option('rmgc_version', RMGC_BOOKING_VERSION);
}

// Register activation hook
register_activation_hook(__FILE__, 'rmgc_activate_plugin');

// Version compatibility check
function rmgc_version_check() {
    global $wp_version;
    
    if (version_compare(PHP_VERSION, RMGC_BOOKING_MIN_PHP_VERSION, '<')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>' . 
                 sprintf('RMGC Booking System requires PHP version %s or higher.', RMGC_BOOKING_MIN_PHP_VERSION) . 
                 '</p></div>';
        });
        return false;
    }

    if (version_compare($wp_version, RMGC_BOOKING_MIN_WP_VERSION, '<')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>' . 
                 sprintf('RMGC Booking System requires WordPress version %s or higher.', RMGC_BOOKING_MIN_WP_VERSION) . 
                 '</p></div>';
        });
        return false;
    }

    return true;
}

// Only load the plugin if version requirements are met
if (rmgc_version_check()) {
    // Include remaining required files
    require_once RMGC_BOOKING_PATH . 'includes/init.php';
    require_once RMGC_BOOKING_PATH . 'includes/admin-menu.php';
    require_once RMGC_BOOKING_PATH . 'includes/shortcodes.php';
    require_once RMGC_BOOKING_PATH . 'includes/settings.php';
    require_once RMGC_BOOKING_PATH . 'includes/email.php';
    require_once RMGC_BOOKING_PATH . 'includes/api.php';
    require_once RMGC_BOOKING_PATH . 'includes/security.php';

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
}