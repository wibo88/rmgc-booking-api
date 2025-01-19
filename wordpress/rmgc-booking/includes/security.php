<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rate limiting implementation
 */
function rmgc_check_rate_limit($email) {
    if (empty($email)) {
        return new WP_Error('invalid_email', 'Invalid email address');
    }

    $transient_key = 'rmgc_rate_limit_' . md5($email);
    $attempt_count = get_transient($transient_key);
    
    // Allow 3 attempts per hour
    if ($attempt_count && $attempt_count >= 3) {
        return new WP_Error(
            'rate_limit_exceeded',
            'Too many booking attempts. Please try again later.'
        );
    }
    
    // Increment attempt count
    if ($attempt_count === false) {
        set_transient($transient_key, 1, HOUR_IN_SECONDS);
    } else {
        set_transient($transient_key, $attempt_count + 1, HOUR_IN_SECONDS);
    }
    
    return true;
}

/**
 * Data sanitization for booking data
 */
function rmgc_sanitize_booking_data($booking_data) {
    return array(
        'date' => sanitize_text_field($booking_data['date']),
        'firstName' => sanitize_text_field($booking_data['firstName']),
        'lastName' => sanitize_text_field($booking_data['lastName']),
        'email' => sanitize_email($booking_data['email']),
        'phone' => sanitize_text_field($booking_data['phone']),
        'state' => sanitize_text_field($booking_data['state']),
        'country' => sanitize_text_field($booking_data['country']),
        'clubName' => sanitize_text_field($booking_data['clubName']),
        'clubState' => sanitize_text_field($booking_data['clubState']),
        'clubCountry' => sanitize_text_field($booking_data['clubCountry']),
        'handicap' => absint($booking_data['handicap']),
        'players' => absint($booking_data['players']),
        'timePreferences' => array_map('sanitize_text_field', $booking_data['timePreferences']),
    );
}

/**
 * Validate booking data
 */
function rmgc_validate_booking_data($booking_data) {
    $errors = new WP_Error();
    
    // Required fields
    $required_fields = array(
        'date' => 'Booking date',
        'firstName' => 'First name',
        'lastName' => 'Last name',
        'email' => 'Email address',
        'handicap' => 'Handicap',
        'players' => 'Number of players',
        'timePreferences' => 'Time preferences'
    );
    
    foreach ($required_fields as $field => $label) {
        if (empty($booking_data[$field])) {
            $errors->add('required_field', sprintf('%s is required', $label));
        }
    }
    
    // Email validation
    if (!is_email($booking_data['email'])) {
        $errors->add('invalid_email', 'Please enter a valid email address');
    }
    
    // Handicap validation
    if ($booking_data['handicap'] < 0 || $booking_data['handicap'] > 24) {
        $errors->add('invalid_handicap', 'Handicap must be between 0 and 24');
    }
    
    // Players validation
    if ($booking_data['players'] < 1 || $booking_data['players'] > 4) {
        $errors->add('invalid_players', 'Number of players must be between 1 and 4');
    }
    
    // Date validation
    $booking_date = new DateTime($booking_data['date']);
    $day_of_week = $booking_date->format('N'); // 1 (Mon) through 7 (Sun)
    
    if (!in_array($day_of_week, array(1, 2, 5))) { // Monday, Tuesday, Friday
        $errors->add('invalid_date', 'Bookings are only available on Monday, Tuesday, and Friday');
    }
    
    return $errors->has_errors() ? $errors : true;
}

/**
 * Error logging
 */
function rmgc_log_error($error_message, $context = array()) {
    $log_dir = WP_CONTENT_DIR . '/rmgc-logs';
    
    // Create logs directory if it doesn't exist
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
        
        // Create .htaccess to prevent direct access
        file_put_contents($log_dir . '/.htaccess', 'deny from all');
    }
    
    $timestamp = current_time('mysql');
    $log_message = sprintf(
        "[%s] %s\nContext: %s\n",
        $timestamp,
        $error_message,
        json_encode($context)
    );
    
    error_log($log_message, 3, $log_dir . '/rmgc-errors.log');
}

/**
 * Security headers
 */
function rmgc_add_security_headers() {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Only add HSTS if SSL is detected
    if (is_ssl()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}