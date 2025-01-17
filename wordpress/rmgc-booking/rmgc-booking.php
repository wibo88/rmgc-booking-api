<?php
// [Previous code remains the same until admin menu...]

// Add admin menu
function rmgc_add_admin_menu() {
    add_menu_page(
        'RMGC Booking',
        'RMGC Booking',
        'manage_options',
        'rmgc-booking',
        'rmgc_bookings_page',
        'dashicons-calendar-alt'
    );
    
    add_submenu_page(
        'rmgc-booking',
        'Settings',
        'Settings',
        'manage_options',
        'rmgc-booking-settings',
        'rmgc_settings_page'
    );
}
add_action('admin_menu', 'rmgc_add_admin_menu');

// Bookings page
function rmgc_bookings_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rmgc_bookings';
    
    // Handle status updates
    if (isset($_POST['booking_id']) && isset($_POST['status'])) {
        $wpdb->update(
            $table_name,
            array('status' => sanitize_text_field($_POST['status'])),
            array('id' => intval($_POST['booking_id']))
        );
        
        // Send email notification if status changed to approved
        if ($_POST['status'] === 'approved') {
            $booking = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                intval($_POST['booking_id'])
            ));
            
            if ($booking) {
                $to = $booking->email;
                $subject = 'Royal Melbourne Golf Club - Booking Approved';
                $message = "Your booking request has been approved:\n\n";
                $message .= "Date: " . $booking->booking_date . "\n";
                $message .= "Players: " . $booking->players . "\n";
                
                wp_mail($to, $subject, $message);
            }
        }
    }
    
    // Get bookings with pagination
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $items_per_page = 20;
    $offset = ($page - 1) * $items_per_page;
    
    $bookings = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $items_per_page,
        $offset
    ));
    
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_items / $items_per_page);
    
    ?>
    <div class="wrap">
        <h1>Booking Requests</h1>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Players</th>
                    <th>Handicap</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="7">No booking requests found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo esc_html($booking->booking_date); ?></td>
                            <td><?php echo esc_html($booking->players); ?></td>
                            <td><?php echo esc_html($booking->handicap); ?></td>
                            <td><?php echo esc_html($booking->email); ?></td>
                            <td><?php echo esc_html($booking->status); ?></td>
                            <td><?php echo esc_html($booking->created_at); ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo esc_attr($booking->id); ?>">
                                    <select name="status" onchange="this.form.submit()" style="width: 100px;">
                                        <option value="pending" <?php selected($booking->status, 'pending'); ?>>Pending</option>
                                        <option value="approved" <?php selected($booking->status, 'approved'); ?>>Approved</option>
                                        <option value="rejected" <?php selected($booking->status, 'rejected'); ?>>Rejected</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if ($total_pages > 1): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $total_pages,
                        'current' => $page,
                    ));
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// Settings page
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
        
        <form method="post">
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

// Email notification function
function rmgc_send_notification($booking_data) {
    $to = get_option('rmgc_notification_emails');
    $subject = 'New Booking Request - Royal Melbourne Golf Club';
    
    $message = "A new booking request has been received:\n\n";
    $message .= "Date: " . $booking_data['date'] . "\n";
    $message .= "Players: " . $booking_data['players'] . "\n";
    $message .= "Handicap: " . $booking_data['handicap'] . "\n";
    $message .= "Email: " . $booking_data['email'] . "\n\n";
    $message .= "View all bookings in the WordPress admin panel.";
    
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    
    wp_mail($to, $subject, $message, $headers);
}

?>