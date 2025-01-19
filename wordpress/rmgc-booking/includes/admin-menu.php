<?php
// Add menu pages
function rmgc_booking_admin_menu() {
    add_menu_page(
        'RMGC Booking',
        'RMGC Booking',
        'manage_options',
        'rmgc-booking',
        'rmgc_booking_requests_page',
        'dashicons-calendar-alt'
    );
    
    add_submenu_page(
        'rmgc-booking',
        'Booking Requests',
        'Booking Requests',
        'manage_options',
        'rmgc-booking',
        'rmgc_booking_requests_page'
    );
    
    add_submenu_page(
        'rmgc-booking',
        'Settings',
        'Settings',
        'manage_options',
        'rmgc-booking-settings',
        'rmgc_booking_settings_page'
    );
}
add_action('admin_menu', 'rmgc_booking_admin_menu');

// Enqueue admin scripts and styles
function rmgc_admin_enqueue_scripts($hook) {
    if ($hook != 'toplevel_page_rmgc-booking') {
        return;
    }
    
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style('wp-jquery-ui-dialog');
    wp_enqueue_style('jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.min.css');
    
    // Enqueue our custom admin JS
    wp_enqueue_script('rmgc-admin-js', plugins_url('../js/admin.js', __FILE__), array('jquery', 'jquery-ui-datepicker', 'jquery-ui-dialog'), '1.0.0', true);
    
    // Pass admin nonce to JS
    wp_localize_script('rmgc-admin-js', 'rmgcAdmin', array(
        'nonce' => wp_create_nonce('rmgc_admin_nonce'),
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('admin_enqueue_scripts', 'rmgc_admin_enqueue_scripts');

// Register AJAX handlers
add_action('wp_ajax_rmgc_update_booking_status', 'rmgc_handle_booking_status_update');
add_action('wp_ajax_rmgc_add_booking_note', 'rmgc_handle_booking_note_add');

function rmgc_handle_booking_status_update() {
    try {
        // Verify permissions
        if (!current_user_can('manage_options')) {
            throw new Exception('Unauthorized access');
        }
        
        // Verify nonce
        if (!check_ajax_referer('rmgc_admin_nonce', 'nonce', false)) {
            throw new Exception('Security check failed');
        }
        
        // Get and validate parameters
        $booking_id = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $assigned_time = isset($_POST['assigned_time']) ? sanitize_text_field($_POST['assigned_time']) : null;
        $note = isset($_POST['note']) ? sanitize_text_field($_POST['note']) : '';
        
        if (!$booking_id || !in_array($status, array('approved', 'rejected'))) {
            throw new Exception('Invalid parameters');
        }
        
        // Update status
        $result = rmgc_update_booking_status($booking_id, $status, $assigned_time);
        if (!$result) {
            throw new Exception('Failed to update booking status');
        }
        
        // Add note if provided
        if ($note) {
            rmgc_add_booking_note($booking_id, $note);
        }
        
        wp_send_json_success();
        
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}

function rmgc_handle_booking_note_add() {
    try {
        // Verify permissions
        if (!current_user_can('manage_options')) {
            throw new Exception('Unauthorized access');
        }
        
        // Verify nonce
        if (!check_ajax_referer('rmgc_admin_nonce', 'nonce', false)) {
            throw new Exception('Security check failed');
        }
        
        // Get and validate parameters
        $booking_id = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
        $note = isset($_POST['note']) ? sanitize_text_field($_POST['note']) : '';
        
        if (!$booking_id || !$note) {
            throw new Exception('Invalid parameters');
        }
        
        // Add note
        $result = rmgc_add_booking_note($booking_id, $note);
        if (!$result) {
            throw new Exception('Failed to add note');
        }
        
        wp_send_json_success();
        
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}

// Include admin templates
require_once plugin_dir_path(__FILE__) . '../templates/admin/booking-list.php';
