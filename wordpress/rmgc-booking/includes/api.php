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
    try {
        // Get and decode the booking data
        if (!isset($_POST['booking'])) {
            throw new Exception('No booking data received');
        }
        
        $booking_data = json_decode(stripslashes($_POST['booking']), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid booking data format: ' . json_last_error_msg());
        }

        // Log incoming request
        error_log('Booking request received: ' . print_r($booking_data, true));
        
        // Verify nonce
        if (!check_ajax_referer('rmgc_booking_nonce', 'nonce', false)) {
            throw new Exception('Security check failed');
        }
        
        // Verify reCAPTCHA
        if (empty($booking_data['recaptchaResponse'])) {
            throw new Exception('Please complete the reCAPTCHA verification');
        }
        
        $recaptcha_result = rmgc_verify_recaptcha($booking_data['recaptchaResponse']);
        if (is_wp_error($recaptcha_result)) {
            error_log('reCAPTCHA verification failed: ' . $recaptcha_result->get_error_message());
            throw new Exception($recaptcha_result->get_error_message());
        }
        
        // Remove recaptcha response from booking data
        unset($booking_data['recaptchaResponse']);
        
        // Validate required fields
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
                throw new Exception($label . ' is required');
            }
        }
        
        // Additional validation
        if (!filter_var($booking_data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address');
        }
        
        if ($booking_data['handicap'] > 24) {
            throw new Exception('Maximum handicap allowed is 24');
        }
        
        if (!in_array($booking_data['players'], array('1', '2', '3', '4'))) {
            throw new Exception('Invalid number of players');
        }
        
        // Validate date (must be Mon, Tue, or Fri)
        $booking_date = new DateTime($booking_data['date']);
        $day_of_week = $booking_date->format('N'); // 1 (Mon) through 7 (Sun)
        if (!in_array($day_of_week, array(1, 2, 5))) {
            throw new Exception('Bookings are only available on Monday, Tuesday, and Friday');
        }
        
        // Check rate limiting
        $rate_limit_check = rmgc_check_rate_limit($booking_data['email']);
        if (is_wp_error($rate_limit_check)) {
            throw new Exception($rate_limit_check->get_error_message());
        }
        
        // Insert the booking
        $result = rmgc_insert_booking($booking_data);
        if (is_wp_error($result)) {
            error_log('Booking insertion error: ' . $result->get_error_message());
            throw new Exception('Failed to create booking: ' . $result->get_error_message());
        }
        
        // Send email notifications
        try {
            rmgc_send_booking_notification($booking_data);
        } catch (Exception $e) {
            error_log('Email notification error: ' . $e->getMessage());
            // Don't throw here - booking was successful even if email fails
        }
        
        wp_send_json_success(array(
            'message' => 'Booking request submitted successfully. We will contact you shortly.',
            'booking_id' => $result
        ));
        
    } catch (Exception $e) {
        error_log('Booking error: ' . $e->getMessage());
        error_log('Request data: ' . print_r($_POST, true));
        
        wp_send_json_error($e->getMessage());
    }
}

/**
 * Verify reCAPTCHA response
 */
function rmgc_verify_recaptcha($recaptcha_response) {
    if (empty($recaptcha_response)) {
        return new WP_Error('recaptcha_missing', 'Please complete the reCAPTCHA verification');
    }
    
    $secret_key = get_option('rmgc_recaptcha_secret_key');
    if (empty($secret_key)) {
        error_log('reCAPTCHA secret key not configured');
        return new WP_Error('recaptcha_config', 'Security verification configuration error');
    }
    
    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    $response = wp_remote_post($verify_url, array(
        'timeout' => 30,
        'body' => array(
            'secret' => $secret_key,
            'response' => $recaptcha_response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        )
    ));
    
    if (is_wp_error($response)) {
        error_log('reCAPTCHA API error: ' . $response->get_error_message());
        return new WP_Error('recaptcha_error', 'Security verification failed. Please try again.');
    }
    
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    
    if (!isset($result['success']) || !$result['success']) {
        $error_codes = isset($result['error-codes']) ? implode(', ', $result['error-codes']) : '';
        error_log('reCAPTCHA validation failed. Error codes: ' . $error_codes);
        return new WP_Error('recaptcha_failed', 'Security verification failed. Please try again.');
    }
    
    return true;
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
            error_log('Failed to update booking status. Booking ID: ' . $booking_id);
            throw new Exception('Failed to update booking status');
        }
        
        // Add note if provided
        if ($note) {
            $note_result = rmgc_add_booking_note($booking_id, $note);
            if ($note_result === false) {
                error_log('Failed to add booking note. Booking ID: ' . $booking_id);
                // Don't throw - status update was successful
            }
        }
        
        wp_send_json_success();
        
    } catch (Exception $e) {
        error_log('Admin booking update error: ' . $e->getMessage());
        wp_send_json_error($e->getMessage());
    }
}
