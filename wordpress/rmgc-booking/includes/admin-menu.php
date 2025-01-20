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
        'rmgc_settings_page'
    );
}
add_action('admin_menu', 'rmgc_add_admin_menu');

// Enqueue admin scripts
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
    $bookings = rmgc_get_bookings();
    ?>
    <div class="wrap">
        <h1>RMGC Bookings</h1>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Players</th>
                    <th>Time Preferences</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                <tr id="booking-<?php echo esc_attr($booking['id']); ?>">
                    <td><?php echo esc_html($booking['date']); ?></td>
                    <td><?php echo esc_html($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                    <td><?php echo esc_html($booking['email']); ?></td>
                    <td><?php echo esc_html($booking['players']); ?></td>
                    <td><?php 
                        $prefs = maybe_unserialize($booking['time_preferences']);
                        echo esc_html(implode(', ', array_map(function($pref) {
                            return ucwords(str_replace('_', ' ', $pref));
                        }, $prefs)));
                    ?></td>
                    <td class="rmgc-status"><?php echo esc_html($booking['status']); ?></td>
                    <td>
                        <button class="button rmgc-approve-booking" data-booking-id="<?php echo esc_attr($booking['id']); ?>"
                            <?php echo $booking['status'] === 'approved' ? 'disabled' : ''; ?>>
                            Approve
                        </button>
                        <button class="button rmgc-reject-booking" data-booking-id="<?php echo esc_attr($booking['id']); ?>"
                            <?php echo $booking['status'] === 'rejected' ? 'disabled' : ''; ?>>
                            Reject
                        </button>
                        <div class="rmgc-notes-section">
                            <textarea id="booking-note-<?php echo esc_attr($booking['id']); ?>" 
                                    placeholder="Add a note"></textarea>
                            <button class="button rmgc-add-note" 
                                    data-booking-id="<?php echo esc_attr($booking['id']); ?>">
                                Add Note
                            </button>
                            <div id="booking-notes-<?php echo esc_attr($booking['id']); ?>" class="rmgc-notes-container">
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
        </table>
    </div>

    <style>
        .rmgc-notes-section {
            margin-top: 10px;
        }
        .rmgc-notes-section textarea {
            width: 100%;
            margin-bottom: 5px;
        }
        .rmgc-notes-container {
            margin-top: 10px;
            max-height: 200px;
            overflow-y: auto;
        }
        .rmgc-note {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .note-date {
            font-size: 0.8em;
            color: #666;
        }
    </style>
    <?php
}