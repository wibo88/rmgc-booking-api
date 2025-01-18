<?php
// Add menu pages
function rmgc_booking_admin_menu() {
    add_menu_page(
        'RMGC Booking',
        'RMGC Booking',
        'manage_options',
        'rmgc-booking',
        'rmgc_booking_requests_page',
        'dashicons-calendar-alt'
    );
    
    add_submenu_page(
        'rmgc-booking',
        'Booking Requests',
        'Booking Requests',
        'manage_options',
        'rmgc-booking',
        'rmgc_booking_requests_page'
    );
    
    add_submenu_page(
        'rmgc-booking',
        'Settings',
        'Settings',
        'manage_options',
        'rmgc-booking-settings',
        'rmgc_booking_settings_page'
    );
}
add_action('admin_menu', 'rmgc_booking_admin_menu');

// Booking requests page
function rmgc_booking_requests_page() {
    // Fetch bookings from API
    $api_url = get_option('rmgc_api_url');
    $api_key = get_option('rmgc_api_key');
    
    $response = wp_remote_get($api_url . '/api/bookings', array(
        'headers' => array(
            'X-API-Key' => $api_key,
            'X-WP-Site' => get_site_url()
        )
    ));
    
    if (is_wp_error($response)) {
        echo '<div class="error"><p>Error fetching booking requests: ' . esc_html($response->get_error_message()) . '</p></div>';
        return;
    }
    
    $bookings = json_decode(wp_remote_retrieve_body($response), true);
    ?>
    <div class="wrap">
        <h1>Booking Requests</h1>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Players</th>
                    <th>Time Preferences</th>
                    <th>Handicap</th>
                    <th>Club</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bookings)) : ?>
                    <?php foreach ($bookings as $booking) : ?>
                        <tr>
                            <td><?php echo esc_html(date('d/m/Y', strtotime($booking['date']))); ?></td>
                            <td><?php echo esc_html($booking['firstName'] . ' ' . $booking['lastName']); ?></td>
                            <td><?php echo esc_html($booking['email']); ?></td>
                            <td><?php echo esc_html($booking['players']); ?></td>
                            <td><?php echo esc_html(implode(', ', array_map(function($pref) {
                                return ucwords(str_replace('_', ' ', $pref));
                            }, $booking['timePreferences']))); ?></td>
                            <td><?php echo esc_html($booking['handicap']); ?></td>
                            <td><?php echo esc_html($booking['clubName']); ?></td>
                            <td><?php echo esc_html(ucfirst($booking['status'] ?? 'Pending')); ?></td>
                            <td>
                                <button class="button approve-booking" data-id="<?php echo esc_attr($booking['id']); ?>">Approve</button>
                                <button class="button reject-booking" data-id="<?php echo esc_attr($booking['id']); ?>">Reject</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="9">No booking requests found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.approve-booking, .reject-booking').on('click', function() {
            const bookingId = $(this).data('id');
            const action = $(this).hasClass('approve-booking') ? 'approve' : 'reject';
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'update_booking_status',
                    booking_id: bookingId,
                    status: action
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error updating booking status');
                    }
                }
            });
        });
    });
    </script>
    <?php
}

// AJAX handler for updating booking status
function rmgc_update_booking_status() {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    
    $api_url = get_option('rmgc_api_url');
    $api_key = get_option('rmgc_api_key');
    
    $response = wp_remote_post($api_url . '/api/bookings/' . $booking_id . '/status', array(
        'headers' => array(
            'X-API-Key' => $api_key,
            'X-WP-Site' => get_site_url(),
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode(array(
            'status' => $status
        ))
    ));
    
    if (is_wp_error($response)) {
        wp_send_json_error();
        return;
    }
    
    wp_send_json_success();
}
add_action('wp_ajax_update_booking_status', 'rmgc_update_booking_status');