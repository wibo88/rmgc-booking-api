<?php
function rmgc_booking_requests_page() {
    // Get bookings with notes
    $bookings = rmgc_get_bookings();
    ?>
    <div class="wrap">
        <h1>Booking Requests</h1>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Club</th>
                    <th>Players</th>
                    <th>Time Preferences</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo esc_html(date('d/m/Y', strtotime($booking['date']))); ?></td>
                        <td>
                            <?php 
                            echo esc_html($booking['first_name'] . ' ' . $booking['last_name']); 
                            if ($booking['assignedTime']) {
                                echo '<br><small>Tee time: ' . esc_html(date('g:i a', strtotime($booking['assignedTime']))) . '</small>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            echo esc_html($booking['club_name']);
                            echo '<br><small>HC: ' . esc_html($booking['handicap']) . '</small>';
                            ?>
                        </td>
                        <td><?php echo esc_html($booking['players']); ?></td>
                        <td><?php 
                            $prefs = maybe_unserialize($booking['time_preferences']);
                            echo esc_html(implode(', ', array_map(function($pref) {
                                return ucwords(str_replace('_', ' ', $pref));
                            }, $prefs)));
                        ?></td>
                        <td>
                            <span class="status-<?php echo esc_attr($booking['status']); ?>">
                                <?php echo esc_html(ucfirst($booking['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $note_count = count($booking['notes']);
                            $latest_note = $note_count > 0 ? $booking['notes'][0] : null;
                            ?>
                            <button class="button view-notes" data-booking='<?php echo json_encode($booking); ?>'>
                                <?php if ($note_count > 0): ?>
                                    <span class="dashicons dashicons-admin-comments"></span>
                                    <?php echo esc_html($note_count); ?>
                                    <span class="screen-reader-text">View Notes</span>
                                <?php else: ?>
                                    Add Note
                                <?php endif; ?>
                            </button>
                            <?php if ($latest_note): ?>
                                <div class="note-preview" title="<?php echo esc_attr($latest_note['note']); ?>">
                                    <?php echo esc_html(substr($latest_note['note'], 0, 50) . (strlen($latest_note['note']) > 50 ? '...' : '')); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($booking['status'] === 'pending'): ?>
                                <button class="button approve-booking" 
                                        data-id="<?php echo esc_attr($booking['id']); ?>"
                                        data-time-prefs='<?php echo json_encode($prefs); ?>'>
                                    Approve
                                </button>
                                <button class="button reject-booking" 
                                        data-id="<?php echo esc_attr($booking['id']); ?>">
                                    Reject
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Approval Dialog -->
    <div id="approval-dialog" title="Approve Booking" style="display:none;">
        <input type="hidden" id="booking-id">
        <p><strong>Time Preferences:</strong> <span id="time-preferences"></span></p>
        <p>
            <label for="assigned-time">Assign Tee Time:</label><br>
            <input type="time" id="assigned-time" required>
        </p>
        <p>
            <label for="approval-note">Add a note (optional):</label><br>
            <textarea id="approval-note" rows="3" style="width: 100%;"></textarea>
        </p>
    </div>

    <!-- Rejection Dialog -->
    <div id="rejection-dialog" title="Reject Booking" style="display:none;">
        <input type="hidden" id="rejection-booking-id">
        <p>
            <label for="rejection-note">Reason for rejection:</label><br>
            <textarea id="rejection-note" rows="3" style="width: 100%;" required></textarea>
        </p>
    </div>

    <!-- Notes Dialog -->
    <div id="notes-dialog" title="Booking Notes" style="display:none;">
        <input type="hidden" id="notes-booking-id">
        <div id="notes-history" style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;"></div>
        <div>
            <label for="new-note">Add a new note:</label><br>
            <textarea id="new-note" rows="3" style="width: 100%;"></textarea>
        </div>
    </div>

    <style>
        .status-pending { color: #f0ad4e; }
        .status-approved { color: #5cb85c; }
        .status-rejected { color: #d9534f; }
        
        .note-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .note-meta {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
        }
        .note-preview {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }
        .view-notes .dashicons {
            line-height: 1.4;
        }
    </style>
    <?php
}
?>