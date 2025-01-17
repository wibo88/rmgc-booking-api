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

// Add the booking form shortcode
function rmgc_booking_form_shortcode() {
    wp_enqueue_script('rmgc-booking', plugin_dir_url(__FILE__) . 'js/booking.js', array('jquery'), '1.0', true);
    
    // Pass API details to JavaScript
    wp_localize_script('rmgc-booking', 'rmgcApi', array(
        'apiUrl' => get_option('rmgc_api_url'),
        'apiKey' => get_option('rmgc_api_key'),
        'siteUrl' => get_site_url()
    ));

    // Return the booking form HTML
    return '
        <div id="rmgc-booking-form" class="rmgc-booking-container">
            <div id="rmgc-date-picker"></div>
            <form id="rmgc-booking">
                <div class="form-group">
                    <label for="players">Number of Players:</label>
                    <select id="players" name="players" required>
                        <option value="2">2 Players</option>
                        <option value="3">3 Players</option>
                        <option value="4">4 Players</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="handicap">Highest Handicap:</label>
                    <input type="number" id="handicap" name="handicap" min="0" max="24" required>
                </div>
                <button type="submit">Request Booking</button>
            </form>
            <div id="rmgc-booking-message"></div>
        </div>
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