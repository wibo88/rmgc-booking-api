<?php
function rmgc_send_booking_notification($booking) {
    $to = get_option('rmgc_notification_email', get_option('admin_email'));
    $from_name = get_option('rmgc_email_from_name', get_bloginfo('name'));
    $from_email = get_option('rmgc_email_from_address', get_option('admin_email'));
    
    // Format date
    $date = new DateTime($booking['date']);
    $formatted_date = $date->format('l, j F Y');
    
    // Format time preferences
    $time_prefs = array_map(function($pref) {
        return ucwords(str_replace('_', ' ', $pref));
    }, $booking['timePreferences']);
    $formatted_time_prefs = implode(', ', $time_prefs);
    
    $subject = get_option('rmgc_admin_email_subject', 'New Booking Request - [name]');
    $subject = str_replace('[name]', "{$booking['firstName']} {$booking['lastName']}", $subject);
    
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        sprintf('From: %s <%s>', $from_name, $from_email)
    );
    
    // Admin notification
    $message = rmgc_get_email_template('admin_notification', array(
        'booking' => $booking,
        'formatted_date' => $formatted_date,
        'formatted_time_prefs' => $formatted_time_prefs
    ));
    
    // Send to admin(s)
    $admin_emails = explode(',', $to);
    foreach ($admin_emails as $admin_email) {
        wp_mail(trim($admin_email), $subject, $message, $headers);
    }
    
    // Guest confirmation
    $guest_subject = get_option('rmgc_guest_email_subject', 'Booking Request Received - Royal Melbourne Golf Club');
    
    $guest_message = rmgc_get_email_template('guest_confirmation', array(
        'booking' => $booking,
        'formatted_date' => $formatted_date,
        'formatted_time_prefs' => $formatted_time_prefs
    ));
    
    wp_mail($booking['email'], $guest_subject, $guest_message, $headers);
}

function rmgc_send_booking_status_notification($booking_id, $status) {
    $booking = rmgc_get_booking($booking_id);
    if (!$booking) {
        return false;
    }
    
    $from_name = get_option('rmgc_email_from_name', get_bloginfo('name'));
    $from_email = get_option('rmgc_email_from_address', get_option('admin_email'));
    
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        sprintf('From: %s <%s>', $from_name, $from_email)
    );
    
    $date = new DateTime($booking['date']);
    $formatted_date = $date->format('l, j F Y');
    
    if ($status === 'approved') {
        $subject = get_option('rmgc_approval_email_subject', 'Booking Confirmed - Royal Melbourne Golf Club');
        $message = rmgc_get_email_template('booking_approved', array(
            'booking' => $booking,
            'formatted_date' => $formatted_date,
            'assigned_time' => $booking['assignedTime'] ? date('g:i A', strtotime($booking['assignedTime'])) : null
        ));
    } else if ($status === 'rejected') {
        $subject = get_option('rmgc_rejection_email_subject', 'Booking Update - Royal Melbourne Golf Club');
        $message = rmgc_get_email_template('booking_rejected', array(
            'booking' => $booking,
            'formatted_date' => $formatted_date
        ));
    } else {
        return false;
    }
    
    return wp_mail($booking['email'], $subject, $message, $headers);
}

function rmgc_get_email_template($template_name, $vars) {
    ob_start();
    
    switch ($template_name) {
        case 'admin_notification':
            include(plugin_dir_path(__FILE__) . '../templates/emails/admin-notification.php');
            break;
        case 'guest_confirmation':
            include(plugin_dir_path(__FILE__) . '../templates/emails/guest-confirmation.php');
            break;
        case 'booking_approved':
            include(plugin_dir_path(__FILE__) . '../templates/emails/booking-approved.php');
            break;
        case 'booking_rejected':
            include(plugin_dir_path(__FILE__) . '../templates/emails/booking-rejected.php');
            break;
    }
    
    return ob_get_clean();
}

// Register hooks for status changes
add_action('rmgc_booking_status_updated', 'rmgc_handle_booking_status_change', 10, 3);

function rmgc_handle_booking_status_change($booking_id, $status, $assigned_time) {
    if (in_array($status, array('approved', 'rejected'))) {
        rmgc_send_booking_status_notification($booking_id, $status);
    }
}

// Function to get email content for debugging
function rmgc_get_email_preview($template_name, $booking_id) {
    if (!current_user_can('manage_options')) {
        return false;
    }
    
    $booking = rmgc_get_booking($booking_id);
    if (!$booking) {
        return false;
    }
    
    $date = new DateTime($booking['date']);
    $formatted_date = $date->format('l, j F Y');
    
    $time_prefs = array_map(function($pref) {
        return ucwords(str_replace('_', ' ', $pref));
    }, $booking['timePreferences']);
    $formatted_time_prefs = implode(', ', $time_prefs);
    
    return rmgc_get_email_template($template_name, array(
        'booking' => $booking,
        'formatted_date' => $formatted_date,
        'formatted_time_prefs' => $formatted_time_prefs,
        'assigned_time' => $booking['assignedTime'] ? date('g:i A', strtotime($booking['assignedTime'])) : null
    ));
}