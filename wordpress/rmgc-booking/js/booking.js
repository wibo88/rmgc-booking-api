jQuery(document).ready(function($) {
    // Initialize date picker
    $('#embedded-calendar').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: 0,
        numberOfMonths: 1,
        beforeShowDay: function(date) {
            var day = date.getDay();
            // Enable only Monday (1), Tuesday (2), and Friday (5)
            return [(day === 1 || day === 2 || day === 5)];
        },
        onSelect: function(dateText) {
            $('#bookingDate').val(dateText);
        }
    });

    // Form submission handler
    $('#rmgc-booking').on('submit', function(e) {
        e.preventDefault();
        
        // Verify reCAPTCHA
        var recaptchaResponse = grecaptcha.getResponse();
        if (!recaptchaResponse) {
            $('#rmgc-booking-message')
                .removeClass('success')
                .addClass('error')
                .html('Please confirm you are not a robot');
            return;
        }

        // Get selected time preferences
        const timePreferences = [];
        $('input[name="timePreference[]"]:checked').each(function() {
            timePreferences.push($(this).val());
        });

        // Gather form data
        const bookingData = {
            date: $('#bookingDate').val(),
            players: $('#players').val(),
            handicap: $('#handicap').val(),
            email: $('#email').val(),
            firstName: $('#firstName').val(),
            lastName: $('#lastName').val(),
            phone: $('#phone').val(),
            state: $('#state').val(),
            country: $('#country').val(),
            clubName: $('#clubName').val(),
            clubState: $('#clubState').val(),
            clubCountry: $('#clubCountry').val(),
            timePreferences: timePreferences,
            recaptchaResponse: recaptchaResponse
        };

        // Clear previous messages
        $('#rmgc-booking-message').removeClass('error success').html('');

        // Show loading state
        const submitButton = $(this).find('button[type="submit"]');
        const originalButtonText = submitButton.text();
        submitButton.prop('disabled', true).text('Submitting...');

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
                    .html('Your booking request has been submitted successfully. We will contact you shortly.');
                
                // Clear form and reset reCAPTCHA
                $('#rmgc-booking')[0].reset();
                grecaptcha.reset();
                
                // Reset calendar
                $('#bookingDate').val('');
            },
            error: function(xhr) {
                console.error('Booking error:', xhr);
                const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred processing your request. Please try again.';
                $('#rmgc-booking-message')
                    .removeClass('success')
                    .addClass('error')
                    .html('Error: ' + error);
                
                // Reset reCAPTCHA
                grecaptcha.reset();
            },
            complete: function() {
                // Reset button state
                submitButton.prop('disabled', false).text(originalButtonText);
            }
        });
    });

    // Optional: Add validation for handicap field
    $('#handicap').on('input', function() {
        const value = parseInt($(this).val());
        if (value > 24) {
            $(this).val(24);
        }
    });
});