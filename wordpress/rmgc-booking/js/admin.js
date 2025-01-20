jQuery(document).ready(function($) {
    // Approve booking
    $('.rmgc-approve-booking').on('click', function(e) {
        e.preventDefault();
        const bookingId = $(this).data('booking-id');
        
        // Show tee time dialog
        const teeTime = prompt('Please enter the confirmed tee time (e.g., 8:30 AM):');
        if (teeTime === null) {
            return; // User cancelled
        }
        
        // Validate tee time format
        const timeRegex = /^(0?[1-9]|1[0-2]):[0-5][0-9]\s*[APap][Mm]$/;
        if (!timeRegex.test(teeTime)) {
            alert('Please enter a valid time in 12-hour format (e.g., 8:30 AM)');
            return;
        }
        
        updateBookingStatus(bookingId, 'approved', $(this), teeTime);
    });

    // Reject booking
    $('.rmgc-reject-booking').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to reject this booking?')) {
            const bookingId = $(this).data('booking-id');
            updateBookingStatus(bookingId, 'rejected', $(this));
        }
    });

    // Add note
    $('.rmgc-add-note').on('click', function(e) {
        e.preventDefault();
        const button = $(this);
        const bookingId = button.data('booking-id');
        const noteInput = $(`#booking-note-${bookingId}`);
        const note = noteInput.val().trim();
        
        if (!note) {
            alert('Please enter a note');
            return;
        }

        button.prop('disabled', true).text('Saving...');

        $.ajax({
            url: rmgcAdmin.ajaxurl,
            method: 'POST',
            data: {
                action: 'rmgc_add_booking_note',
                booking_id: bookingId,
                note: note,
                nonce: rmgcAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Add new note to display
                    const notesContainer = $(`#booking-notes-${bookingId}`);
                    const newNote = $(`
                        <div class="rmgc-note">
                            <span class="note-date">${new Date().toLocaleString()}</span>
                            <p>${note}</p>
                        </div>
                    `);
                    notesContainer.prepend(newNote);
                    
                    // Clear input
                    noteInput.val('');
                    
                    // Show success message
                    showMessage('Note added successfully', 'success');
                } else {
                    showMessage(response.data || 'Error adding note', 'error');
                }
            },
            error: function() {
                showMessage('Error adding note. Please try again.', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text('Add Note');
            }
        });
    });

    // Function to update booking status
    function updateBookingStatus(bookingId, status, buttonElement, teeTime = null) {
        const row = buttonElement.closest('tr');
        const statusCell = row.find('.rmgc-status');
        const originalStatus = statusCell.text();
        const approveButton = row.find('.rmgc-approve-booking');
        const rejectButton = row.find('.rmgc-reject-booking');
        
        // Disable buttons during update
        approveButton.prop('disabled', true);
        rejectButton.prop('disabled', true);
        
        // Show loading state
        statusCell.html('<em>Updating...</em>');

        $.ajax({
            url: rmgcAdmin.ajaxurl,
            method: 'POST',
            data: {
                action: 'rmgc_update_booking_status',
                booking_id: bookingId,
                status: status,
                tee_time: teeTime,
                nonce: rmgcAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update status display
                    let statusText = status.charAt(0).toUpperCase() + status.slice(1);
                    if (status === 'approved' && teeTime) {
                        statusText += ` (${teeTime})`;
                    }
                    statusCell.html(statusText)
                        .removeClass('status-pending status-approved status-rejected')
                        .addClass('status-' + status);
                    
                    // Update button states
                    if (status === 'approved') {
                        approveButton.prop('disabled', true);
                        rejectButton.prop('disabled', false);
                    } else if (status === 'rejected') {
                        approveButton.prop('disabled', false);
                        rejectButton.prop('disabled', true);
                    }
                    
                    showMessage('Booking status updated successfully', 'success');
                } else {
                    // Revert status on error
                    statusCell.text(originalStatus);
                    approveButton.prop('disabled', originalStatus === 'approved');
                    rejectButton.prop('disabled', originalStatus === 'rejected');
                    
                    showMessage(response.data || 'Error updating status', 'error');
                }
            },
            error: function() {
                // Revert on error
                statusCell.text(originalStatus);
                approveButton.prop('disabled', originalStatus === 'approved');
                rejectButton.prop('disabled', originalStatus === 'rejected');
                
                showMessage('Error updating status. Please try again.', 'error');
            }
        });
    }

    // Function to show messages
    function showMessage(message, type = 'info') {
        const messageDiv = $('<div>')
            .addClass('notice')
            .addClass(type === 'error' ? 'notice-error' : 'notice-success')
            .addClass('is-dismissible')
            .html(`<p>${message}</p>`)
            .hide();

        $('.wrap > h1').after(messageDiv);
        messageDiv.slideDown();

        // Add dismiss button
        const dismissButton = $('<button>')
            .attr('type', 'button')
            .addClass('notice-dismiss')
            .html('<span class="screen-reader-text">Dismiss this notice.</span>');
        
        messageDiv.append(dismissButton);

        dismissButton.on('click', function() {
            messageDiv.slideUp(function() {
                $(this).remove();
            });
        });

        // Auto dismiss after 5 seconds
        setTimeout(function() {
            if (messageDiv.is(':visible')) {
                messageDiv.slideUp(function() {
                    $(this).remove();
                });
            }
        }, 5000);
    }
});