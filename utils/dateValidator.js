/**
 * Utility functions for validating booking dates for Royal Melbourne Golf Club
 */

// List of Victorian public holidays (2025)
const PUBLIC_HOLIDAYS_2025 = [
  '2025-01-01', // New Year's Day
  '2025-01-27', // Australia Day Holiday
  '2025-03-10', // Labour Day
  '2025-04-18', // Good Friday
  '2025-04-21', // Easter Monday
  '2025-04-25', // Anzac Day
  '2025-06-09', // King's Birthday
  '2025-09-26', // AFL Grand Final Friday
  '2025-11-04', // Melbourne Cup
  '2025-12-25', // Christmas Day
  '2025-12-26', // Boxing Day
];

function isPublicHoliday(date) {
  return PUBLIC_HOLIDAYS_2025.includes(date.toISOString().split('T')[0]);
}

function isValidBookingDay(date) {
  const dayOfWeek = date.getDay();
  const isWeekdayAllowed = [1, 2, 5].includes(dayOfWeek); // Monday, Tuesday, Friday
  
  if (!isWeekdayAllowed) {
    return {
      valid: false,
      reason: 'Bookings are only available on Mondays, Tuesdays, and Fridays'
    };
  }

  if (isPublicHoliday(date)) {
    return {
      valid: false,
      reason: 'Bookings are not available on public holidays'
    };
  }

  // Check if date is in the future
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  if (date < today) {
    return {
      valid: false,
      reason: 'Booking date must be in the future'
    };
  }

  // Can add more rules here (e.g., maximum booking window)
  
  return {
    valid: true
  };
}

module.exports = {
  isValidBookingDay,
  isPublicHoliday
};