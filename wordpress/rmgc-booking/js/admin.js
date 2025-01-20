jQuery(document).ready(function($) {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Approve booking
    $('.rmgc-approve-booking').on('click', function(e) {
        e.preventDefault();
        const bookingId = $(this).data('booking-id');
        updateBookingStatus(bookingId, 'approved', $(this));
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
        const bookingId = $(this).data('booking-id');
        const noteInput = $(`#booking-note-${bookingId}`);
        const note = noteInput.val().trim();
        
        if (!note) {
            alert('Please enter a note');
            return;
        }

        const button = $(this);
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
    function updateBookingStatus(bookingId, status, buttonElement) {
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
                nonce: rmgcAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update status display
                    statusCell.html(status.charAt(0).toUpperCase() + status.slice(1))
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

    // Bulk actions
    $('#doaction, #doaction2').on('click', function(e) {
        e.preventDefault();
        
        const select = $(this).prev('select');
        const action = select.val();
        
        if (action === '-1') {
            return;
        }
        
        const checkedBoxes = $('input[name="booking[]"]:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select at least one booking');
            return;
        }
        
        if (!confirm(`Are you sure you want to ${action} the selected bookings?`)) {
            return;
        }
        
        const bookingIds = checkedBoxes.map(function() {
            return $(this).val();
        }).get();
        
        $.ajax({
            url: rmgcAdmin.ajaxurl,
            method: 'POST',
            data: {
                action: 'rmgc_bulk_update_status',
                booking_ids: bookingIds,
                status: action,
                nonce: rmgcAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    showMessage(response.data || 'Error updating bookings', 'error');
                }
            },
            error: function() {
                showMessage('Error updating bookings. Please try again.', 'error');
            }
        });
    });
});