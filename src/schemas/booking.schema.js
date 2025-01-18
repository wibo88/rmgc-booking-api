const Joi = require('joi');

const bookingSchema = Joi.object({
    date: Joi.date()
        .required()
        .messages({
            'date.base': 'Please provide a valid date',
            'any.required': 'Booking date is required'
        }),
    
    timePreferences: Joi.array()
        .items(Joi.string().valid('early_morning', 'morning', 'afternoon', 'late_afternoon'))
        .min(1)
        .required()
        .messages({
            'array.min': 'Please select at least one time preference',
            'any.required': 'Time preferences are required'
        }),

    players: Joi.number()
        .integer()
        .min(1)
        .max(4)
        .required()
        .messages({
            'number.base': 'Number of players must be a number',
            'number.min': 'Minimum 1 player required',
            'number.max': 'Maximum 4 players allowed'
        }),

    handicap: Joi.number()
        .min(0)
        .max(24)
        .required()
        .messages({
            'number.base': 'Handicap must be a number',
            'number.min': 'Handicap cannot be negative',
            'number.max': 'Maximum handicap allowed is 24'
        }),

    email: Joi.string()
        .email()
        .required()
        .messages({
            'string.email': 'Please provide a valid email address',
            'any.required': 'Email is required'
        }),

    firstName: Joi.string()
        .required()
        .messages({
            'any.required': 'First name is required'
        }),

    lastName: Joi.string()
        .required()
        .messages({
            'any.required': 'Last name is required'
        }),

    phone: Joi.string()
        .allow('')
        .optional(),

    state: Joi.string()
        .allow('')
        .optional(),

    country: Joi.string()
        .allow('')
        .optional(),

    clubName: Joi.string()
        .allow('')
        .optional(),

    clubState: Joi.string()
        .allow('')
        .optional(),

    clubCountry: Joi.string()
        .allow('')
        .optional(),

    recaptchaResponse: Joi.string()
        .required()
        .messages({
            'any.required': 'ReCAPTCHA verification is required'
        })
});

module.exports = bookingSchema;