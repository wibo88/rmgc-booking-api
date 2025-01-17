require('dotenv').config();
const express = require('express');
const cors = require('cors');
const { isValidBookingDay } = require('./utils/dateValidator');

const app = express();

// Middleware
app.use(cors());
app.use(express.json());

// Test endpoint
app.get('/api/test', (req, res) => {
  res.json({ message: 'API is working!' });
});

// Check date availability
app.get('/api/check-date/:date', (req, res) => {
  try {
    const dateToCheck = new Date(req.params.date);
    
    if (isNaN(dateToCheck.getTime())) {
      return res.status(400).json({ 
        error: 'Invalid date format. Please use YYYY-MM-DD' 
      });
    }

    const validation = isValidBookingDay(dateToCheck);
    res.json(validation);
  } catch (error) {
    res.status(500).json({ error: 'Error checking date availability' });
  }
});

// Process booking request
app.post('/api/booking', (req, res) => {
  const { date, players, handicap } = req.body;

  // Validate date
  const bookingDate = new Date(date);
  const dateValidation = isValidBookingDay(bookingDate);
  
  if (!dateValidation.valid) {
    return res.status(400).json({ 
      error: dateValidation.reason 
    });
  }

  // Validate handicap
  if (handicap > 24) {
    return res.status(400).json({
      error: 'Maximum handicap allowed is 24'
    });
  }

  // Validate group size
  if (players < 2 || players > 4) {
    return res.status(400).json({
      error: 'Group size must be between 2 and 4 players'
    });
  }

  // If all validations pass, accept the booking
  res.json({
    status: 'success',
    message: 'Booking request received successfully',
    data: {
      date: date,
      players: players,
      handicap: handicap,
      // We'll add more booking details here later
    }
  });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});