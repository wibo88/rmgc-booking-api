<?php
if (!defined('ABSPATH')) {
    exit;
}

// Add menu items
function rmgc_add_admin_menu() {
    add_menu_page(
        'RMGC Bookings',
        'RMGC Bookings',
        'manage_options',
        'rmgc-bookings',
        'rmgc_bookings_page',
        'dashicons-calendar-alt'
    );
    
    add_submenu_page(
        'rmgc-bookings',
        'Settings',
        'Settings',
        'manage_options',
        'rmgc-settings',
        'rmgc_booking_settings_page'
    );
    
    add_submenu_page(
        'rmgc-bookings',
        'System Logs',
        'System Logs',
        'manage_options',
        'rmgc-logs',
        'rmgc_logs_page'
    );
}
add_action('admin_menu', 'rmgc_add_admin_menu');

// Enqueue admin scripts and styles
function rmgc_admin_enqueue_scripts($hook) {
    // Only load on our plugin's pages
    if (strpos($hook, 'rmgc') === false) {
        return;
    }
    
    wp_enqueue_script(
        'rmgc-admin',
        plugins_url('../js/admin.js', __FILE__),
        array('jquery'),
        time(),
        true
    );

    wp_localize_script('rmgc-admin', 'rmgcAdmin', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('rmgc_admin_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'rmgc_admin_enqueue_scripts');

// Render bookings page
function rmgc_bookings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $bookings = rmgc_get_bookings();
    ?>
    <div class="wrap">
        <h1>Booking Requests</h1>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <select id="bulk-action-selector-top">
                    <option value="-1">Bulk Actions</option>
                    <option value="approve">Approve</option>
                    <option value="reject">Reject</option>
                </select>
                <button type="button" class="button action">Apply</button>
            </div>
            <div class="tablenav-pages">
                <span class="displaying-num"><?php echo count($bookings); ?> items</span>
            </div>
            <br class="clear">
        </div>

        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <input type="checkbox" />
                    </td>
                    <th scope="col" class="manage-column column-date">Date</th>
                    <th scope="col" class="manage-column">Name</th>
                    <th scope="col" class="manage-column">Contact</th>
                    <th scope="col" class="manage-column">Club Details</th>
                    <th scope="col" class="manage-column">Time & Players</th>
                    <th scope="col" class="manage-column">Status</th>
                    <th scope="col" class="manage-column">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                <tr id="booking-<?php echo esc_attr($booking['id']); ?>">
                    <th scope="row" class="check-column">
                        <input type="checkbox" name="booking[]" value="<?php echo esc_attr($booking['id']); ?>" />
                    </th>
                    <td class="date column-date">
                        <?php echo esc_html(date('D, j M Y', strtotime($booking['date']))); ?>
                    </td>
                    <td class="column-name">
                        <strong><?php echo esc_html($booking['first_name'] . ' ' . $booking['last_name']); ?></strong>
                        <?php if (!empty($booking['handicap'])): ?>
                            <br><small>Handicap: <?php echo esc_html($booking['handicap']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="column-contact">
                        <strong><?php echo esc_html($booking['email']); ?></strong>
                        <?php if (!empty($booking['phone'])): ?>
                            <br><?php echo esc_html($booking['phone']); ?>
                        <?php endif; ?>
                        <?php if (!empty($booking['state']) || !empty($booking['country'])): ?>
                            <br><small><?php echo esc_html(trim($booking['state'] . ', ' . $booking['country'], ', ')); ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="column-club">
                        <strong><?php echo esc_html($booking['club_name']); ?></strong>
                        <?php if (!empty($booking['club_state']) || !empty($booking['club_country'])): ?>
                            <br><small><?php echo esc_html(trim($booking['club_state'] . ', ' . $booking['club_country'], ', ')); ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="column-time">
                        <strong><?php echo esc_html($booking['players'] . ' player' . ($booking['players'] > 1 ? 's' : '')); ?></strong>
                        <?php 
                            $prefs = maybe_unserialize($booking['time_preferences']);
                            $prefs = array_map(function($pref) {
                                return ucwords(str_replace('_', ' ', $pref));
                            }, $prefs);
                            echo '<br><small>' . esc_html(implode(', ', $prefs)) . '</small>';
                        ?>
                    </td>
                    <td class="column-status">
                        <span class="rmgc-status status-<?php echo esc_attr($booking['status']); ?>">
                            <?php 
                                $status_text = ucfirst($booking['status']);
                                if ($booking['status'] === 'approved' && !empty($booking['tee_time'])) {
                                    $status_text .= " ({$booking['tee_time']})";
                                }
                                echo esc_html($status_text);
                            ?>
                        </span>
                    </td>
                    <td class="column-actions">
                        <div class="row-actions">
                            <button class="button-primary rmgc-approve-booking" 
                                    data-booking-id="<?php echo esc_attr($booking['id']); ?>"
                                    <?php echo $booking['status'] === 'approved' ? 'disabled' : ''; ?>>
                                Approve
                            </button>
                            <button class="button rmgc-reject-booking" 
                                    data-booking-id="<?php echo esc_attr($booking['id']); ?>"
                                    <?php echo $booking['status'] === 'rejected' ? 'disabled' : ''; ?>>
                                Reject
                            </button>
                        </div>
                        <div class="rmgc-notes-section">
                            <textarea id="booking-note-<?php echo esc_attr($booking['id']); ?>" 
                                    placeholder="Add a note" rows="2"></textarea>
                            <button class="button rmgc-add-note" 
                                    data-booking-id="<?php echo esc_attr($booking['id']); ?>">
                                Add Note
                            </button>
                            <div id="booking-notes-<?php echo esc_attr($booking['id']); ?>" 
                                 class="rmgc-notes-container">
                                <?php if (!empty($booking['notes'])): ?>
                                    <?php foreach ($booking['notes'] as $note): ?>
                                        <div class="rmgc-note">
                                            <span class="note-date"><?php echo esc_html($note['date']); ?></span>
                                            <p><?php echo esc_html($note['content']); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <input type="checkbox" />
                    </td>
                    <th scope="col" class="manage-column column-date">Date</th>
                    <th scope="col" class="manage-column">Name</th>
                    <th scope="col" class="manage-column">Contact</th>
                    <th scope="col" class="manage-column">Club Details</th>
                    <th scope="col" class="manage-column">Time & Players</th>
                    <th scope="col" class="manage-column">Status</th>
                    <th scope="col" class="manage-column">Actions</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <style>
        .status-pending { color: #f0ad4e; }
        .status-approved { color: #5cb85c; }
        .status-rejected { color: #d9534f; }

        .column-date { width: 12%; }
        .column-name { width: 15%; }
        .column-contact { width: 18%; }
        .column-club { width: 15%; }
        .column-time { width: 12%; }
        .column-status { width: 8%; }
        .column-actions { width: 20%; }

        .rmgc-notes-section {
            margin-top: 10px;
        }
        .rmgc-notes-section textarea {
            width: 100%;
            margin-bottom: 5px;
            padding: 5px;
        }
        .rmgc-notes-container {
            margin-top: 10px;
            max-height: 150px;
            overflow-y: auto;
            background: #f9f9f9;
            border: 1px solid #e5e5e5;
            padding: 5px;
        }
        .rmgc-note {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .note-date {
            font-size: 0.8em;
            color: #666;
            font-style: italic;
        }
        .row-actions {
            margin-bottom: 10px;
        }
        .row-actions button {
            margin-right: 5px;
        }
        td.column-actions {
            padding-bottom: 15px;
        }
    </style>
    <?php
}

// Render logs page
function rmgc_logs_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $log_file = WP_CONTENT_DIR . '/rmgc-logs/rmgc-errors.log';
    $log_content = '';
    
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
    }
    
    // Handle log clear action
    if (isset($_POST['clear_logs']) && check_admin_referer('rmgc_clear_logs')) {
        file_put_contents($log_file, '');
        $log_content = '';
        echo '<div class="notice notice-success"><p>Logs cleared successfully!</p></div>';
    }
    
    ?>
    <div class="wrap">
        <h1>System Logs</h1>
        
        <form method="post">
            <?php wp_nonce_field('rmgc_clear_logs'); ?>
            <p>
                <input type="submit" name="clear_logs" class="button" value="Clear Logs">
                <button type="button" class="button" onclick="window.location.reload()">Refresh</button>
            </p>
        </form>
        
        <div class="card">
            <textarea style="width: 100%; height: 500px; font-family: monospace; white-space: pre;" readonly><?php 
                echo !empty($log_content) ? esc_textarea($log_content) : 'No logs available.';
            ?></textarea>
        </div>
    </div>
    <?php
}