<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register API endpoints
add_action('init', 'rmgc_register_api_endpoints');

function rmgc_register_api_endpoints() {
    add_action('wp_ajax_rmgc_create_booking', 'rmgc_api_create_booking');
    add_action('wp_ajax_nopriv_rmgc_create_booking', 'rmgc_api_create_booking');
    add_action('wp_ajax_rmgc_update_booking_status', 'rmgc_api_update_booking_status');
}

// Handle booking creation
function rmgc_api_create_booking() {
    rmgc_log('=== Start Booking Creation ===');
    rmgc_log('POST data received:', $_POST);
    
    try {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rmgc_booking_nonce')) {
            rmgc_log('Nonce verification failed');
            throw new Exception('Security check failed');
        }
        
        // Get and decode the booking data
        if (!isset($_POST['booking'])) {
            rmgc_log('No booking data received in POST request');
            throw new Exception('No booking data received');
        }
        
        $booking_data = json_decode(stripslashes($_POST['booking']), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            rmgc_log('JSON decode error: ' . json_last_error_msg());
            rmgc_log('Raw booking data:', $_POST['booking']);
            throw new Exception('Invalid booking data format: ' . json_last_error_msg());
        }

        rmgc_log('Decoded booking data:', $booking_data);
        
        // Check required fields
        $required_fields = array(
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'handicap' => 'Handicap',
            'players' => 'Number of Players',
            'date' => 'Booking Date',
            'timePreferences' => 'Time Preferences'
        );
        
        foreach ($required_fields as $field => $label) {
            if (empty($booking_data[$field])) {
                rmgc_log("Missing required field: $field");
                throw new Exception($label . ' is required');
            }
        }
        
        // Additional validation
        if (!filter_var($booking_data['email'], FILTER_VALIDATE_EMAIL)) {
            rmgc_log('Invalid email format: ' . $booking_data['email']);
            throw new Exception('Please enter a valid email address');
        }
        
        if ($booking_data['handicap'] > 24) {
            rmgc_log('Handicap too high: ' . $booking_data['handicap']);
            throw new Exception('Maximum handicap allowed is 24');
        }
        
        if (!in_array($booking_data['players'], array('1', '2', '3', '4'))) {
            rmgc_log('Invalid number of players: ' . $booking_data['players']);
            throw new Exception('Invalid number of players');
        }
        
        // Validate date (must be Mon, Tue, or Fri)
        $booking_date = new DateTime($booking_data['date']);
        $day_of_week = $booking_date->format('N'); // 1 (Mon) through 7 (Sun)
        if (!in_array($day_of_week, array(1, 2, 5))) {
            rmgc_log('Invalid booking day: ' . $day_of_week);
            throw new Exception('Bookings are only available on Monday, Tuesday, and Friday');
        }
        
        // Check rate limiting
        $rate_limit_check = rmgc_check_rate_limit($booking_data['email']);
        if (is_wp_error($rate_limit_check)) {
            rmgc_log('Rate limit exceeded for email: ' . $booking_data['email']);
            throw new Exception($rate_limit_check->get_error_message());
        }
        
        // Insert the booking
        rmgc_log('Attempting to insert booking');
        $result = rmgc_insert_booking($booking_data);
        if (is_wp_error($result)) {
            rmgc_log('Booking insertion error: ' . $result->get_error_message());
            throw new Exception('Failed to create booking: ' . $result->get_error_message());
        }
        
        rmgc_log('Booking inserted successfully, ID: ' . $result);
        
        // Send email notifications
        try {
            rmgc_log('Attempting to send email notifications');
            rmgc_send_booking_notification($booking_data);
            rmgc_log('Email notifications sent successfully');
        } catch (Exception $e) {
            rmgc_log('Email notification error: ' . $e->getMessage());
            // Don't throw here - booking was successful even if email fails
        }
        
        rmgc_log('=== Booking Creation Successful ===');
        wp_send_json_success(array(
            'message' => 'Booking request submitted successfully. We will contact you shortly.',
            'booking_id' => $result
        ));
        
    } catch (Exception $e) {
        rmgc_log('Booking Creation Error: ' . $e->getMessage());
        wp_send_json_error($e->getMessage());
    }
}

// Handle booking status updates
function rmgc_api_update_booking_status() {
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
        if ($result === false) {
            rmgc_log('Failed to update booking status. Booking ID: ' . $booking_id);
            throw new Exception('Failed to update booking status');
        }
        
        // Add note if provided
        if ($note) {
            $note_result = rmgc_add_booking_note($booking_id, $note);
            if ($note_result === false) {
                rmgc_log('Failed to add booking note. Booking ID: ' . $booking_id);
                // Don't throw - status update was successful
            }
        }
        
        wp_send_json_success();
        
    } catch (Exception $e) {
        rmgc_log('Admin booking update error: ' . $e->getMessage());
        wp_send_json_error($e->getMessage());
    }
}