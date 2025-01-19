<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register API endpoints
function rmgc_register_api_endpoints() {
    add_action('wp_ajax_rmgc_create_booking', 'rmgc_api_create_booking');
    add_action('wp_ajax_nopriv_rmgc_create_booking', 'rmgc_api_create_booking');
    add_action('wp_ajax_rmgc_update_booking_status', 'rmgc_api_update_booking_status');
    add_action('init', 'rmgc_add_security_headers');
}
add_action('init', 'rmgc_register_api_endpoints');

// Handle booking creation
function rmgc_api_create_booking() {
    try {
        // Debug logging
        error_log('Booking submission received: ' . print_r($_POST, true));
        
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
            throw new Exception('Invalid booking data format: ' . json_last_error_msg());
        }
        
        // Debug log decoded booking data
        error_log('Decoded booking data: ' . print_r($booking_data, true));
        
        // Get reCAPTCHA response
        $recaptcha_response = isset($booking_data['recaptchaResponse']) ? $booking_data['recaptchaResponse'] : '';
        error_log('reCAPTCHA response: ' . $recaptcha_response);
        
        // Verify reCAPTCHA first
        if (empty($recaptcha_response)) {
            throw new Exception('Please complete the reCAPTCHA verification');
        }
        
        $recaptcha_result = rmgc_verify_recaptcha($recaptcha_response);
        if (is_wp_error($recaptcha_result)) {
            throw new Exception($recaptcha_result->get_error_message());
        }
        
        // Remove recaptcha response from booking data
        unset($booking_data['recaptchaResponse']);
        
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
        // Log the error with detailed context
        rmgc_log_error($e->getMessage(), array(
            'booking_data' => isset($booking_data) ? $booking_data : null,
            'post_data' => $_POST,
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ));
        
        wp_send_json_error($e->getMessage());
    }
}

/**
 * Verify reCAPTCHA response
 */
function rmgc_verify_recaptcha($recaptcha_response) {
    error_log('Starting reCAPTCHA verification');
    
    if (empty($recaptcha_response)) {
        error_log('reCAPTCHA response is empty');
        return new WP_Error('recaptcha_missing', 'Please complete the reCAPTCHA verification');
    }
    
    $secret_key = get_option('rmgc_recaptcha_secret_key');
    if (empty($secret_key)) {
        error_log('reCAPTCHA secret key not configured');
        return new WP_Error('recaptcha_config', 'reCAPTCHA configuration error');
    }
    
    error_log('Sending verification request to Google');
    
    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    $response = wp_remote_post($verify_url, array(
        'body' => array(
            'secret' => $secret_key,
            'response' => $recaptcha_response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        )
    ));
    
    if (is_wp_error($response)) {
        error_log('reCAPTCHA API request failed: ' . $response->get_error_message());
        return new WP_Error('recaptcha_error', 'Failed to verify reCAPTCHA');
    }
    
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    
    error_log('reCAPTCHA API response: ' . print_r($result, true));
    
    if (!isset($result['success']) || !$result['success']) {
        $error_codes = isset($result['error-codes']) ? implode(', ', $result['error-codes']) : '';
        error_log('reCAPTCHA validation failed. Error codes: ' . $error_codes);
        return new WP_Error('recaptcha_failed', 'reCAPTCHA verification failed: ' . $error_codes);
    }
    
    error_log('reCAPTCHA verification successful');
    return true;
}

// Handle booking status updates
function rmgc_api_update_booking_status() {
    // ... (rest of the code remains the same)
}