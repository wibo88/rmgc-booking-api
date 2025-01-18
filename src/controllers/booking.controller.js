const bookingSchema = require('../schemas/booking.schema');
const { sendBookingEmail } = require('../services/email.service');
const { verifyRecaptcha } = require('../services/recaptcha.service');
const { validateBookingDate } = require('../utils/date.validator');

const createBooking = async (req, res) => {
    try {
        // Validate request body against schema
        const { error, value } = bookingSchema.validate(req.body);
        if (error) {
            return res.status(400).json({ error: error.details[0].message });
        }

        // Verify reCAPTCHA
        const recaptchaValid = await verifyRecaptcha(value.recaptchaResponse);
        if (!recaptchaValid) {
            return res.status(400).json({ error: 'ReCAPTCHA verification failed' });
        }

        // Validate booking date (Monday, Tuesday, Friday only)
        const dateValid = validateBookingDate(value.date);
        if (!dateValid.isValid) {
            return res.status(400).json({ error: dateValid.message });
        }

        // Process time preferences
        const timePreferences = value.timePreferences;
        if (!timePreferences || timePreferences.length === 0) {
            return res.status(400).json({ error: 'Please select at least one time preference' });
        }

        // Store booking in database (implement your database logic here)
        // const booking = await BookingModel.create(value);

        // Send email notification
        await sendBookingEmail({
            to: value.email,
            name: `${value.firstName} ${value.lastName}`,
            date: value.date,
            players: value.players,
            timePreferences: value.timePreferences,
            handicap: value.handicap,
            clubName: value.clubName || 'Not provided'
        });

        // Return success response
        res.status(201).json({
            message: 'Booking request received successfully',
            booking: {
                date: value.date,
                name: `${value.firstName} ${value.lastName}`,
                email: value.email,
                players: value.players,
                timePreferences: value.timePreferences
            }
        });

    } catch (err) {
        console.error('Booking error:', err);
        res.status(500).json({ error: 'An error occurred processing your booking request' });
    }
};

module.exports = {
    createBooking
};