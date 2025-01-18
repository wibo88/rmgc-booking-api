<?php
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
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rmgc_booking_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Get and validate the booking data
    $booking_data = json_decode(stripslashes($_POST['booking']), true);
    
    // Basic validation
    $required_fields = array('date', 'firstName', 'lastName', 'email', 'handicap', 'players', 'timePreferences');
    foreach ($required_fields as $field) {
        if (empty($booking_data[$field])) {
            wp_send_json_error('Missing required field: ' . $field);
            return;
        }
    }
    
    // Validate handicap
    if ($booking_data['handicap'] < 0 || $booking_data['handicap'] > 24) {
        wp_send_json_error('Invalid handicap value');
        return;
    }
    
    // Validate players
    if ($booking_data['players'] < 1 || $booking_data['players'] > 4) {
        wp_send_json_error('Invalid number of players');
        return;
    }
    
    // Insert the booking
    $result = rmgc_insert_booking($booking_data);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
        return;
    }
    
    // Send email notifications
    rmgc_send_booking_notification($booking_data);
    
    wp_send_json_success(array(
        'message' => 'Booking created successfully',
        'booking_id' => $result
    ));
}

// Handle booking status updates
function rmgc_api_update_booking_status() {
    // Verify user has permission
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
        return;
    }
    
    // Get and validate the booking ID and status
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    
    if (!$booking_id || !in_array($status, array('approved', 'rejected'))) {
        wp_send_json_error('Invalid booking ID or status');
        return;
    }
    
    // Update the status
    $result = rmgc_update_booking_status($booking_id, $status);
    
    if (!$result) {
        wp_send_json_error('Failed to update booking status');
        return;
    }
    
    wp_send_json_success(array(
        'message' => 'Booking status updated successfully'
    ));
}
