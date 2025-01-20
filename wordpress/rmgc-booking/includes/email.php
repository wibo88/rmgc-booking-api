<?php
function rmgc_send_booking_notification($booking) {
    // Get admin emails from settings
    $admin_emails = get_option('rmgc_admin_notification_emails', get_option('admin_email'));
    
    // Email configuration
    $from_name = get_option('rmgc_email_from_name', 'Royal Melbourne Golf Club');
    $from_email = get_option('rmgc_email_from_address', get_option('admin_email'));
    
    // Format date
    $date = new DateTime($booking['date']);
    $formatted_date = $date->format('l, j F Y');
    
    // Format time preferences
    $time_prefs = array_map(function($pref) {
        return ucwords(str_replace('_', ' ', $pref));
    }, $booking['timePreferences']);
    $formatted_time_prefs = implode(', ', $time_prefs);
    
    // Email headers
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        sprintf('From: %s <%s>', $from_name, $from_email)
    );
    
    // Admin email content
    $admin_subject = 'New Booking Request - ' . $booking['firstName'] . ' ' . $booking['lastName'];
    $admin_message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #d3bc8d; color: #333; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .footer { text-align: center; padding: 20px; font-size: 0.9em; color: #666; }
            .label { font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Booking Request</h2>
            </div>
            
            <div class='content'>
                <h3>Visitor Details</h3>
                <p><span class='label'>Name:</span> {$booking['firstName']} {$booking['lastName']}</p>
                <p><span class='label'>Email:</span> {$booking['email']}</p>
                <p><span class='label'>Phone:</span> {$booking['phone']}</p>
                <p><span class='label'>Location:</span> {$booking['state']}, {$booking['country']}</p>
                
                <h3>Golf Details</h3>
                <p><span class='label'>Club:</span> {$booking['clubName']}</p>
                <p><span class='label'>Handicap:</span> {$booking['handicap']}</p>
                <p><span class='label'>Club Location:</span> {$booking['clubState']}, {$booking['clubCountry']}</p>
                
                <h3>Booking Details</h3>
                <p><span class='label'>Date:</span> {$formatted_date}</p>
                <p><span class='label'>Number of Players:</span> {$booking['players']}</p>
                <p><span class='label'>Time Preferences:</span> {$formatted_time_prefs}</p>
            </div>
            
            <div class='footer'>
                <p>This is an automated message from your website's booking system.</p>
                <p>To manage this booking, please visit the admin dashboard.</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Send admin notifications
    $admin_emails = array_map('trim', explode(',', $admin_emails));
    foreach ($admin_emails as $admin_email) {
        if (is_email($admin_email)) {
            $sent = wp_mail($admin_email, $admin_subject, $admin_message, $headers);
            rmgc_log_error('Admin email attempt', array(
                'email' => $admin_email,
                'success' => $sent ? 'yes' : 'no'
            ));
        }
    }
    
    // Guest email content
    $visitor_subject = "Booking Request Received - Royal Melbourne Golf Club";
    $visitor_message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #d3bc8d; color: #333; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .footer { text-align: center; padding: 20px; font-size: 0.9em; color: #666; }
            .important { font-weight: bold; }
            ul { margin-left: 20px; padding-left: 0; }
            li { margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Booking Request Received</h2>
            </div>
            
            <div class='content'>
                <p>Dear {$booking['firstName']},</p>
                
                <p>Thank you for your booking request at Royal Melbourne Golf Club. We have received your request for:</p>
                
                <p>
                    <strong>Date:</strong> {$formatted_date}<br>
                    <strong>Number of Players:</strong> {$booking['players']}<br>
                    <strong>Time Preferences:</strong> {$formatted_time_prefs}
                </p>
                
                <p>Our team will review your request and contact you shortly to confirm your booking.</p>
                
                <p class='important'>Please note the following important information:</p>
                <ul>
                    <li>Bookings are subject to availability</li>
                    <li>Please arrive 30 minutes before your confirmed tee time</li>
                    <li>Proper golf attire is required (no denim, t-shirts, or cargo shorts)</li>
                    <li>You will need to present proof of your current handicap on the day</li>
                    <li>Green fees must be paid upon arrival</li>
                </ul>
                
                <p class='important'>If you need to make any changes to your booking request or have any questions, please contact our Pro Shop:</p>
                <p>
                    Phone: (03) 9598 6755<br>
                    Email: proshop@royalmelbourne.com.au
                </p>
            </div>
            
            <div class='footer'>
                <p><strong>Royal Melbourne Golf Club</strong></p>
                <p>Thank you for choosing to play with us</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Send guest confirmation
    $sent = wp_mail($booking['email'], $visitor_subject, $visitor_message, $headers);
    rmgc_log_error('Guest email attempt', array(
        'email' => $booking['email'],
        'success' => $sent ? 'yes' : 'no'
    ));
}

// Send status update notifications
function rmgc_send_status_notification($booking_id, $status) {
    global $wpdb;
    
    // Get booking details
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    $booking = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $booking_id
    ), ARRAY_A);
    
    if (!$booking) {
        return false;
    }
    
    // Email configuration
    $from_name = get_option('rmgc_email_from_name', 'Royal Melbourne Golf Club');
    $from_email = get_option('rmgc_email_from_address', get_option('admin_email'));
    
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        sprintf('From: %s <%s>', $from_name, $from_email)
    );
    
    // Format date
    $date = new DateTime($booking['date']);
    $formatted_date = $date->format('l, j F Y');
    
    // Format time preferences
    $time_prefs = maybe_unserialize($booking['time_preferences']);
    $formatted_time_prefs = implode(', ', array_map(function($pref) {
        return ucwords(str_replace('_', ' ', $pref));
    }, $time_prefs));
    
    // Prepare email content based on status
    if ($status === 'approved') {
        $subject = "Booking Confirmed - Royal Melbourne Golf Club";
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #d3bc8d; color: #333; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .footer { text-align: center; padding: 20px; font-size: 0.9em; color: #666; }
                .important { font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Booking Confirmed</h2>
                </div>
                
                <div class='content'>
                    <p>Dear {$booking['first_name']},</p>
                    
                    <p>We are pleased to confirm your booking at Royal Melbourne Golf Club:</p>
                    
                    <p>
                        <strong>Date:</strong> {$formatted_date}<br>
                        <strong>Number of Players:</strong> {$booking['players']}<br>
                        <strong>Time Preferences:</strong> {$formatted_time_prefs}
                    </p>
                    
                    <p class='important'>Important Reminders:</p>
                    <ul>
                        <li>Please arrive 30 minutes before your tee time</li>
                        <li>Proper golf attire is required</li>
                        <li>Bring proof of your current handicap</li>
                        <li>Green fees are payable upon arrival</li>
                    </ul>
                    
                    <p>If you need to make any changes or have questions, please contact our Pro Shop:</p>
                    <p>
                        Phone: (03) 9598 6755<br>
                        Email: proshop@royalmelbourne.com.au
                    </p>
                </div>
                
                <div class='footer'>
                    <p><strong>Royal Melbourne Golf Club</strong></p>
                    <p>We look forward to welcoming you</p>
                </div>
            </div>
        </body>
        </html>";
    } else {
        $subject = "Booking Update - Royal Melbourne Golf Club";
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #d3bc8d; color: #333; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .footer { text-align: center; padding: 20px; font-size: 0.9em; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Booking Update</h2>
                </div>
                
                <div class='content'>
                    <p>Dear {$booking['first_name']},</p>
                    
                    <p>We regret to inform you that we are unable to accommodate your booking request for:</p>
                    
                    <p>
                        <strong>Date:</strong> {$formatted_date}<br>
                        <strong>Number of Players:</strong> {$booking['players']}<br>
                        <strong>Time Preferences:</strong> {$formatted_time_prefs}
                    </p>
                    
                    <p>If you would like to explore alternative dates or have any questions, please contact our Pro Shop:</p>
                    <p>
                        Phone: (03) 9598 6755<br>
                        Email: proshop@royalmelbourne.com.au
                    </p>
                </div>
                
                <div class='footer'>
                    <p><strong>Royal Melbourne Golf Club</strong></p>
                    <p>Thank you for your interest in playing with us</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    // Send notification
    $sent = wp_mail($booking['email'], $subject, $message, $headers);
    rmgc_log_error('Status notification sent', array(
        'booking_id' => $booking_id,
        'status' => $status,
        'email' => $booking['email'],
        'success' => $sent ? 'yes' : 'no'
    ));
    
    return $sent;
}