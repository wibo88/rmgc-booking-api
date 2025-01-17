<?php
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
    
    wp_enqueue_script('rmgc-booking', plugin_dir_url(dirname(__FILE__)) . 'js/booking.js', array('jquery', 'jquery-ui-datepicker'), time(), true);
    
    wp_localize_script('rmgc-booking', 'rmgcApi', array(
        'apiUrl' => get_option('rmgc_api_url'),
        'apiKey' => get_option('rmgc_api_key'),
        'siteUrl' => get_site_url(),
        'recaptchaSiteKey' => get_option('rmgc_recaptcha_site_key')
    ));
}
add_action('wp_enqueue_scripts', 'rmgc_enqueue_scripts');