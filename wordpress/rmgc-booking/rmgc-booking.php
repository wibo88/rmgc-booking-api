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

// Enqueue scripts and styles
function rmgc_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-style', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css');
    
    wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js');
    
    wp_enqueue_script('rmgc-booking', plugin_dir_url(__FILE__) . 'js/booking.js', array('jquery', 'jquery-ui-datepicker'), time(), true);
    
    wp_localize_script('rmgc-booking', 'rmgcApi', array(
        'apiUrl' => get_option('rmgc_api_url'),
        'apiKey' => get_option('rmgc_api_key'),
        'siteUrl' => get_site_url(),
        'recaptchaSiteKey' => get_option('rmgc_recaptcha_site_key')
    ));
}
add_action('wp_enqueue_scripts', 'rmgc_enqueue_scripts');

// Add the booking form shortcode
function rmgc_booking_form_shortcode() {
    return '
        <div id="rmgc-booking-form" class="rmgc-booking-container">
            <form id="rmgc-booking" class="rmgc-form">
                <div class="form-group calendar-container">
                    <h3>Select Date</h3>
                    <div id="embedded-calendar"></div>
                    <input type="hidden" id="booking-date" name="date" required>
                    <small>Available: Mondays, Tuesdays, and Fridays</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="players">Number of Players:</label>
                        <select id="players" name="players" required>
                            <option value="">Select players</option>
                            <option value="1">1 Player</option>
                            <option value="2">2 Players</option>
                            <option value="3">3 Players</option>
                            <option value="4">4 Players</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="handicap">Highest Handicap:</label>
                        <input type="number" id="handicap" name="handicap" min="0" max="24" required>
                        <small>Maximum handicap allowed is 24</small>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>
                
                <div class="g-recaptcha" data-sitekey="' . esc_attr(get_option('rmgc_recaptcha_site_key')) . '"></div>
                
                <button type="submit" class="submit-button">Request Booking</button>
            </form>
            <div id="rmgc-booking-message"></div>
        </div>
        <style>
            .rmgc-form {
                max-width: 800px;
                margin: 20px auto;
                padding: 20px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .calendar-container {
                margin-bottom: 30px;
            }
            #embedded-calendar {
                width: 100%;
                margin-bottom: 15px;
            }
            .form-row {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 20px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
                color: #333;
            }
            .form-group input,
            .form-group select {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
            }
            .form-group small {
                display: block;
                color: #666;
                margin-top: 5px;
            }
            .submit-button {
                background-color: #005b94;
                color: white;
                padding: 12px 24px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                width: 100%;
                font-size: 16px;
                margin-top: 20px;
            }
            .submit-button:hover {
                background-color: #004675;
            }
            #rmgc-booking-message {
                margin-top: 20px;
                padding: 15px;
                border-radius: 4px;
                text-align: center;
            }
            #rmgc-booking-message.error {
                background-color: #ffe6e6;
                color: #d63031;
                border: 1px solid #fab1a0;
            }
            #rmgc-booking-message.success {
                background-color: #e6ffe6;
                color: #27ae60;
                border: 1px solid #a8e6cf;
            }
            .ui-datepicker {
                width: 100%;
                padding: 15px;
                box-sizing: border-box;
            }
            .ui-datepicker table {
                width: 100%;
                font-size: 14px;
            }
            .ui-datepicker th {
                background: #f5f5f5;
                padding: 7px;
                text-align: center;
            }
            .ui-datepicker td {
                padding: 3px;
                text-align: center;
            }
            .ui-datepicker td span,
            .ui-datepicker td a {
                display: block;
                padding: 8px;
                text-align: center;
                text-decoration: none;
                border-radius: 4px;
            }
            .ui-datepicker-unselectable.ui-state-disabled {
                opacity: 0.3;
            }
            .g-recaptcha {
                margin: 20px 0;
                display: flex;
                justify-content: center;
            }
        </style>
    ';
}
add_shortcode('rmgc_booking_form', 'rmgc_booking_form_shortcode');

// [Rest of your existing admin menu and settings code...]