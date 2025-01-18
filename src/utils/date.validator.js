const validateBookingDate = (dateString) => {
    try {
        const date = new Date(dateString);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        // Check if date is valid
        if (isNaN(date.getTime())) {
            return {
                isValid: false,
                message: 'Invalid date format'
            };
        }

        // Check if date is in the past
        if (date < today) {
            return {
                isValid: false,
                message: 'Cannot book dates in the past'
            };
        }

        // Get day of week (0 = Sunday, 1 = Monday, etc.)
        const dayOfWeek = date.getDay();

        // Check if day is Monday (1), Tuesday (2), or Friday (5)
        if (![1, 2, 5].includes(dayOfWeek)) {
            return {
                isValid: false,
                message: 'Bookings are only available on Mondays, Tuesdays, and Fridays'
            };
        }

        // All validations passed
        return {
            isValid: true,
            message: 'Date is valid'
        };
    } catch (error) {
        console.error('Date validation error:', error);
        return {
            isValid: false,
            message: 'Error validating date'
        };
    }
};

module.exports = {
    validateBookingDate
};