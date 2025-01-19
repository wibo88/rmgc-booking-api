<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register API endpoints
function rmgc_register_api_endpoints() {
    // Endpoint for creating bookings
    add_action('wp_ajax_rmgc_create_booking', 'rmgc_api_create_booking');
    add_action('wp_ajax_nopriv_rmgc_create_booking', 'rmgc_api_create_booking');
    
    // Endpoint for updating booking status (admin only)
    add_action('wp_ajax_rmgc_update_booking_status', 'rmgc_api_update_booking_status');
    
    // Add security headers
    add_action('init', 'rmgc_add_security_headers');
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
        
        // Sanitize the booking data
        $booking_data = rmgc_sanitize_booking_data($booking_data);
        
        // Validate the booking data
        $validation_result = rmgc_validate_booking_data($booking_data);
        if (is_wp_error($validation_result)) {
            throw new Exception($validation_result->get_error_message());
        }
        
        // Check rate limiting
        $rate_limit_check = rmgc_check_rate_limit($booking_data['email']);
        if (is_wp_error($rate_limit_check)) {
            throw new Exception($rate_limit_check->get_error_message());
        }

        // Extract recaptcha response from booking data
        $recaptcha_response = isset($booking_data['recaptchaResponse']) ? $booking_data['recaptchaResponse'] : '';
        unset($booking_data['recaptchaResponse']); // Remove from booking data before storage
        
        // Verify reCAPTCHA
        if (empty($recaptcha_response)) {
            throw new Exception('Please complete the reCAPTCHA verification');
        }
        
        $recaptcha_result = rmgc_verify_recaptcha($recaptcha_response);
        if (is_wp_error($recaptcha_result)) {
            throw new Exception($recaptcha_result->get_error_message());
        }
        
        // Insert the booking
        $result = rmgc_insert_booking($booking_data);
        if (is_wp_error($result)) {
            throw new Exception($result->get_error_message());
        }
        
        // Send email notifications
        rmgc_send_booking_notification($booking_data);
        
        wp_send_json_success(array(
            'message' => 'Booking created successfully',
            'booking_id' => $result
        ));
        
    } catch (Exception $e) {
        // Log the error
        rmgc_log_error($e->getMessage(), array(
            'booking_data' => isset($booking_data) ? $booking_data : null,
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
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
        
        // Get and validate the booking ID and status
        $booking_id = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        
        if (!$booking_id || !in_array($status, array('pending', 'approved', 'rejected'), true)) {
            throw new Exception('Invalid booking ID or status');
        }
        
        // Update the status
        $result = rmgc_update_booking_status($booking_id, $status);
        if (!$result) {
            throw new Exception('Failed to update booking status');
        }
        
        wp_send_json_success(array(
            'message' => 'Booking status updated successfully'
        ));
        
    } catch (Exception $e) {
        // Log the error
        rmgc_log_error($e->getMessage(), array(
            'booking_id' => isset($booking_id) ? $booking_id : null,
            'status' => isset($status) ? $status : null,
            'user_id' => get_current_user_id()
        ));
        
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
        rmgc_log_error('reCAPTCHA secret key not configured');
        return new WP_Error('recaptcha_config', 'reCAPTCHA configuration error');
    }
    
    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    $response = wp_remote_post($verify_url, array(
        'body' => array(
            'secret' => $secret_key,
            'response' => $recaptcha_response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        )
    ));
    
    if (is_wp_error($response)) {
        rmgc_log_error('reCAPTCHA verification failed', array('error' => $response->get_error_message()));
        return new WP_Error('recaptcha_error', 'Failed to verify reCAPTCHA');
    }
    
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    
    if (!isset($result['success']) || !$result['success']) {
        $error_codes = isset($result['error-codes']) ? implode(', ', $result['error-codes']) : '';
        rmgc_log_error('reCAPTCHA validation failed', array(
            'error_codes' => $error_codes,
            'response' => $result
        ));
        return new WP_Error('recaptcha_failed', 'reCAPTCHA verification failed');
    }
    
    return true;
}