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

    // Form validation helper
    function validateForm(bookingData) {
        if (!bookingData.date) {
            return 'Please select a booking date';
        }
        if (!bookingData.players) {
            return 'Please select the number of players';
        }
        if (!bookingData.handicap || bookingData.handicap > 24) {
            return 'Please enter a valid handicap (maximum 24)';
        }
        if (!bookingData.email || !bookingData.email.includes('@')) {
            return 'Please enter a valid email address';
        }
        if (!bookingData.firstName || !bookingData.lastName) {
            return 'Please enter your full name';
        }
        if (!bookingData.phone) {
            return 'Please enter your phone number';
        }
        if (bookingData.timePreferences.length === 0) {
            return 'Please select at least one time preference';
        }
        return null;
    }

    // Form submission handler
    $('#rmgc-booking').on('submit', function(e) {
        e.preventDefault();
        
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
            timePreferences: timePreferences
        };

        // Validate form data
        const validationError = validateForm(bookingData);
        if (validationError) {
            $('#rmgc-booking-message')
                .removeClass('success')
                .addClass('error')
                .html(validationError);
            return;
        }

        // Verify reCAPTCHA
        let recaptchaResponse;
        try {
            console.log('Checking reCAPTCHA...'); // Debug log
            recaptchaResponse = grecaptcha.getResponse();
            console.log('reCAPTCHA response:', recaptchaResponse ? 'received' : 'missing'); // Debug log
            
            if (!recaptchaResponse) {
                $('#rmgc-booking-message')
                    .removeClass('success')
                    .addClass('error')
                    .html('Please confirm you are not a robot');
                return;
            }
        } catch (error) {
            console.error('reCAPTCHA error:', error);
            $('#rmgc-booking-message')
                .removeClass('success')
                .addClass('error')
                .html('Error with security verification. Please refresh the page and try again.');
            return;
        }

        // Add reCAPTCHA response to booking data
        bookingData.recaptchaResponse = recaptchaResponse;

        // Clear previous messages
        $('#rmgc-booking-message').removeClass('error success').html('');

        // Show loading state
        const submitButton = $(this).find('button[type="submit"]');
        const originalButtonText = submitButton.text();
        submitButton.prop('disabled', true).text('Submitting...');

        // Log the data we're about to send
        console.log('Sending booking data:', {
            action: 'rmgc_create_booking',
            nonce: rmgcAjax.nonce,
            booking: bookingData
        });

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
                    $('#bookingDate').val('');
                } else {
                    console.error('Response error:', response.data);
                    $('#rmgc-booking-message')
                        .removeClass('success')
                        .addClass('error')
                        .html('Error: ' + (response.data || 'An error occurred processing your request. Please try again.'));
                    
                    // Reset reCAPTCHA
                    grecaptcha.reset();
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                
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

    // Handicap field validation
    $('#handicap').on('input', function() {
        const value = parseInt($(this).val());
        if (value > 24) {
            $(this).val(24);
        }
    });
});