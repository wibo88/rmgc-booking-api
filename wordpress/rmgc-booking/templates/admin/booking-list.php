<?php
// Protect from direct file access
if (!defined('ABSPATH')) {
    exit;
}

function rmgc_booking_requests_page() {
    // Get filters
    $current_filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : 'all';
    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    
    // Build filters array
    $filters = array();
    if ($current_filter !== 'all') {
        $filters['status'] = $current_filter;
    }
    if ($date_from) {
        $filters['date_from'] = $date_from;
    }
    if ($date_to) {
        $filters['date_to'] = $date_to;
    }
    if ($search) {
        $filters['search'] = $search;
    }
    
    // Get bookings from database
    $bookings = rmgc_get_bookings($filters);
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Booking Requests</h1>
        
        <!-- Filters -->
        <div class="tablenav top">
            <form method="get">
                <input type="hidden" name="page" value="rmgc-booking">
                
                <select name="filter" class="rmgc-filter">
                    <option value="all" <?php selected($current_filter, 'all'); ?>>All Statuses</option>
                    <option value="pending" <?php selected($current_filter, 'pending'); ?>>Pending</option>
                    <option value="approved" <?php selected($current_filter, 'approved'); ?>>Approved</option>
                    <option value="rejected" <?php selected($current_filter, 'rejected'); ?>>Rejected</option>
                </select>
                
                <input type="text" name="date_from" class="date-picker" placeholder="From Date" value="<?php echo esc_attr($date_from); ?>">
                <input type="text" name="date_to" class="date-picker" placeholder="To Date" value="<?php echo esc_attr($date_to); ?>">
                <input type="search" name="search" placeholder="Search bookings..." value="<?php echo esc_attr($search); ?>">
                
                <input type="submit" class="button" value="Filter">
            </form>
        </div>
        
        <!-- Bookings Table -->
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
                    <th>Last Modified</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bookings)) : ?>
                    <?php foreach ($bookings as $booking) : ?>
                        <tr>
                            <td>
                                <?php echo esc_html(date('d/m/Y', strtotime($booking['date']))); ?>
                                <?php if ($booking['assignedTime']): ?>
                                    <br><small><?php echo esc_html(date('g:i A', strtotime($booking['assignedTime']))); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($booking['firstName'] . ' ' . $booking['lastName']); ?></td>
                            <td><?php echo esc_html($booking['email']); ?></td>
                            <td><?php echo esc_html($booking['players']); ?></td>
                            <td><?php echo esc_html(implode(', ', array_map(function($pref) {
                                return ucwords(str_replace('_', ' ', $pref));
                            }, $booking['timePreferences']))); ?></td>
                            <td><?php echo esc_html($booking['handicap']); ?></td>
                            <td><?php echo esc_html($booking['clubName']); ?></td>
                            <td>
                                <span class="status-<?php echo esc_attr($booking['status']); ?>">
                                    <?php echo esc_html(ucfirst($booking['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    echo esc_html(date('d/m/Y H:i', strtotime($booking['lastModified'])));
                                    if ($booking['modifiedBy']) {
                                        $user = get_userdata($booking['modifiedBy']);
                                        echo '<br><small>by ' . esc_html($user->display_name) . '</small>';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <button class="button action-button approve-booking" 
                                            data-id="<?php echo esc_attr($booking['id']); ?>"
                                            data-date="<?php echo esc_attr($booking['date']); ?>"
                                            data-time-prefs='<?php echo esc_attr(json_encode($booking['timePreferences'])); ?>'>
                                        Approve
                                    </button>
                                    <button class="button action-button reject-booking" 
                                            data-id="<?php echo esc_attr($booking['id']); ?>">
                                        Reject
                                    </button>
                                <?php endif; ?>
                                <button class="button action-button view-notes" 
                                        data-id="<?php echo esc_attr($booking['id']); ?>"
                                        data-booking='<?php echo esc_attr(json_encode($booking)); ?>'>
                                    Notes
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="10">No booking requests found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Approval Dialog -->
    <div id="approval-dialog" title="Approve Booking" style="display:none;">
        <form id="approval-form">
            <input type="hidden" id="booking-id">
            <div class="form-field">
                <label for="assigned-time">Assign Tee Time:</label>
                <input type="time" id="assigned-time" required>
                <p class="description">Selected time preferences: <span id="time-preferences"></span></p>
            </div>
            <div class="form-field">
                <label for="approval-note">Add Note (optional):</label>
                <textarea id="approval-note" rows="4" style="width: 100%;"></textarea>
            </div>
        </form>
    </div>

    <!-- Rejection Dialog -->
    <div id="rejection-dialog" title="Reject Booking" style="display:none;">
        <form id="rejection-form">
            <input type="hidden" id="rejection-booking-id">
            <div class="form-field">
                <label for="rejection-note">Reason for Rejection:</label>
                <textarea id="rejection-note" rows="4" style="width: 100%;" required></textarea>
            </div>
        </form>
    </div>

    <!-- Notes Dialog -->
    <div id="notes-dialog" title="Booking Notes" style="display:none;">
        <div id="notes-history"></div>
        <form id="add-note-form">
            <input type="hidden" id="notes-booking-id">
            <div class="form-field">
                <label for="new-note">Add Note:</label>
                <textarea id="new-note" rows="4" style="width: 100%;" required></textarea>
            </div>
        </form>
    </div>

    <style>
        .status-pending { color: #f0ad4e; }
        .status-approved { color: #5cb85c; }
        .status-rejected { color: #d9534f; }
        
        .form-field { margin-bottom: 15px; }
        .form-field label { display: block; margin-bottom: 5px; font-weight: bold; }
        
        .note-item {
            border-left: 4px solid #0073aa;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
        }
        
        .note-meta { font-size: 0.9em; color: #666; margin-bottom: 5px; }
        .date-picker { width: 130px; }
        .rmgc-filter { min-width: 150px; }
        .action-button { margin-right: 5px !important; }
        
        .ui-dialog { z-index: 100102 !important; }
        .ui-dialog-titlebar { background: #0073aa; color: #fff; }
        .ui-button.ui-dialog-titlebar-close { color: #fff; }
    </style>
    <?php
}
