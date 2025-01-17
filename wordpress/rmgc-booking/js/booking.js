jQuery(document).ready(function($) {
    // Initialize datepicker
    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: 0,
        beforeShowDay: function(date) {
            var day = date.getDay();
            // Enable only Monday (1), Tuesday (2), and Friday (5)
            return [(day === 1 || day === 2 || day === 5)];
        }
    });

    // Handle form submission
    $('#rmgc-booking').on('submit', function(e) {
        e.preventDefault();
        
        const bookingData = {
            date: $('#booking-date').val(),
            players: $('#players').val(),
            handicap: $('#handicap').val()
        };

        // Clear previous messages
        $('#rmgc-booking-message').removeClass('error success').html('');

        console.log('Sending booking request:', bookingData);
        console.log('API URL:', rmgcApi.apiUrl);

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
                
                // Clear form
                $('#rmgc-booking')[0].reset();
            },
            error: function(xhr) {
                console.error('Booking error:', xhr);
                const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
                $('#rmgc-booking-message')
                    .removeClass('success')
                    .addClass('error')
                    .html('Error: ' + error);
            }
        });
    });
});