jQuery(document).ready(function($) {
    // Initialize calendar...
    [Previous calendar code remains the same]

    // Form submission handler
    $('#rmgc-booking').on('submit', async function(e) {
        e.preventDefault();
        
        // Clear previous messages
        $('#rmgc-booking-message').removeClass('error success').html('');
        
        // Show loading state
        const submitButton = $(this).find('button[type="submit"]');
        const originalButtonText = submitButton.text();
        submitButton.prop('disabled', true).text('Submitting...');

        try {
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

            console.log('Form data:', bookingData);

            // Validate form data
            const validationError = validateForm(bookingData);
            if (validationError) {
                throw new Error(validationError);
            }

            // Get reCAPTCHA response
            console.log('Getting reCAPTCHA response...');
            const recaptchaResponse = grecaptcha.getResponse();
            console.log('reCAPTCHA response length:', recaptchaResponse.length);
            
            if (!recaptchaResponse) {
                throw new Error('Please complete the reCAPTCHA verification');
            }

            // Add reCAPTCHA response to booking data
            bookingData.recaptchaResponse = recaptchaResponse;

            // Send to WordPress AJAX
            const response = await $.ajax({
                url: rmgcAjax.ajaxurl,
                method: 'POST',
                data: {
                    action: 'rmgc_create_booking',
                    nonce: rmgcAjax.nonce,
                    booking: JSON.stringify(bookingData)
                }
            });

            console.log('Server response:', response);

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
                throw new Error(response.data || 'An error occurred processing your request');
            }
        } catch (error) {
            console.error('Submission error:', error);
            
            $('#rmgc-booking-message')
                .removeClass('success')
                .addClass('error')
                .html('Error: ' + (error.message || 'An error occurred processing your request. Please try again.'));
            
            // Reset reCAPTCHA
            grecaptcha.reset();
        } finally {
            // Reset button state
            submitButton.prop('disabled', false).text(originalButtonText);
        }
    });

    // Validation functions remain the same
})