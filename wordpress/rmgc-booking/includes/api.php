<?php
if (!defined('ABSPATH')) {
    exit;
}

// Add error logging function if not exists
if (!function_exists('rmgc_log_error')) {
    function rmgc_log_error($message, $context = array()) {
        $log_dir = WP_CONTENT_DIR . '/rmgc-logs';
        $log_file = $log_dir . '/rmgc-errors.log';
        
        // Ensure log directory exists
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        // Format the log message
        $timestamp = current_time('mysql');
        $formatted_message = sprintf(
            "[%s] %s\nContext: %s\n",
            $timestamp,
            $message,
            json_encode($context, JSON_PRETTY_PRINT)
        );
        
        // Append to log file
        file_put_contents($log_file, $formatted_message . "\n", FILE_APPEND);
    }
}

// Register API endpoints
function rmgc_register_api_endpoints() {
    // Endpoint for creating bookings
    add_action('wp_ajax_rmgc_create_booking', 'rmgc_api_create_booking');
    add_action('wp_ajax_nopriv_rmgc_create_booking', 'rmgc_api_create_booking');
    
    // Endpoint for updating booking status (admin only)
    add_action('wp_ajax_rmgc_update_booking_status', 'rmgc_api_update_booking_status');
}
add_action('init', 'rmgc_register_api_endpoints');

// Handle booking creation
function rmgc_api_create_booking() {
    try {
        rmgc_log_error('Booking submission started', array(
            'POST' => $_POST,
            'SERVER' => $_SERVER
        ));
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rmgc_booking_nonce')) {
            rmgc_log_error('Nonce verification failed', array(
                'provided_nonce' => isset($_POST['nonce']) ? $_POST['nonce'] : 'not set'
            ));
            throw new Exception('Security check failed');
        }
        
        // Get and decode the booking data
        if (!isset($_POST['booking'])) {
            rmgc_log_error('No booking data received');
            throw new Exception('No booking data received');
        }
        
        $booking_data = json_decode(stripslashes($_POST['booking']), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            rmgc_log_error('JSON decode error', array(
                'error' => json_last_error_msg(),
                'raw_data' => $_POST['booking']
            ));
            throw new Exception('Invalid booking data format');
        }
        
        rmgc_log_error('Booking data decoded', array(
            'booking_data' => $booking_data
        ));
        
        // Insert the booking
        $result = rmgc_insert_booking($booking_data);
        if (is_wp_error($result)) {
            rmgc_log_error('Booking insertion failed', array(
                'error' => $result->get_error_message(),
                'data' => $booking_data
            ));
            throw new Exception('Failed to create booking: ' . $result->get_error_message());
        }
        
        // Send email notifications
        rmgc_send_booking_notification($booking_data);
        
        rmgc_log_error('Booking created successfully', array(
            'booking_id' => $result
        ));
        
        wp_send_json_success(array(
            'message' => 'Booking created successfully',
            'booking_id' => $result
        ));
        
    } catch (Exception $e) {
        rmgc_log_error('Booking creation failed', array(
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'booking_data' => isset($booking_data) ? $booking_data : null
        ));
        
        wp_send_json_error($e->getMessage());
    }
}