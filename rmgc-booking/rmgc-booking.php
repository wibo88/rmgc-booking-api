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

// Create the bookings table on plugin activation
function rmgc_create_bookings_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        booking_date date NOT NULL,
        players int(11) NOT NULL,
        handicap int(11) NOT NULL,
        email varchar(100) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Add default notification email
    add_option('rmgc_notification_emails', get_option('admin_email'));
}
register_activation_hook(__FILE__, 'rmgc_create_bookings_table');

[Rest of your existing code...]