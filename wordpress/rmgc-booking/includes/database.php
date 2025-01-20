<?php
function rmgc_get_bookings($filters = array()) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    $notes_table = $wpdb->prefix . 'rmgc_booking_notes';
    
    // Debug log
    error_log('Getting bookings with filters: ' . print_r($filters, true));
    
    $where = array();
    $values = array();

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
    
    // Basic query to test
    $basic_query = "SELECT * FROM $table_name $where_clause ORDER BY date DESC";
    
    if (!empty($values)) {
        $basic_query = $wpdb->prepare($basic_query, $values);
    }
    
    // Debug log the query
    error_log('Basic query: ' . $basic_query);
    
    // Test basic query first
    $basic_results = $wpdb->get_results($basic_query, ARRAY_A);
    error_log('Basic query results count: ' . count($basic_results));
    
    // If basic query works, try full query with notes
    if ($basic_results) {
        $query = "SELECT b.*, 
                    GROUP_CONCAT(n.note ORDER BY n.created_at DESC) as notes,
                    GROUP_CONCAT(n.created_at ORDER BY n.created_at DESC) as note_dates,
                    GROUP_CONCAT(n.created_by ORDER BY n.created_at DESC) as note_authors
                FROM $table_name b
                LEFT JOIN $notes_table n ON b.id = n.booking_id
                $where_clause
                GROUP BY b.id
                ORDER BY b.date DESC";
        
        if (!empty($values)) {
            $query = $wpdb->prepare($query, $values);
        }
        
        // Debug log the full query
        error_log('Full query: ' . $query);
        
        $results = $wpdb->get_results($query, ARRAY_A);
        error_log('Full query results count: ' . count($results));
        
        if ($results === false) {
            error_log('MySQL Error: ' . $wpdb->last_error);
            return array();
        }
        
        // Format the results
        foreach ($results as &$booking) {
            // Debug each booking
            error_log('Processing booking: ' . print_r($booking, true));
            
            $booking['timePreferences'] = !empty($booking['time_preferences']) ? 
                maybe_unserialize($booking['time_preferences']) : array();
            
            if (!empty($booking['notes'])) {
                $notes = explode(',', $booking['notes']);
                $dates = explode(',', $booking['note_dates']);
                $authors = explode(',', $booking['note_authors']);
                
                $booking['notes'] = array_map(function($note, $date, $author) {
                    $user = get_userdata($author);
                    return array(
                        'note' => $note,
                        'date' => $date,
                        'author' => $user ? $user->display_name : 'Unknown'
                    );
                }, $notes, $dates, $authors);
            } else {
                $booking['notes'] = array();
            }
            
            // Standard field mappings
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
    
    return array();
}

// Rest of the file remains the same