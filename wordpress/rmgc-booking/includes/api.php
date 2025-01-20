<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register API endpoints
function rmgc_register_api_endpoints() {
    // Booking creation endpoint
    add_action('wp_ajax_rmgc_create_booking', 'rmgc_api_create_booking');
    add_action('wp_ajax_nopriv_rmgc_create_booking', 'rmgc_api_create_booking');
    
    // Admin endpoints (require authentication)
    add_action('wp_ajax_rmgc_update_booking_status', 'rmgc_api_update_booking_status');
    add_action('wp_ajax_rmgc_add_booking_note', 'rmgc_api_add_booking_note');
    add_action('wp_ajax_rmgc_bulk_update_status', 'rmgc_api_bulk_update_status');
    add_action('wp_ajax_rmgc_test_email', 'rmgc_api_test_email');
}
add_action('init', 'rmgc_register_api_endpoints');

// Handle booking creation
function rmgc_api_create_booking() {
    try {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rmgc_booking_nonce')) {
            throw new Exception('Security check failed');
        }
        
        // Get and decode the booking data
        if (!isset($_POST['booking'])) {
            throw new Exception('No booking data received');
        }
        
        $booking_data = json_decode(stripslashes($_POST['booking']), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid booking data format');
        }
        
        // Sanitize and insert booking
        $result = rmgc_insert_booking($booking_data);
        if (is_wp_error($result)) {
            throw new Exception($result->get_error_message());
        }
        
        // Send notifications
        rmgc_send_booking_notification($booking_data);
        
        wp_send_json_success(array(
            'message' => 'Booking created successfully',
            'booking_id' => $result
        ));
        
    } catch (Exception $e) {
        rmgc_log_error('Booking creation failed', array(
            'error' => $e->getMessage(),
            'booking_data' => isset($booking_data) ? $booking_data : null
        ));
        wp_send_json_error($e->getMessage());
    }
}

// Handle booking status updates
function rmgc_api_update_booking_status() {
    try {
        // Verify user has permission
        if (!current_user_can('manage_options')) {
            throw new Exception('Unauthorized access');
        }
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rmgc_admin_nonce')) {
            throw new Exception('Security check failed');
        }
        
        // Get and validate data
        $booking_id = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $tee_time = isset($_POST['tee_time']) ? sanitize_text_field($_POST['tee_time']) : null;
        
        if (!$booking_id || !in_array($status, array('pending', 'approved', 'rejected'))) {
            throw new Exception('Invalid booking ID or status');
        }
        
        // Update the status and tee time
        $result = rmgc_update_booking_status($booking_id, $status, $tee_time);
        if (!$result) {
            throw new Exception('Failed to update booking status');
        }
        
        // Send notification if approved/rejected
        if ($status === 'approved' || $status === 'rejected') {
            rmgc_send_status_notification($booking_id, $status);
        }
        
        wp_send_json_success('Status updated successfully');
        
    } catch (Exception $e) {
        rmgc_log_error('Status update failed', array(
            'error' => $e->getMessage(),
            'booking_id' => isset($booking_id) ? $booking_id : null,
            'status' => isset($status) ? $status : null
        ));
        wp_send_json_error($e->getMessage());
    }
}

// Handle adding notes
function rmgc_api_add_booking_note() {
    try {
        // Verify user has permission
        if (!current_user_can('manage_options')) {
            throw new Exception('Unauthorized access');
        }
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rmgc_admin_nonce')) {
            throw new Exception('Security check failed');
        }
        
        // Get and validate the booking ID and note
        $booking_id = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
        $note = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';
        
        if (!$booking_id || empty($note)) {
            throw new Exception('Invalid booking ID or note content');
        }
        
        // Add the note
        $result = rmgc_add_booking_note($booking_id, $note);
        if (!$result) {
            throw new Exception('Failed to add note');
        }
        
        wp_send_json_success(array(
            'message' => 'Note added successfully',
            'date' => current_time('mysql'),
            'note' => $note
        ));
        
    } catch (Exception $e) {
        rmgc_log_error('Note addition failed', array(
            'error' => $e->getMessage(),
            'booking_id' => isset($booking_id) ? $booking_id : null,
            'note' => isset($note) ? $note : null
        ));
        wp_send_json_error($e->getMessage());
    }
}

// Handle test email
function rmgc_api_test_email() {
    try {
        if (!current_user_can('manage_options')) {
            throw new Exception('Unauthorized access');
        }
        
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rmgc_test_email')) {
            throw new Exception('Security check failed');
        }
        
        $admin_email = get_option('rmgc_admin_notification_emails', get_option('admin_email'));
        $subject = 'RMGC Booking System - Test Email';
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
            </style>
        </head>
        <body>
            <h2>Test Email</h2>
            <p>This is a test email from your RMGC Booking System.</p>
            <p>If you're receiving this, your email settings are working correctly.</p>
            <p>Time sent: " . current_time('mysql') . "</p>
        </body>
        </html>";
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8'
        );
        
        $sent = wp_mail($admin_email, $subject, $message, $headers);
        
        if (!$sent) {
            throw new Exception('Failed to send test email');
        }
        
        wp_send_json_success('Test email sent successfully');
        
    } catch (Exception $e) {
        rmgc_log_error('Test email failed', array(
            'error' => $e->getMessage()
        ));
        wp_send_json_error($e->getMessage());
    }
}