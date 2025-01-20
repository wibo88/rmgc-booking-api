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
        tee_time varchar(20),
        status varchar(20) DEFAULT 'pending',
        notes text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        last_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY idx_created_at (created_at),
        KEY idx_date (date),
        KEY idx_status (status)
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
        'status' => 'pending',
        'created_at' => current_time('mysql')
    );
    
    $result = $wpdb->insert($table_name, $data);
    
    if ($result === false) {
        return new WP_Error('db_insert_error', 'Could not insert booking into database: ' . $wpdb->last_error);
    }
    
    return $wpdb->insert_id;
}

// Function to get all bookings
function rmgc_get_bookings($args = array()) {
    global $wpdb;
    
    $defaults = array(
        'orderby' => 'created_at',
        'order' => 'DESC',
        'status' => 'all',
        'limit' => null,
        'offset' => 0
    );
    
    $args = wp_parse_args($args, $defaults);
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    
    $where = '';
    if ($args['status'] !== 'all') {
        $where = $wpdb->prepare(" WHERE status = %s", $args['status']);
    }
    
    $limit_clause = '';
    if (!is_null($args['limit'])) {
        $limit_clause = $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
    }
    
    $order_column = in_array($args['orderby'], array('created_at', 'date', 'status')) ? $args['orderby'] : 'created_at';
    $order_dir = $args['order'] === 'ASC' ? 'ASC' : 'DESC';
    
    $sql = "SELECT * FROM $table_name $where ORDER BY $order_column $order_dir, id DESC$limit_clause";
    
    $results = $wpdb->get_results($sql, ARRAY_A);
    
    // Format the results
    foreach ($results as &$booking) {
        $booking['timePreferences'] = maybe_unserialize($booking['time_preferences']);
        $booking['notes'] = !empty($booking['notes']) ? maybe_unserialize($booking['notes']) : array();
        
        // Convert snake_case to camelCase for JavaScript
        $booking['firstName'] = $booking['first_name'];
        $booking['lastName'] = $booking['last_name'];
        $booking['clubName'] = $booking['club_name'];
        $booking['clubState'] = $booking['club_state'];
        $booking['clubCountry'] = $booking['club_country'];
        $booking['teeTime'] = $booking['tee_time'];
    }
    
    return $results;
}

// Function to update booking status
function rmgc_update_booking_status($booking_id, $status, $tee_time = null) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    
    $data = array(
        'status' => $status,
        'last_modified' => current_time('mysql')
    );
    
    $format = array('%s', '%s');
    
    if ($status === 'approved' && !empty($tee_time)) {
        $data['tee_time'] = $tee_time;
        $format[] = '%s';
    }
    
    $result = $wpdb->update(
        $table_name,
        $data,
        array('id' => $booking_id),
        $format,
        array('%d')
    );
    
    return $result !== false;
}

// Function to add a note to a booking
function rmgc_add_booking_note($booking_id, $note_content) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    
    // Get existing notes
    $booking = $wpdb->get_row($wpdb->prepare(
        "SELECT notes FROM $table_name WHERE id = %d",
        $booking_id
    ));
    
    // Initialize notes array
    $notes = !empty($booking->notes) ? maybe_unserialize($booking->notes) : array();
    
    // Add new note
    $notes[] = array(
        'date' => current_time('mysql'),
        'content' => $note_content,
        'user_id' => get_current_user_id()
    );
    
    // Update booking
    $result = $wpdb->update(
        $table_name,
        array(
            'notes' => maybe_serialize($notes),
            'last_modified' => current_time('mysql')
        ),
        array('id' => $booking_id),
        array('%s', '%s'),
        array('%d')
    );
    
    return $result !== false;
}

// Function to get a single booking
function rmgc_get_booking($booking_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    
    $booking = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $booking_id
    ), ARRAY_A);
    
    if (!$booking) {
        return null;
    }
    
    // Format the booking data
    $booking['timePreferences'] = maybe_unserialize($booking['time_preferences']);
    $booking['notes'] = !empty($booking['notes']) ? maybe_unserialize($booking['notes']) : array();
    
    // Convert snake_case to camelCase
    $booking['firstName'] = $booking['first_name'];
    $booking['lastName'] = $booking['last_name'];
    $booking['clubName'] = $booking['club_name'];
    $booking['clubState'] = $booking['club_state'];
    $booking['clubCountry'] = $booking['club_country'];
    $booking['teeTime'] = $booking['tee_time'];
    
    return $booking;
}