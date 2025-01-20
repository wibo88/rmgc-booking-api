jQuery(document).ready(function($) {
    // Approve booking
    $('.rmgc-approve-booking').on('click', function(e) {
        e.preventDefault();
        var bookingId = $(this).data('booking-id');
        updateBookingStatus(bookingId, 'approved');
    });

    // Reject booking
    $('.rmgc-reject-booking').on('click', function(e) {
        e.preventDefault();
        var bookingId = $(this).data('booking-id');
        updateBookingStatus(bookingId, 'rejected');
    });

    // Add notes
    $('.rmgc-add-note').on('click', function(e) {
        e.preventDefault();
        var bookingId = $(this).data('booking-id');
        var noteInput = $('#booking-note-' + bookingId);
        var note = noteInput.val();
        
        if (!note) {
            alert('Please enter a note');
            return;
        }

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
                    noteInput.val('');
                    // Reload notes display or append new note
                    var notesContainer = $('#booking-notes-' + bookingId);
                    var newNote = '<div class="rmgc-note">' + 
                                '<span class="note-date">' + new Date().toLocaleString() + '</span>' +
                                '<p>' + note + '</p>' +
                                '</div>';
                    notesContainer.append(newNote);
                } else {
                    alert('Error adding note: ' + response.data);
                }
            },
            error: function() {
                alert('Error adding note. Please try again.');
            }
        });
    });

    // Function to update booking status
    function updateBookingStatus(bookingId, status) {
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
                    // Update UI
                    var row = $('#booking-' + bookingId);
                    row.find('.rmgc-status').text(status);
                    
                    // Update button states
                    if (status === 'approved') {
                        row.find('.rmgc-approve-booking').prop('disabled', true);
                        row.find('.rmgc-reject-booking').prop('disabled', false);
                    } else if (status === 'rejected') {
                        row.find('.rmgc-approve-booking').prop('disabled', false);
                        row.find('.rmgc-reject-booking').prop('disabled', true);
                    }
                    
                    // Show success message
                    alert('Booking status updated successfully');
                } else {
                    alert('Error updating booking status: ' + response.data);
                }
            },
            error: function() {
                alert('Error updating booking status. Please try again.');
            }
        });
    }
});