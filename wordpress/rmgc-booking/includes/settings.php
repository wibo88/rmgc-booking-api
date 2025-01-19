<?php
// Register settings
function rmgc_register_settings() {
    register_setting('rmgc_settings', 'rmgc_api_url');
    register_setting('rmgc_settings', 'rmgc_api_key');
    register_setting('rmgc_settings', 'rmgc_recaptcha_site_key');
    register_setting('rmgc_settings', 'rmgc_recaptcha_secret_key');
    register_setting('rmgc_settings', 'rmgc_admin_notification_emails');
    register_setting('rmgc_settings', 'rmgc_admin_email_subject');
    register_setting('rmgc_settings', 'rmgc_guest_email_subject');
    register_setting('rmgc_settings', 'rmgc_email_from_name');
    register_setting('rmgc_settings', 'rmgc_email_from_address');
}
add_action('admin_init', 'rmgc_register_settings');

// Settings page content
function rmgc_booking_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $admin_notification_emails = get_option('rmgc_admin_notification_emails', get_option('admin_email'));
    $admin_email_subject = get_option('rmgc_admin_email_subject', 'New Booking Request Received - RMGC');
    $guest_email_subject = get_option('rmgc_guest_email_subject', 'Your Booking Request - Royal Melbourne Golf Club');
    $email_from_name = get_option('rmgc_email_from_name', get_bloginfo('name'));
    $email_from_address = get_option('rmgc_email_from_address', get_option('admin_email'));

    if (isset($_POST['rmgc_save_settings'])) {
        if (!isset($_POST['rmgc_settings_nonce']) || !wp_verify_nonce($_POST['rmgc_settings_nonce'], 'rmgc_save_settings')) {
            wp_die('Invalid nonce');
        }

        update_option('rmgc_api_url', sanitize_text_field($_POST['rmgc_api_url']));
        update_option('rmgc_api_key', sanitize_text_field($_POST['rmgc_api_key']));
        update_option('rmgc_recaptcha_site_key', sanitize_text_field($_POST['rmgc_recaptcha_site_key']));
        update_option('rmgc_recaptcha_secret_key', sanitize_text_field($_POST['rmgc_recaptcha_secret_key']));
        
        // Sanitize admin notification emails (allow multiple, comma-separated)
        $admin_emails = array_map('trim', explode(',', sanitize_text_field($_POST['rmgc_admin_notification_emails'])));
        $admin_emails = array_filter($admin_emails, 'is_email');
        update_option('rmgc_admin_notification_emails', implode(',', $admin_emails));
        
        update_option('rmgc_admin_email_subject', sanitize_text_field($_POST['rmgc_admin_email_subject']));
        update_option('rmgc_guest_email_subject', sanitize_text_field($_POST['rmgc_guest_email_subject']));
        update_option('rmgc_email_from_name', sanitize_text_field($_POST['rmgc_email_from_name']));
        update_option('rmgc_email_from_address', sanitize_email($_POST['rmgc_email_from_address']));

        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>RMGC Booking Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('rmgc_save_settings', 'rmgc_settings_nonce'); ?>
            
            <h2>API Configuration</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="rmgc_api_url">API URL</label>
                    </th>
                    <td>
                        <input type="text" id="rmgc_api_url" name="rmgc_api_url" value="<?php echo esc_attr(get_option('rmgc_api_url')); ?>" class="regular-text">
                        <p class="description">The URL of your booking API endpoint (e.g., http://localhost:3000)</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="rmgc_api_key">API Key</label>
                    </th>
                    <td>
                        <input type="password" id="rmgc_api_key" name="rmgc_api_key" value="<?php echo esc_attr(get_option('rmgc_api_key')); ?>" class="regular-text">
                        <p class="description">Your API authentication key</p>
                    </td>
                </tr>
            </table>

            <h2>Email Configuration</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="rmgc_admin_notification_emails">Admin Notification Emails</label>
                    </th>
                    <td>
                        <input type="text" id="rmgc_admin_notification_emails" name="rmgc_admin_notification_emails" value="<?php echo esc_attr($admin_notification_emails); ?>" class="regular-text">
                        <p class="description">Email addresses to receive booking notifications (comma-separated for multiple)</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="rmgc_admin_email_subject">Admin Email Subject</label>
                    </th>
                    <td>
                        <input type="text" id="rmgc_admin_email_subject" name="rmgc_admin_email_subject" value="<?php echo esc_attr($admin_email_subject); ?>" class="regular-text">
                        <p class="description">Subject line for staff notification emails</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="rmgc_guest_email_subject">Guest Email Subject</label>
                    </th>
                    <td>
                        <input type="text" id="rmgc_guest_email_subject" name="rmgc_guest_email_subject" value="<?php echo esc_attr($guest_email_subject); ?>" class="regular-text">
                        <p class="description">Subject line for guest confirmation emails</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="rmgc_email_from_name">From Name</label>
                    </th>
                    <td>
                        <input type="text" id="rmgc_email_from_name" name="rmgc_email_from_name" value="<?php echo esc_attr($email_from_name); ?>" class="regular-text">
                        <p class="description">Name to appear in the From field of notification emails</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="rmgc_email_from_address">From Email</label>
                    </th>
                    <td>
                        <input type="email" id="rmgc_email_from_address" name="rmgc_email_from_address" value="<?php echo esc_attr($email_from_address); ?>" class="regular-text">
                        <p class="description">Email address to send notifications from</p>
                    </td>
                </tr>
            </table>
            
            <h2>reCAPTCHA Configuration</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="rmgc_recaptcha_site_key">ReCAPTCHA Site Key</label>
                    </th>
                    <td>
                        <input type="text" id="rmgc_recaptcha_site_key" name="rmgc_recaptcha_site_key" value="<?php echo esc_attr(get_option('rmgc_recaptcha_site_key')); ?>" class="regular-text">
                        <p class="description">Google ReCAPTCHA v2 site key</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="rmgc_recaptcha_secret_key">ReCAPTCHA Secret Key</label>
                    </th>
                    <td>
                        <input type="password" id="rmgc_recaptcha_secret_key" name="rmgc_recaptcha_secret_key" value="<?php echo esc_attr(get_option('rmgc_recaptcha_secret_key')); ?>" class="regular-text">
                        <p class="description">Google ReCAPTCHA v2 secret key</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="rmgc_save_settings" class="button-primary" value="Save Settings">
            </p>
        </form>
    </div>
    <?php
}