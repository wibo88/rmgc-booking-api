<?php
/**
 * Plugin Name: RMGC Booking System
 * Description: Royal Melbourne Golf Club booking system integration
 * Version: 1.0
 * Author: Your Name
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue scripts and styles
function rmgc_enqueue_scripts() {
    // Enqueue jQuery UI and theme
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-style', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css', array(), '1.13.2');
    
    // Enqueue our custom script
    wp_enqueue_script('rmgc-booking', plugin_dir_url(__FILE__) . 'js/booking.js', array('jquery', 'jquery-ui-datepicker'), time(), true);
    
    // Pass API details to JavaScript
    wp_localize_script('rmgc-booking', 'rmgcApi', array(
        'apiUrl' => get_option('rmgc_api_url'),
        'apiKey' => get_option('rmgc_api_key'),
        'siteUrl' => get_site_url()
    ));
}
add_action('wp_enqueue_scripts', 'rmgc_enqueue_scripts');

// Add the booking form shortcode
function rmgc_booking_form_shortcode() {
    // Return the booking form HTML
    return '
        <div id="rmgc-booking-form" class="rmgc-booking-container">
            <form id="rmgc-booking" class="rmgc-form">
                <div class="form-group">
                    <label for="booking-date">Select Date:</label>
                    <input type="text" id="booking-date" name="date" class="datepicker" readonly required>
                    <small>Available: Mondays, Tuesdays, and Fridays</small>
                </div>
                
                <div class="form-group">
                    <label for="players">Number of Players:</label>
                    <select id="players" name="players" required>
                        <option value="">Select number of players</option>
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
                
                <button type="submit" class="submit-button">Request Booking</button>
            </form>
            <div id="rmgc-booking-message"></div>
        </div>
        <style>
            .rmgc-form {
                max-width: 500px;
                margin: 20px auto;
            }
            .rmgc-form .form-group {
                margin-bottom: 20px;
            }
            .rmgc-form label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            .rmgc-form input[type="text"],
            .rmgc-form input[type="number"],
            .rmgc-form select {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
            }
            .rmgc-form small {
                display: block;
                color: #666;
                margin-top: 5px;
            }
            .rmgc-form .submit-button {
                background-color: #005b94;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            .rmgc-form .submit-button:hover {
                background-color: #004675;
            }
            #rmgc-booking-message {
                margin-top: 20px;
                padding: 10px;
                border-radius: 4px;
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
            /* Date picker styles */
            .ui-datepicker {
                background-color: white;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                display: none;
            }
            .ui-datepicker .ui-datepicker-header {
                background: #005b94;
                color: white;
                padding: 5px;
                border-radius: 3px;
            }
            .ui-datepicker .ui-datepicker-title {
                text-align: center;
            }
            .ui-datepicker .ui-datepicker-prev,
            .ui-datepicker .ui-datepicker-next {
                cursor: pointer;
                position: absolute;
                top: 5px;
                width: 20px;
                height: 20px;
                text-align: center;
                color: white;
            }
            .ui-datepicker .ui-datepicker-prev {
                left: 5px;
            }
            .ui-datepicker .ui-datepicker-next {
                right: 5px;
            }
            .ui-datepicker td {
                padding: 3px;
                text-align: center;
            }
            .ui-datepicker td span,
            .ui-datepicker td a {
                display: block;
                padding: 5px;
                text-decoration: none;
                text-align: center;
            }
            .ui-datepicker-unselectable.ui-state-disabled {
                color: #ccc;
            }
        </style>
    ';
}
add_shortcode('rmgc_booking_form', 'rmgc_booking_form_shortcode');

// Add admin menu
function rmgc_add_admin_menu() {
    add_menu_page(
        'RMGC Booking Settings',
        'RMGC Booking',
        'manage_options',
        'rmgc-booking-settings',
        'rmgc_settings_page'
    );
}
add_action('admin_menu', 'rmgc_add_admin_menu');

// Create the settings page
function rmgc_settings_page() {
    // Save settings
    if (isset($_POST['rmgc_save_settings'])) {
        update_option('rmgc_api_url', sanitize_text_field($_POST['rmgc_api_url']));
        update_option('rmgc_api_key', sanitize_text_field($_POST['rmgc_api_key']));
        echo '<div class="updated"><p>Settings saved!</p></div>';
    }

    // Display settings form
    ?>
    <div class="wrap">
        <h2>RMGC Booking System Settings</h2>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="rmgc_api_url">API URL:</label></th>
                    <td>
                        <input type="text" id="rmgc_api_url" name="rmgc_api_url" 
                               value="<?php echo esc_attr(get_option('rmgc_api_url')); ?>" 
                               class="regular-text">
                        <p class="description">Example: http://localhost:3000</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmgc_api_key">API Key:</label></th>
                    <td>
                        <input type="text" id="rmgc_api_key" name="rmgc_api_key" 
                               value="<?php echo esc_attr(get_option('rmgc_api_key')); ?>" 
                               class="regular-text">
                    </td>
                </tr>
            </table>
            <p>
                <input type="submit" name="rmgc_save_settings" class="button-primary" 
                       value="Save Settings">
            </p>
        </form>
        <h3>Shortcode</h3>
        <p>Use this shortcode to display the booking form: <code>[rmgc_booking_form]</code></p>
    </div>
    <?php
}