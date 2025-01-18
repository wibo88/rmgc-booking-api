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
    // Get bookings from database
    $bookings = rmgc_get_bookings();
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
                            <td><?php echo esc_html(ucfirst($booking['status'])); ?></td>
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
                    action: 'rmgc_update_booking_status',
                    booking_id: bookingId,
                    status: action,
                    nonce: rmgcAjax.nonce
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

// Register AJAX handler
add_action('wp_ajax_rmgc_update_booking_status', 'rmgc_handle_booking_status_update');

function rmgc_handle_booking_status_update() {
    // Verify nonce and permissions
    if (!check_ajax_referer('rmgc_booking_nonce', 'nonce', false) || !current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
        return;
    }
    
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    
    if (!$booking_id || !in_array($status, array('approve', 'reject'))) {
        wp_send_json_error('Invalid parameters');
        return;
    }
    
    $result = rmgc_update_booking_status($booking_id, $status);
    
    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Failed to update status');
    }
}