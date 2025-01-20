<?php
function rmgc_send_booking_notification($booking) {
    // Get admin notification emails (support multiple recipients)
    $admin_emails = get_option('rmgc_admin_notification_emails', get_option('admin_email'));
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
    
    // Get custom email subjects
    $admin_subject = get_option('rmgc_admin_email_subject', 'New Booking Request - RMGC');
    $admin_subject = str_replace(
        array('{first_name}', '{last_name}'),
        array($booking['firstName'], $booking['lastName']),
        $admin_subject
    );
    
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        sprintf('From: %s <%s>', $from_name, $from_email)
    );
    
    $admin_message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #005b94; color: white; padding: 20px; text-align: center; }
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
            </div>
        </div>
    </body>
    </html>";
    
    // Send to admin(s) - handle multiple recipients
    $admin_emails = array_map('trim', explode(',', $admin_emails));
    foreach ($admin_emails as $admin_email) {
        if (is_email($admin_email)) {
            wp_mail($admin_email, $admin_subject, $admin_message, $headers);
        }
    }
    
    // Send confirmation to visitor
    $visitor_subject = get_option('rmgc_guest_email_subject', 'Booking Request Received - Royal Melbourne Golf Club');
    $visitor_message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #005b94; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .footer { text-align: center; padding: 20px; font-size: 0.9em; color: #666; }
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
                
                <ul>
                    <li>Date: {$formatted_date}</li>
                    <li>Players: {$booking['players']}</li>
                    <li>Time Preferences: {$formatted_time_prefs}</li>
                </ul>
                
                <p>Our team will review your request and contact you shortly to confirm your booking.</p>
                
                <p>Please note:</p>
                <ul>
                    <li>Bookings are subject to availability</li>
                    <li>We require players to arrive 30 minutes before their tee time</li>
                    <li>Proper golf attire is required</li>
                    <li>Proof of handicap may be required on the day</li>
                </ul>
            </div>
            
            <div class='footer'>
                <p>Royal Melbourne Golf Club</p>
                <p>Thank you for choosing to play with us</p>
            </div>
        </div>
    </body>
    </html>";
    
    wp_mail($booking['email'], $visitor_subject, $visitor_message, $headers);
    
    // Log email attempts
    rmgc_log_error('Email notifications sent', array(
        'admin_emails' => $admin_emails,
        'guest_email' => $booking['email'],
        'date' => $formatted_date
    ));
}