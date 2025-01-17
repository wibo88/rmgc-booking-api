jQuery(document).ready(function($) {
    // Handle form submission
    $('#rmgc-booking').on('submit', function(e) {
        e.preventDefault();
        
        const bookingData = {
            date: $('#selected-date').val(),
            players: $('#players').val(),
            handicap: $('#handicap').val()
        };

        // Send to our API
        $.ajax({
            url: rmgcApi.apiUrl + '/api/booking',
            method: 'POST',
            headers: {
                'X-API-Key': rmgcApi.apiKey,
                'X-WP-Site': rmgcApi.siteUrl,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(bookingData),
            success: function(response) {
                $('#rmgc-booking-message')
                    .removeClass('error')
                    .addClass('success')
                    .html('Booking request submitted successfully!');
            },
            error: function(xhr) {
                const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
                $('#rmgc-booking-message')
                    .removeClass('success')
                    .addClass('error')
                    .html('Error: ' + error);
            }
        });
    });

    // Function to check date availability
    function checkDateAvailability(date) {
        $.ajax({
            url: rmgcApi.apiUrl + '/api/check-date/' + date,
            headers: {
                'X-API-Key': rmgcApi.apiKey,
                'X-WP-Site': rmgcApi.siteUrl
            },
            success: function(response) {
                if (response.valid) {
                    $('#selected-date').val(date);
                    $('#rmgc-booking-message').html('');
                } else {
                    $('#rmgc-booking-message')
                        .removeClass('success')
                        .addClass('error')
                        .html('Error: ' + response.reason);
                }
            }
        });
    }
});