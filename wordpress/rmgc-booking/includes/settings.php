<?php

function rmgc_send_notification($booking_data) {
    $to = get_option('rmgc_notification_emails');
    $subject = 'New Booking Request - Royal Melbourne Golf Club';
    
    $message = "A new booking request has been received:\n\n";
    $message .= "Date: " . $booking_data['date'] . "\n";
    $message .= "Players: " . $booking_data['players'] . "\n";
    $message .= "Contact Details:\n";
    $message .= "Name: " . $booking_data['firstName'] . " " . $booking_data['lastName'] . "\n";
    $message .= "Email: " . $booking_data['email'] . "\n";
    $message .= "Phone: " . $booking_data['phone'] . "\n";
    $message .= "State: " . $booking_data['state'] . "\n";
    $message .= "Country: " . $booking_data['country'] . "\n\n";
    $message .= "Golf Details:\n";
    $message .= "Club Name: " . $booking_data['clubName'] . "\n";
    $message .= "Handicap: " . $booking_data['handicap'] . "\n";
    $message .= "Club State: " . $booking_data['clubState'] . "\n";
    $message .= "Club Country: " . $booking_data['clubCountry'] . "\n";
    
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    
    wp_mail($to, $subject, $message, $headers);
}

function rmgc_settings_page() {
    if (isset($_POST['rmgc_save_settings'])) {
        update_option('rmgc_api_url', sanitize_text_field($_POST['rmgc_api_url']));
        update_option('rmgc_api_key', sanitize_text_field($_POST['rmgc_api_key']));
        update_option('rmgc_notification_emails', sanitize_text_field($_POST['rmgc_notification_emails']));
        update_option('rmgc_recaptcha_site_key', sanitize_text_field($_POST['rmgc_recaptcha_site_key']));
        update_option('rmgc_recaptcha_secret_key', sanitize_text_field($_POST['rmgc_recaptcha_secret_key']));
        echo '<div class="updated"><p>Settings saved!</p></div>';
    }
    
    ?>
    <div class="wrap">
        <h2>RMGC Booking System Settings</h2>
        
        <form method="post" class="rmgc-settings-form">
            <table class="form-table">
                <tr>
                    <th><label for="rmgc_api_url">API URL:</label></th>
                    <td>
                        <input type="text" id="rmgc_api_url" name="rmgc_api_url" 
                               value="<?php echo esc_attr(get_option('rmgc_api_url')); ?>" 
                               class="regular-text">
                        <p class="description">Example: http://localhost:3000</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmgc_api_key">API Key:</label></th>
                    <td>
                        <input type="text" id="rmgc_api_key" name="rmgc_api_key" 
                               value="<?php echo esc_attr(get_option('rmgc_api_key')); ?>" 
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="rmgc_notification_emails">Notification Emails:</label></th>
                    <td>
                        <input type="text" id="rmgc_notification_emails" name="rmgc_notification_emails" 
                               value="<?php echo esc_attr(get_option('rmgc_notification_emails')); ?>" 
                               class="regular-text">
                        <p class="description">Comma-separated list of email addresses to receive booking notifications</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmgc_recaptcha_site_key">reCAPTCHA Site Key:</label></th>
                    <td>
                        <input type="text" id="rmgc_recaptcha_site_key" name="rmgc_recaptcha_site_key" 
                               value="<?php echo esc_attr(get_option('rmgc_recaptcha_site_key')); ?>" 
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="rmgc_recaptcha_secret_key">reCAPTCHA Secret Key:</label></th>
                    <td>
                        <input type="text" id="rmgc_recaptcha_secret_key" name="rmgc_recaptcha_secret_key" 
                               value="<?php echo esc_attr(get_option('rmgc_recaptcha_secret_key')); ?>" 
                               class="regular-text">
                        <p class="description">Get your reCAPTCHA keys from <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA</a></p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="rmgc_save_settings" class="button-primary" value="Save Settings">
            </p>
        </form>
        
        <h3>Shortcode</h3>
        <p>Use this shortcode to display the booking form: <code>[rmgc_booking_form]</code></p>
    </div>
    <?php
}