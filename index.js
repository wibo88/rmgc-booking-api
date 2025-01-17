require('dotenv').config();
const express = require('express');
const cors = require('cors');
const { isValidBookingDay } = require('./utils/dateValidator');

const app = express();

// CORS configuration
const corsOptions = {
  origin: 'https://www.royalmelbourne.com.au',
  methods: ['GET', 'POST'],
  allowedHeaders: ['Content-Type', 'X-API-Key', 'X-WP-Site'],
  credentials: true
};

// Middleware
app.use(cors(corsOptions));
app.use(express.json());

// Test endpoint
app.get('/api/test', (req, res) => {
  console.log('Test endpoint hit');
  res.json({ message: 'API is working!' });
});

// Check date availability
app.get('/api/check-date/:date', (req, res) => {
  console.log('Date check request received:', req.params.date);
  try {
    const dateToCheck = new Date(req.params.date);
    
    if (isNaN(dateToCheck.getTime())) {
      console.log('Invalid date format received');
      return res.status(400).json({ 
        error: 'Invalid date format. Please use YYYY-MM-DD' 
      });
    }

    const validation = isValidBookingDay(dateToCheck);
    console.log('Validation result:', validation);
    res.json(validation);
  } catch (error) {
    console.error('Error checking date:', error);
    res.status(500).json({ error: 'Error checking date availability' });
  }
});

// Process booking request
app.post('/api/booking', (req, res) => {
  console.log('Booking request received:', req.body);
  const { date, players, handicap } = req.body;

  // Validate date
  const bookingDate = new Date(date);
  const dateValidation = isValidBookingDay(bookingDate);
  
  if (!dateValidation.valid) {
    console.log('Date validation failed:', dateValidation.reason);
    return res.status(400).json({ 
      error: dateValidation.reason 
    });
  }

  // Validate handicap
  if (handicap > 24) {
    console.log('Handicap validation failed');
    return res.status(400).json({
      error: 'Maximum handicap allowed is 24'
    });
  }

  // Validate group size
  if (players < 2 || players > 4) {
    console.log('Group size validation failed');
    return res.status(400).json({
      error: 'Group size must be between 2 and 4 players'
    });
  }

  // If all validations pass, accept the booking
  console.log('Booking validated successfully');
  res.json({
    status: 'success',
    message: 'Booking request received successfully',
    data: {
      date: date,
      players: players,
      handicap: handicap
    }
  });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
  console.log('CORS enabled for:', corsOptions.origin);
});