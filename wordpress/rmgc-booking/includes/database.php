<?php
function rmgc_create_bookings_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        date date NOT NULL,
        first_name varchar(100) NOT NULL,
        last_name varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        phone varchar(50),
        state varchar(100),
        country varchar(100),
        club_name varchar(100),
        club_state varchar(100),
        club_country varchar(100),
        handicap int(3) NOT NULL,
        players int(1) NOT NULL,
        time_preferences text NOT NULL,
        status varchar(20) DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Function to insert a new booking
function rmgc_insert_booking($booking_data) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    
    $data = array(
        'date' => $booking_data['date'],
        'first_name' => $booking_data['firstName'],
        'last_name' => $booking_data['lastName'],
        'email' => $booking_data['email'],
        'phone' => $booking_data['phone'],
        'state' => $booking_data['state'],
        'country' => $booking_data['country'],
        'club_name' => $booking_data['clubName'],
        'club_state' => $booking_data['clubState'],
        'club_country' => $booking_data['clubCountry'],
        'handicap' => $booking_data['handicap'],
        'players' => $booking_data['players'],
        'time_preferences' => maybe_serialize($booking_data['timePreferences']),
        'status' => 'pending'
    );
    
    $result = $wpdb->insert($table_name, $data);
    
    if ($result === false) {
        return new WP_Error('db_insert_error', 'Could not insert booking into database.', $wpdb->last_error);
    }
    
    return $wpdb->insert_id;
}

// Function to get all bookings
function rmgc_get_bookings() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date DESC", ARRAY_A);
    
    // Format the time preferences for each booking
    foreach ($results as &$booking) {
        $booking['timePreferences'] = maybe_unserialize($booking['time_preferences']);
        // Convert snake_case to camelCase for JavaScript
        $booking['firstName'] = $booking['first_name'];
        $booking['lastName'] = $booking['last_name'];
        $booking['clubName'] = $booking['club_name'];
        $booking['clubState'] = $booking['club_state'];
        $booking['clubCountry'] = $booking['club_country'];
    }
    
    return $results;
}

// Function to update booking status
function rmgc_update_booking_status($booking_id, $status) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    
    $result = $wpdb->update(
        $table_name,
        array('status' => $status),
        array('id' => $booking_id),
        array('%s'),
        array('%d')
    );
    
    return $result !== false;
}
