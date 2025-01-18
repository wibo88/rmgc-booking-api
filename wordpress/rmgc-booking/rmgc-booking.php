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

// Include required files
require_once plugin_dir_path(__FILE__) . 'includes/init.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/email.php';
require_once plugin_dir_path(__FILE__) . 'includes/database.php';
require_once plugin_dir_path(__FILE__) . 'includes/api.php';

// Register activation hook
register_activation_hook(__FILE__, 'rmgc_activate_plugin');

function rmgc_activate_plugin() {
    // Create database tables
    rmgc_create_bookings_table();
}

// Initialize nonce for AJAX
function rmgc_add_nonce() {
    wp_localize_script('rmgc-booking-js', 'rmgcAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('rmgc_booking_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'rmgc_add_nonce');