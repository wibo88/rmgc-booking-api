require('dotenv').config();
const express = require('express');
const cors = require('cors');

const app = express();

// Middleware
app.use(cors());
app.use(express.json());

// Simple test endpoint
app.get('/api/test', (req, res) => {
  res.json({ message: 'API is working!' });
});

// Endpoint to receive booking requests from WordPress
app.post('/api/booking', (req, res) => {
  const bookingData = req.body;
  console.log('Received booking request:', bookingData);
  
  // Here we'll later add validation and processing
  res.json({
    status: 'received',
    data: bookingData
  });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});