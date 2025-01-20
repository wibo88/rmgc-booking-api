<?php
// Register settings
function rmgc_register_settings() {
    // Email Settings
    register_setting('rmgc_settings', 'rmgc_admin_notification_emails');
    register_setting('rmgc_settings', 'rmgc_admin_email_subject');
    register_setting('rmgc_settings', 'rmgc_guest_email_subject');
    register_setting('rmgc_settings', 'rmgc_email_from_name');
    register_setting('rmgc_settings', 'rmgc_email_from_address');
    
    // SMTP Settings
    register_setting('rmgc_settings', 'rmgc_smtp_host');
    register_setting('rmgc_settings', 'rmgc_smtp_port');
    register_setting('rmgc_settings', 'rmgc_smtp_user');
    register_setting('rmgc_settings', 'rmgc_smtp_pass');
    register_setting('rmgc_settings', 'rmgc_smtp_secure');
    register_setting('rmgc_settings', 'rmgc_smtp_enable', 'boolval');
}
add_action('admin_init', 'rmgc_register_settings');

// Settings page content
function rmgc_booking_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Get current values
    $admin_notification_emails = get_option('rmgc_admin_notification_emails', get_option('admin_email'));
    $admin_email_subject = get_option('rmgc_admin_email_subject', 'New Booking Request - RMGC');
    $guest_email_subject = get_option('rmgc_guest_email_subject', 'Your Booking Request - Royal Melbourne Golf Club');
    $email_from_name = get_option('rmgc_email_from_name', 'Royal Melbourne Golf Club');
    $email_from_address = get_option('rmgc_email_from_address', get_option('admin_email'));
    
    // SMTP settings
    $smtp_enable = get_option('rmgc_smtp_enable', false);
    $smtp_host = get_option('rmgc_smtp_host', '');
    $smtp_port = get_option('rmgc_smtp_port', '587');
    $smtp_user = get_option('rmgc_smtp_user', '');
    $smtp_pass = get_option('rmgc_smtp_pass', '');
    $smtp_secure = get_option('rmgc_smtp_secure', 'tls');

    if (isset($_POST['rmgc_save_settings'])) {
        if (!isset($_POST['rmgc_settings_nonce']) || !wp_verify_nonce($_POST['rmgc_settings_nonce'], 'rmgc_save_settings')) {
            wp_die('Invalid nonce');
        }

        // Save Email Settings
        update_option('rmgc_admin_notification_emails', sanitize_text_field($_POST['rmgc_admin_notification_emails']));
        update_option('rmgc_admin_email_subject', sanitize_text_field($_POST['rmgc_admin_email_subject']));
        update_option('rmgc_guest_email_subject', sanitize_text_field($_POST['rmgc_guest_email_subject']));
        update_option('rmgc_email_from_name', sanitize_text_field($_POST['rmgc_email_from_name']));
        update_option('rmgc_email_from_address', sanitize_email($_POST['rmgc_email_from_address']));
        
        // Save SMTP Settings
        update_option('rmgc_smtp_enable', isset($_POST['rmgc_smtp_enable']));
        update_option('rmgc_smtp_host', sanitize_text_field($_POST['rmgc_smtp_host']));
        update_option('rmgc_smtp_port', sanitize_text_field($_POST['rmgc_smtp_port']));
        update_option('rmgc_smtp_user', sanitize_text_field($_POST['rmgc_smtp_user']));
        if (!empty($_POST['rmgc_smtp_pass'])) {
            update_option('rmgc_smtp_pass', sanitize_text_field($_POST['rmgc_smtp_pass']));
        }
        update_option('rmgc_smtp_secure', sanitize_text_field($_POST['rmgc_smtp_secure']));

        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>RMGC Booking Settings</h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('rmgc_save_settings', 'rmgc_settings_nonce'); ?>
            
            <h2>Email Configuration</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="rmgc_admin_notification_emails">Admin Notification Emails</label>
                    </th>
                    <td>
                        <input type="text" id="rmgc_admin_notification_emails" name="rmgc_admin_notification_emails" 
                               value="<?php echo esc_attr($admin_notification_emails); ?>" class="regular-text">
                        <p class="description">Comma-separated list of email addresses to receive booking notifications</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="rmgc_admin_email_subject">Admin Email Subject</label>
                    </th>
                    <td>
                        <input type="text" id="rmgc_admin_email_subject" name="rmgc_admin_email_subject" 
                               value="<?php echo esc_attr($admin_email_subject); ?>" class="regular-text">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="rmgc_guest_email_subject">Guest Email Subject</label>
                    </th>
                    <td>
                        <input type="text" id="rmgc_guest_email_subject" name="rmgc_guest_email_subject" 
                               value="<?php echo esc_attr($guest_email_subject); ?>" class="regular-text">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="rmgc_email_from_name">From Name</label>
                    </th>
                    <td>
                        <input type="text" id="rmgc_email_from_name" name="rmgc_email_from_name" 
                               value="<?php echo esc_attr($email_from_name); ?>" class="regular-text">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="rmgc_email_from_address">From Email</label>
                    </th>
                    <td>
                        <input type="email" id="rmgc_email_from_address" name="rmgc_email_from_address" 
                               value="<?php echo esc_attr($email_from_address); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            
            <h2>SMTP Configuration</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="rmgc_smtp_enable">Enable SMTP</label>
                    </th>
                    <td>
                        <input type="checkbox" id="rmgc_smtp_enable" name="rmgc_smtp_enable" 
                               <?php checked($smtp_enable); ?>>
                        <p class="description">Use SMTP for sending emails instead of default mail function</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="rmgc_smtp_host">SMTP Host</label>
                    </th>
                    <td>
                        <input type="text" id="rmgc_smtp_host" name="rmgc_smtp_host" 
                               value="<?php echo esc_attr($smtp_host); ?>" class="regular-text">
                        <p class="description">e.g., smtp.gmail.com or mail.yourserver.com</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="rmgc_smtp_port">SMTP Port</label>
                    </th>
                    <td>
                        <input type="text" id="rmgc_smtp_port" name="rmgc_smtp_port" 
                               value="<?php echo esc_attr($smtp_port); ?>" class="small-text">
                        <p class="description">Common ports: 25, 465, or 587</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="rmgc_smtp_secure">Encryption</label>
                    </th>
                    <td>
                        <select id="rmgc_smtp_secure" name="rmgc_smtp_secure">
                            <option value="none" <?php selected($smtp_secure, 'none'); ?>>None</option>
                            <option value="ssl" <?php selected($smtp_secure, 'ssl'); ?>>SSL</option>
                            <option value="tls" <?php selected($smtp_secure, 'tls'); ?>>TLS</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="rmgc_smtp_user">SMTP Username</label>
                    </th>
                    <td>
                        <input type="text" id="rmgc_smtp_user" name="rmgc_smtp_user" 
                               value="<?php echo esc_attr($smtp_user); ?>" class="regular-text">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="rmgc_smtp_pass">SMTP Password</label>
                    </th>
                    <td>
                        <input type="password" id="rmgc_smtp_pass" name="rmgc_smtp_pass" class="regular-text">
                        <p class="description">Leave blank to keep existing password</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="rmgc_save_settings" class="button-primary" value="Save Settings">
                <button type="button" class="button" onclick="rmgcTestEmail()">Send Test Email</button>
            </p>
        </form>
    </div>

    <script>
    function rmgcTestEmail() {
        var data = {
            action: 'rmgc_test_email',
            nonce: '<?php echo wp_create_nonce('rmgc_test_email'); ?>'
        };
        
        jQuery.post(ajaxurl, data, function(response) {
            if (response.success) {
                alert('Test email sent successfully! Please check your inbox.');
            } else {
                alert('Error sending test email: ' + response.data);
            }
        });
    }
    </script>
    <?php
}