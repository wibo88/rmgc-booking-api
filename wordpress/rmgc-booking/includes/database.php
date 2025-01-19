<?php
// [Previous code remains the same until rmgc_update_booking_status function]

function rmgc_update_booking_status($booking_id, $status, $assigned_time = null) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    $data = array(
        'status' => $status,
        'modified_by' => get_current_user_id()
    );
    $formats = array('%s', '%d');
    
    if ($assigned_time) {
        $data['assigned_time'] = $assigned_time;
        $formats[] = '%s';
    }
    
    $result = $wpdb->update(
        $table_name,
        $data,
        array('id' => $booking_id),
        $formats,
        array('%d')
    );
    
    if ($result === false) {
        error_log('RMGC Booking Update Error: ' . $wpdb->last_error);
        return false;
    }
    
    do_action('rmgc_booking_status_updated', $booking_id, $status, $assigned_time);
    return true;
}

// [Rest of the file remains the same]