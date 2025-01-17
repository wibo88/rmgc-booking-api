jQuery(document).ready(function($) {
    // Initialize main date picker
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
            $('#bookingDate').val(dateText);
            // When start date is selected, set minimum date for end date picker
            $('#embedded-calendar-end').datepicker('option', 'minDate', dateText);
            validateDateRange();
        }
    });

    // Initialize end date picker
    $('#embedded-calendar-end').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: 0,
        numberOfMonths: [2, 1],
        beforeShowDay: function(date) {
            var day = date.getDay();
            // Enable only Monday (1), Tuesday (2), and Friday (5)
            return [(day === 1 || day === 2 || day === 5)];
        },
        onSelect: function(dateText) {
            $('#lastDate').val(dateText);
            validateDateRange();
        }
    });

    // Validate date range
    function validateDateRange() {
        const startDate = $('#bookingDate').val();
        const endDate = $('#lastDate').val();
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (end < start) {
                $('#rmgc-booking-message')
                    .removeClass('success')
                    .addClass('error')
                    .html('Last available date must be after first available date');
                return false;
            }
        }
        
        $('#rmgc-booking-message').html('');
        return true;
    }

    // Handle form submission
    $('#rmgc-booking').on('submit', function(e) {
        e.preventDefault();
        
        // Verify date range
        if (!validateDateRange()) {
            return;
        }

        // Verify reCAPTCHA
        var recaptchaResponse = grecaptcha.getResponse();
        if (!recaptchaResponse) {
            $('#rmgc-booking-message')
                .removeClass('success')
                .addClass('error')
                .html('Please complete the reCAPTCHA verification');
            return;
        }

        // Collect form data
        const bookingData = {
            firstName: $('#firstName').val(),
            lastName: $('#lastName').val(),
            email: $('#email').val(),
            phone: $('#phone').val(),
            state: $('#state').val(),
            country: $('#country').val(),
            clubName: $('#clubName').val(),
            handicap: $('#handicap').val(),
            clubState: $('#clubState').val(),
            clubCountry: $('#clubCountry').val(),
            date: $('#bookingDate').val(),
            lastDate: $('#lastDate').val(),
            players: $('#players').val(),
            recaptchaResponse: recaptchaResponse
        };

        // Clear previous messages
        $('#rmgc-booking-message').removeClass('error success').html('');

        // Show loading state
        const submitButton = $(this).find('button[type="submit"]');
        const originalButtonText = submitButton.html();
        submitButton.html('Sending...').prop('disabled', true);

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
                $('#bookingDate, #lastDate').val('');
                grecaptcha.reset();
                
                // Scroll to message
                $('html, body').animate({
                    scrollTop: $('#rmgc-booking-message').offset().top - 100
                }, 500);
            },
            error: function(xhr) {
                console.error('Booking error:', xhr);
                const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
                $('#rmgc-booking-message')
                    .removeClass('success')
                    .addClass('error')
                    .html('Error: ' + error);
                
                // Scroll to error message
                $('html, body').animate({
                    scrollTop: $('#rmgc-booking-message').offset().top - 100
                }, 500);
                
                // Reset reCAPTCHA
                grecaptcha.reset();
            },
            complete: function() {
                // Reset button state
                submitButton.html(originalButtonText).prop('disabled', false);
            }
        });
    });
});