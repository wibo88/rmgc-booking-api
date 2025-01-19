jQuery(document).ready(function($) {
    // Initialize date picker with custom navigation
    var calendar = $('#embedded-calendar').datepicker({
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
        },
        onChangeMonthYear: function(year, month) {
            updateHeaderDate(year, month);
        }
    }).data('datepicker');

    // Initial header update
    var currentDate = new Date();
    updateHeaderDate(currentDate.getFullYear(), currentDate.getMonth() + 1);

    // Custom navigation handlers
    $('#custom-prev').click(function() {
        var current = $('#embedded-calendar').datepicker('getDate');
        current.setMonth(current.getMonth() - 1);
        $('#embedded-calendar').datepicker('setDate', current);
        updateHeaderDate(current.getFullYear(), current.getMonth() + 1);
    });

    $('#custom-next').click(function() {
        var current = $('#embedded-calendar').datepicker('getDate');
        current.setMonth(current.getMonth() + 1);
        $('#embedded-calendar').datepicker('setDate', current);
        updateHeaderDate(current.getFullYear(), current.getMonth() + 1);
    });

    function updateHeaderDate(year, month) {
        var monthNames = [
            "January", "February", "March", "April",
            "May", "June", "July", "August",
            "September", "October", "November", "December"
        ];
        $('#custom-month-year').text(monthNames[month - 1] + ' ' + year);
    }

    // Form submission handler
    $('#rmgc-booking').on('submit', function(e) {
        e.preventDefault();
        
        // Get selected time preferences
        const timePreferences = [];
        $('input[name="timePreference[]"]:checked').each(function() {
            timePreferences.push($(this).val());
        });

        // Validate time preferences
        if (timePreferences.length === 0) {
            $('#rmgc-booking-message')
                .removeClass('success')
                .addClass('error')
                .html('Please select at least one time preference');
            return;
        }

        // Verify reCAPTCHA
        var recaptchaResponse = grecaptcha.getResponse();
        console.log('reCAPTCHA response:', recaptchaResponse); // Debug log
        
        if (!recaptchaResponse) {
            $('#rmgc-booking-message')
                .removeClass('success')
                .addClass('error')
                .html('Please confirm you are not a robot');
            return;
        }

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

        console.log('Submitting booking data:', bookingData); // Debug log

        // Clear previous messages
        $('#rmgc-booking-message').removeClass('error success').html('');

        // Show loading state
        const submitButton = $(this).find('button[type="submit"]');
        const originalButtonText = submitButton.text();
        submitButton.prop('disabled', true).text('Submitting...');

        // Send to WordPress AJAX
        $.ajax({
            url: rmgcAjax.ajaxurl,
            method: 'POST',
            data: {
                action: 'rmgc_create_booking',
                nonce: rmgcAjax.nonce,
                booking: JSON.stringify(bookingData)
            },
            success: function(response) {
                console.log('Server response:', response); // Debug log
                if (response.success) {
                    $('#rmgc-booking-message')
                        .removeClass('error')
                        .addClass('success')
                        .html('Your booking request has been submitted successfully. We will contact you shortly.');
                    
                    // Clear form and reset reCAPTCHA
                    $('#rmgc-booking')[0].reset();
                    grecaptcha.reset();
                    
                    // Reset calendar
                    $('#bookingDate').val('');
                } else {
                    $('#rmgc-booking-message')
                        .removeClass('success')
                        .addClass('error')
                        .html('Error: ' + (response.data || 'An error occurred processing your request. Please try again.'));
                    
                    // Reset reCAPTCHA
                    grecaptcha.reset();
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', {xhr: xhr, status: status, error: error}); // Debug log
                $('#rmgc-booking-message')
                    .removeClass('success')
                    .addClass('error')
                    .html('Error: An error occurred processing your request. Please try again.');
                
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