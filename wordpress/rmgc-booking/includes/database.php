<?php
// [Previous content remains the same until rmgc_get_bookings function]

function rmgc_get_bookings($filters = array()) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    $where = array();
    $values = array();

    // [Previous filter code remains the same]

    $query = $wpdb->prepare(
        "SELECT b.*, 
                GROUP_CONCAT(n.note ORDER BY n.created_at DESC) as notes,
                GROUP_CONCAT(n.created_at ORDER BY n.created_at DESC) as note_dates,
                GROUP_CONCAT(n.created_by ORDER BY n.created_at DESC) as note_authors
         FROM $table_name b
         LEFT JOIN {$wpdb->prefix}rmgc_booking_notes n ON b.id = n.booking_id
         $where_clause
         GROUP BY b.id
         ORDER BY b.created_at DESC", // Changed from b.date to b.created_at
        $values
    );

    // [Rest of the function remains the same]
}

// Update the last_modified field when updating status
function rmgc_update_booking_status($booking_id, $status, $assigned_time = null) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    $data = array(
        'status' => $status,
        'modified_by' => get_current_user_id(),
        'last_modified' => current_time('mysql') // Add current timestamp
    );
    
    if ($assigned_time) {
        $data['assigned_time'] = $assigned_time;
    }
    
    $formats = array('%s', '%d', '%s'); // Add format for last_modified
    if ($assigned_time) {
        $formats[] = '%s';
    }
    
    $result = $wpdb->update(
        $table_name,
        $data,
        array('id' => $booking_id),
        $formats,
        array('%d')
    );
    
    if ($result !== false) {
        do_action('rmgc_booking_status_updated', $booking_id, $status, $assigned_time);
    } else {
        error_log('RMGC Booking Status Update Error: ' . $wpdb->last_error);
    }
    
    return $result !== false;
}

// [Rest of the file remains the same]