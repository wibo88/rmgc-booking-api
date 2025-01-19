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
        admin_notes text,
        assigned_time time DEFAULT NULL,
        last_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        modified_by bigint(20) DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY idx_status (status),
        KEY idx_date (date),
        KEY idx_email (email)
    ) $charset_collate;

    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rmgc_booking_notes (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        booking_id bigint(20) NOT NULL,
        note text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        created_by bigint(20) NOT NULL,
        PRIMARY KEY  (id),
        KEY booking_id (booking_id),
        FOREIGN KEY (booking_id) REFERENCES $table_name(id) ON DELETE CASCADE
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

// Function to get all bookings with optional filters
function rmgc_get_bookings($filters = array()) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    $where = array();
    $values = array();

    // Build WHERE clause based on filters
    if (!empty($filters['status'])) {
        $where[] = 'status = %s';
        $values[] = $filters['status'];
    }

    if (!empty($filters['date_from'])) {
        $where[] = 'date >= %s';
        $values[] = $filters['date_from'];
    }

    if (!empty($filters['date_to'])) {
        $where[] = 'date <= %s';
        $values[] = $filters['date_to'];
    }

    if (!empty($filters['search'])) {
        $where[] = '(
            first_name LIKE %s 
            OR last_name LIKE %s 
            OR email LIKE %s 
            OR club_name LIKE %s
        )';
        $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
        $values = array_merge($values, array($search_term, $search_term, $search_term, $search_term));
    }

    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $query = $wpdb->prepare(
        "SELECT b.*, 
                GROUP_CONCAT(n.note ORDER BY n.created_at DESC) as notes,
                GROUP_CONCAT(n.created_at ORDER BY n.created_at DESC) as note_dates,
                GROUP_CONCAT(n.created_by ORDER BY n.created_at DESC) as note_authors
         FROM $table_name b
         LEFT JOIN {$wpdb->prefix}rmgc_booking_notes n ON b.id = n.booking_id
         $where_clause
         GROUP BY b.id
         ORDER BY b.date DESC",
        $values
    );

    $results = $wpdb->get_results($query, ARRAY_A);
    
    // Format the results
    foreach ($results as &$booking) {
        $booking['timePreferences'] = maybe_unserialize($booking['time_preferences']);
        
        // Format notes
        if (!empty($booking['notes'])) {
            $notes = explode(',', $booking['notes']);
            $dates = explode(',', $booking['note_dates']);
            $authors = explode(',', $booking['note_authors']);
            
            $booking['notes'] = array_map(function($note, $date, $author) {
                return array(
                    'note' => $note,
                    'date' => $date,
                    'author' => get_userdata($author)->display_name
                );
            }, $notes, $dates, $authors);
        } else {
            $booking['notes'] = array();
        }
        
        // Convert snake_case to camelCase for JavaScript
        $booking['firstName'] = $booking['first_name'];
        $booking['lastName'] = $booking['last_name'];
        $booking['clubName'] = $booking['club_name'];
        $booking['clubState'] = $booking['club_state'];
        $booking['clubCountry'] = $booking['club_country'];
        $booking['assignedTime'] = $booking['assigned_time'];
        $booking['createdAt'] = $booking['created_at'];
        $booking['lastModified'] = $booking['last_modified'];
        $booking['modifiedBy'] = $booking['modified_by'];
    }
    
    return $results;
}

// Function to update booking status
function rmgc_update_booking_status($booking_id, $status, $assigned_time = null) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    $data = array(
        'status' => $status,
        'modified_by' => get_current_user_id()
    );
    
    if ($assigned_time) {
        $data['assigned_time'] = $assigned_time;
    }
    
    $result = $wpdb->update(
        $table_name,
        $data,
        array('id' => $booking_id),
        array('%s', '%d'),
        array('%d')
    );
    
    if ($result !== false) {
        do_action('rmgc_booking_status_updated', $booking_id, $status, $assigned_time);
    }
    
    return $result !== false;
}

// Function to add a note to a booking
function rmgc_add_booking_note($booking_id, $note) {
    global $wpdb;
    
    $result = $wpdb->insert(
        $wpdb->prefix . 'rmgc_booking_notes',
        array(
            'booking_id' => $booking_id,
            'note' => $note,
            'created_by' => get_current_user_id()
        ),
        array('%d', '%s', '%d')
    );
    
    return $result !== false ? $wpdb->insert_id : false;
}

// Function to get a single booking
function rmgc_get_booking($booking_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    $query = $wpdb->prepare(
        "SELECT b.*, 
                GROUP_CONCAT(n.note ORDER BY n.created_at DESC) as notes,
                GROUP_CONCAT(n.created_at ORDER BY n.created_at DESC) as note_dates,
                GROUP_CONCAT(n.created_by ORDER BY n.created_at DESC) as note_authors
         FROM $table_name b
         LEFT JOIN {$wpdb->prefix}rmgc_booking_notes n ON b.id = n.booking_id
         WHERE b.id = %d
         GROUP BY b.id",
        $booking_id
    );
    
    $booking = $wpdb->get_row($query, ARRAY_A);
    
    if ($booking) {
        $booking['timePreferences'] = maybe_unserialize($booking['time_preferences']);
        
        // Format notes
        if (!empty($booking['notes'])) {
            $notes = explode(',', $booking['notes']);
            $dates = explode(',', $booking['note_dates']);
            $authors = explode(',', $booking['note_authors']);
            
            $booking['notes'] = array_map(function($note, $date, $author) {
                return array(
                    'note' => $note,
                    'date' => $date,
                    'author' => get_userdata($author)->display_name
                );
            }, $notes, $dates, $authors);
        } else {
            $booking['notes'] = array();
        }
        
        // Convert snake_case to camelCase
        $booking['firstName'] = $booking['first_name'];
        $booking['lastName'] = $booking['last_name'];
        $booking['clubName'] = $booking['club_name'];
        $booking['clubState'] = $booking['club_state'];
        $booking['clubCountry'] = $booking['club_country'];
        $booking['assignedTime'] = $booking['assigned_time'];
        $booking['createdAt'] = $booking['created_at'];
        $booking['lastModified'] = $booking['last_modified'];
        $booking['modifiedBy'] = $booking['modified_by'];
    }
    
    return $booking;
}