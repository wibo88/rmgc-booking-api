<?php
// Previous code remains the same up to the actions column
                            <td>
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <button class="button action-button approve-booking" 
                                            data-id="<?php echo esc_attr($booking['id']); ?>"
                                            data-date="<?php echo esc_attr($booking['date']); ?>"
                                            data-time-prefs="<?php echo esc_attr(json_encode($booking['timePreferences'])); ?>">
                                        Approve
                                    </button>
                                    <button class="button action-button reject-booking" 
                                            data-id="<?php echo esc_attr($booking['id']); ?>">
                                        Reject
                                    </button>
                                <?php endif; ?>
                                <button class="button action-button view-notes" 
                                        data-id="<?php echo esc_attr($booking['id']); ?>"
                                        data-booking="<?php echo esc_attr(json_encode($booking)); ?>">
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
        
        .form-field {
            margin-bottom: 15px;
        }
        
        .form-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .note-item {
            border-left: 4px solid #0073aa;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
        }
        
        .note-meta {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
        }
        
        .date-picker {
            width: 130px;
        }
        
        .action-button {
            margin-right: 5px !important;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Initialize date pickers
        $('.date-picker').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        
        // Initialize dialogs
        $('#approval-dialog').dialog({
            autoOpen: false,
            modal: true,
            width: 400,
            buttons: {
                "Approve": function() {
                    handleApproval();
                },
                "Cancel": function() {
                    $(this).dialog("close");
                }
            }
        });
        
        $('#rejection-dialog').dialog({
            autoOpen: false,
            modal: true,
            width: 400,
            buttons: {
                "Reject": function() {
                    handleRejection();
                },
                "Cancel": function() {
                    $(this).dialog("close");
                }
            }
        });
        
        $('#notes-dialog').dialog({
            autoOpen: false,
            modal: true,
            width: 500,
            buttons: {
                "Add Note": function() {
                    handleAddNote();
                },
                "Close": function() {
                    $(this).dialog("close");
                }
            }
        });
        
        // Handle approval button click
        $('.approve-booking').on('click', function() {
            const id = $(this).data('id');
            const timePrefs = $(this).data('time-prefs');
            $('#booking-id').val(id);
            $('#time-preferences').text(timePrefs.map(pref => 
                pref.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
            ).join(', '));
            $('#approval-dialog').dialog('open');
        });
        
        // Handle rejection button click
        $('.reject-booking').on('click', function() {
            $('#rejection-booking-id').val($(this).data('id'));
            $('#rejection-dialog').dialog('open');
        });
        
        // Handle view notes button click
        $('.view-notes').on('click', function() {
            const booking = $(this).data('booking');
            $('#notes-booking-id').val(booking.id);
            
            // Display existing notes
            const notesHtml = booking.notes.length ? booking.notes.map(note => `
                <div class="note-item">
                    <div class="note-meta">
                        By ${note.author} on ${new Date(note.date).toLocaleString()}
                    </div>
                    <div class="note-content">${note.note}</div>
                </div>
            `).join('') : '<p>No notes yet</p>';
            
            $('#notes-history').html(notesHtml);
            $('#notes-dialog').dialog('open');
        });
        
        // Handle approval submission
        function handleApproval() {
            const bookingId = $('#booking-id').val();
            const assignedTime = $('#assigned-time').val();
            const note = $('#approval-note').val();
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'rmgc_update_booking_status',
                    nonce: rmgcAdmin.nonce,
                    booking_id: bookingId,
                    status: 'approved',
                    assigned_time: assignedTime,
                    note: note
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error updating booking status: ' + response.data);
                    }
                },
                error: function() {
                    alert('Server error occurred');
                }
            });
        }
        
        // Handle rejection submission
        function handleRejection() {
            const bookingId = $('#rejection-booking-id').val();
            const note = $('#rejection-note').val();
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'rmgc_update_booking_status',
                    nonce: rmgcAdmin.nonce,
                    booking_id: bookingId,
                    status: 'rejected',
                    note: note
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error updating booking status: ' + response.data);
                    }
                },
                error: function() {
                    alert('Server error occurred');
                }
            });
        }
        
        // Handle adding a note
        function handleAddNote() {
            const bookingId = $('#notes-booking-id').val();
            const note = $('#new-note').val();
            
            if (!note) {
                alert('Please enter a note');
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'rmgc_add_booking_note',
                    nonce: rmgcAdmin.nonce,
                    booking_id: bookingId,
                    note: note
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error adding note: ' + response.data);
                    }
                },
                error: function() {
                    alert('Server error occurred');
                }
            });
        }
    });
    </script>
    <?php
}

// Register AJAX handlers
add_action('wp_ajax_rmgc_update_booking_status', 'rmgc_handle_booking_status_update');
add_action('wp_ajax_rmgc_add_booking_note', 'rmgc_handle_booking_note_add');

function rmgc_handle_booking_status_update() {
    try {
        // Verify permissions
        if (!current_user_can('manage_options')) {
            throw new Exception('Unauthorized access');
        }
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rmgc_admin_nonce')) {
            throw new Exception('Security check failed');
        }
        
        // Get and validate parameters
        $booking_id = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $assigned_time = isset($_POST['assigned_time']) ? sanitize_text_field($_POST['assigned_time']) : null;
        $note = isset($_POST['note']) ? sanitize_text_field($_POST['note']) : '';
        
        if (!$booking_id || !in_array($status, array('approved', 'rejected'))) {
            throw new Exception('Invalid parameters');
        }
        
        // Update status
        $result = rmgc_update_booking_status($booking_id, $status, $assigned_time);
        if (!$result) {
            throw new Exception('Failed to update booking status');
        }
        
        // Add note if provided
        if ($note) {
            rmgc_add_booking_note($booking_id, $note);
        }
        
        wp_send_json_success();
        
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}

function rmgc_handle_booking_note_add() {
    try {
        // Verify permissions
        if (!current_user_can('manage_options')) {
            throw new Exception('Unauthorized access');
        }
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rmgc_admin_nonce')) {
            throw new Exception('Security check failed');
        }
        
        // Get and validate parameters
        $booking_id = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
        $note = isset($_POST['note']) ? sanitize_text_field($_POST['note']) : '';
        
        if (!$booking_id || !$note) {
            throw new Exception('Invalid parameters');
        }
        
        // Add note
        $result = rmgc_add_booking_note($booking_id, $note);
        if (!$result) {
            throw new Exception('Failed to add note');
        }
        
        wp_send_json_success();
        
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}