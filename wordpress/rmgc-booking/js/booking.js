jQuery(document).ready(function($) {
    // Initialize embedded calendar
    $('#embedded-calendar').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: 0,
        numberOfMonths: [2, 1],
        beforeShowDay: function(date) {
            var day = date.getDay();
            // Enable only Monday (1), Tuesday (2), and Friday (5)
            return [(day === 1 || day === 2 || day === 5)];
        },
        onSelect: function(dateText) {
            $('#booking-date').val(dateText);
        }
    });

    // Handle form submission
    $('#rmgc-booking').on('submit', function(e) {
        e.preventDefault();
        
        // Verify reCAPTCHA
        var recaptchaResponse = grecaptcha.getResponse();
        if (!recaptchaResponse) {
            $('#rmgc-booking-message')
                .removeClass('success')
                .addClass('error')
                .html('Please complete the reCAPTCHA verification');
            return;
        }

        const bookingData = {
            date: $('#booking-date').val(),
            players: $('#players').val(),
            handicap: $('#handicap').val(),
            email: $('#email').val(),
            recaptchaResponse: recaptchaResponse
        };

        // Clear previous messages
        $('#rmgc-booking-message').removeClass('error success').html('');

        // Send to API
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
                    .html('Booking request submitted successfully! We will contact you shortly.');
                
                // Clear form and reset reCAPTCHA
                $('#rmgc-booking')[0].reset();
                grecaptcha.reset();
            },
            error: function(xhr) {
                console.error('Booking error:', xhr);
                const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
                $('#rmgc-booking-message')
                    .removeClass('success')
                    .addClass('error')
                    .html('Error: ' + error);
                
                // Reset reCAPTCHA
                grecaptcha.reset();
            }
        });
    });
});