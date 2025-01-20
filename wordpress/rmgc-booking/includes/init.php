<?php
// Enqueue scripts and styles
function rmgc_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-style', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css');
    
    wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js');
    
    wp_enqueue_script('rmgc-booking', plugin_dir_url(dirname(__FILE__)) . 'js/booking.js', array('jquery', 'jquery-ui-datepicker'), time(), true);
    
    // Add AJAX URL and nonce for our local endpoints
    wp_localize_script('rmgc-booking', 'rmgcAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('rmgc_booking_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'rmgc_enqueue_scripts');

// Version check and database updates
function rmgc_check_version() {
    $current_version = get_option('rmgc_db_version', '1.0.0');
    
    if (version_compare($current_version, '2.0.0', '<')) {
        rmgc_update_db_schema();
        update_option('rmgc_db_version', '2.0.0');
    }
}
add_action('plugins_loaded', 'rmgc_check_version');