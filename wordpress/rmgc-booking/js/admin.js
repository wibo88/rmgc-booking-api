jQuery(document).ready(function($) {
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
        open: function() {
            $('#new-note').val(''); // Clear the new note field
        },
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
        const bookingId = $(this).data('id');
        const timePrefs = $(this).closest('tr').find('.time-preferences').text().trim();
        $('#booking-id').val(bookingId);
        $('#time-preferences').text(timePrefs);
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
        let notesHtml = '';
        if (booking.notes && booking.notes.length) {
            notesHtml = booking.notes.map(note => `
                <div class="note-item">
                    <div class="note-meta">
                        By ${note.author} on ${new Date(note.date).toLocaleString()}
                    </div>
                    <div class="note-content">${note.note}</div>
                </div>
            `).join('');
        } else {
            notesHtml = '<p>No notes yet</p>';
        }
        
        $('#notes-history').html(notesHtml);
        $('#notes-dialog').dialog('open');
    });
    
    // Handle approval submission
    function handleApproval() {
        const bookingId = $('#booking-id').val();
        const assignedTime = $('#assigned-time').val();
        const note = $('#approval-note').val();
        
        if (!assignedTime) {
            alert('Please assign a tee time');
            return;
        }
        
        $.ajax({
            url: rmgcAdmin.ajaxurl,
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
                    console.error('Server error:', response);
                    alert('Error updating booking status: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                alert('Server error occurred: ' + textStatus);
            }
        });
    }
    
    // Handle rejection submission
    function handleRejection() {
        const bookingId = $('#rejection-booking-id').val();
        const note = $('#rejection-note').val();
        
        if (!note) {
            alert('Please provide a reason for rejection');
            return;
        }
        
        $.ajax({
            url: rmgcAdmin.ajaxurl,
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
                    console.error('Server error:', response);
                    alert('Error updating booking status: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                alert('Server error occurred: ' + textStatus);
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
            url: rmgcAdmin.ajaxurl,
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
                    console.error('Server error:', response);
                    alert('Error adding note: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                alert('Server error occurred: ' + textStatus);
            }
        });
    }

    // Add better error logging
    $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
        console.error('AJAX Error:', {
            event: event,
            jqxhr: jqxhr,
            settings: settings,
            thrownError: thrownError
        });
    });
});