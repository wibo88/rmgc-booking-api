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
        
        // Get and validate the booking ID and status
        $booking_id = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        
        if (!$booking_id || !in_array($status, array('pending', 'approved', 'rejected'))) {
            throw new Exception('Invalid booking ID or status');
        }
        
        // Update the status
        $result = rmgc_update_booking_status($booking_id, $status);
        if (!$result) {
            throw new Exception('Failed to update booking status');
        }
        
        // Send status notification email if needed
        if ($status === 'approved' || $status === 'rejected') {
            rmgc_send_status_notification($booking_id, $status);
        }
        
        // Log success
        rmgc_log_error('Booking status updated', array(
            'booking_id' => $booking_id,
            'new_status' => $status,
            'user_id' => get_current_user_id()
        ));
        
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
            throw new Exception('Invalid booking ID or note');
        }
        
        // Add the note
        $result = rmgc_add_booking_note($booking_id, $note);
        if (!$result) {
            throw new Exception('Failed to add note');
        }
        
        wp_send_json_success('Note added successfully');
        
    } catch (Exception $e) {
        rmgc_log_error('Note addition failed', array(
            'error' => $e->getMessage(),
            'booking_id' => isset($booking_id) ? $booking_id : null
        ));
        wp_send_json_error($e->getMessage());
    }
}

// Handle bulk status updates
function rmgc_api_bulk_update_status() {
    try {
        // Verify user has permission
        if (!current_user_can('manage_options')) {
            throw new Exception('Unauthorized access');
        }
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rmgc_admin_nonce')) {
            throw new Exception('Security check failed');
        }
        
        // Get and validate the booking IDs and status
        $booking_ids = isset($_POST['booking_ids']) ? array_map('absint', $_POST['booking_ids']) : array();
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        
        if (empty($booking_ids) || !in_array($status, array('approved', 'rejected'))) {
            throw new Exception('Invalid booking IDs or status');
        }
        
        // Update each booking
        $success_count = 0;
        $failed_ids = array();
        
        foreach ($booking_ids as $booking_id) {
            $result = rmgc_update_booking_status($booking_id, $status);
            if ($result) {
                $success_count++;
                // Send status notification
                rmgc_send_status_notification($booking_id, $status);
            } else {
                $failed_ids[] = $booking_id;
            }
        }
        
        // Log results
        rmgc_log_error('Bulk status update completed', array(
            'total' => count($booking_ids),
            'successful' => $success_count,
            'failed' => $failed_ids,
            'status' => $status
        ));
        
        if (empty($failed_ids)) {
            wp_send_json_success(sprintf('Successfully updated %d bookings', $success_count));
        } else {
            wp_send_json_error(sprintf('Updated %d bookings, failed to update bookings: %s', 
                $success_count, 
                implode(', ', $failed_ids)
            ));
        }
        
    } catch (Exception $e) {
        rmgc_log_error('Bulk status update failed', array(
            'error' => $e->getMessage(),
            'booking_ids' => isset($booking_ids) ? $booking_ids : null,
            'status' => isset($status) ? $status : null
        ));
        wp_send_json_error($e->getMessage());
    }
}