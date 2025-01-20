<?php
function rmgc_get_bookings($filters = array()) {
    // [Previous function content remains the same]
}

function rmgc_insert_booking($booking_data) {
    global $wpdb;
    
    error_log('Attempting to insert booking: ' . print_r($booking_data, true));
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    
    $data = array(
        'date' => $booking_data['date'],
        'first_name' => $booking_data['firstName'],
        'last_name' => $booking_data['lastName'],
        'email' => $booking_data['email'],
        'phone' => $booking_data['phone'],
        'state' => isset($booking_data['state']) ? $booking_data['state'] : '',
        'country' => isset($booking_data['country']) ? $booking_data['country'] : '',
        'club_name' => isset($booking_data['clubName']) ? $booking_data['clubName'] : '',
        'club_state' => isset($booking_data['clubState']) ? $booking_data['clubState'] : '',
        'club_country' => isset($booking_data['clubCountry']) ? $booking_data['clubCountry'] : '',
        'handicap' => $booking_data['handicap'],
        'players' => $booking_data['players'],
        'time_preferences' => maybe_serialize($booking_data['timePreferences']),
        'status' => 'pending',
        'created_at' => current_time('mysql'),
        'last_modified' => current_time('mysql')
    );
    
    $format = array(
        '%s', // date
        '%s', // first_name
        '%s', // last_name
        '%s', // email
        '%s', // phone
        '%s', // state
        '%s', // country
        '%s', // club_name
        '%s', // club_state
        '%s', // club_country
        '%d', // handicap
        '%d', // players
        '%s', // time_preferences
        '%s', // status
        '%s', // created_at
        '%s'  // last_modified
    );
    
    error_log('Inserting with data: ' . print_r($data, true));
    error_log('Using format: ' . print_r($format, true));
    
    $result = $wpdb->insert($table_name, $data, $format);
    
    if ($result === false) {
        error_log('Database error: ' . $wpdb->last_error);
        return new WP_Error('db_insert_error', 'Could not insert booking into database: ' . $wpdb->last_error);
    }
    
    $booking_id = $wpdb->insert_id;
    error_log('Successfully inserted booking with ID: ' . $booking_id);
    
    return $booking_id;
}

function rmgc_update_booking_status($booking_id, $status, $assigned_time = null) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    $data = array(
        'status' => $status,
        'last_modified' => current_time('mysql'),
        'modified_by' => get_current_user_id()
    );
    $where = array('id' => $booking_id);
    
    if ($assigned_time) {
        $data['assigned_time'] = $assigned_time;
    }
    
    $result = $wpdb->update($table_name, $data, $where);
    
    if ($result === false) {
        error_log('Failed to update booking status: ' . $wpdb->last_error);
        return false;
    }
    
    return true;
}

function rmgc_add_booking_note($booking_id, $note) {
    global $wpdb;
    
    $result = $wpdb->insert(
        $wpdb->prefix . 'rmgc_booking_notes',
        array(
            'booking_id' => $booking_id,
            'note' => $note,
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql')
        ),
        array('%d', '%s', '%d', '%s')
    );
    
    return $result !== false ? $wpdb->insert_id : false;
}