const getBookingEmailTemplate = (booking) => {
    const formatTimePreferences = (prefs) => {
        return prefs.map(pref => {
            switch(pref) {
                case 'early_morning': return 'Early Morning';
                case 'morning': return 'Morning';
                case 'afternoon': return 'Afternoon';
                case 'late_afternoon': return 'Late Afternoon';
                default: return pref;
            }
        }).join(', ');
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-AU', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    return `
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #005b94;
                    color: white;
                    padding: 20px;
                    text-align: center;
                }
                .content {
                    padding: 20px;
                    background-color: #f9f9f9;
                }
                .booking-details {
                    margin: 20px 0;
                    padding: 15px;
                    background-color: white;
                    border-radius: 5px;
                }
                .footer {
                    text-align: center;
                    padding: 20px;
                    font-size: 0.9em;
                    color: #666666;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Booking Request Received</h1>
                </div>
                
                <div class="content">
                    <p>Dear ${booking.name},</p>
                    
                    <p>Thank you for your booking request at Royal Melbourne Golf Club. We have received your request with the following details:</p>
                    
                    <div class="booking-details">
                        <p><strong>Date:</strong> ${formatDate(booking.date)}</p>
                        <p><strong>Number of Players:</strong> ${booking.players}</p>
                        <p><strong>Time Preferences:</strong> ${formatTimePreferences(booking.timePreferences)}</p>
                        <p><strong>Handicap:</strong> ${booking.handicap}</p>
                        <p><strong>Club:</strong> ${booking.clubName}</p>
                    </div>
                    
                    <p>Our team will review your request and contact you shortly to confirm your booking and provide further details.</p>
                    
                    <p>Please note:</p>
                    <ul>
                        <li>Bookings are subject to availability</li>
                        <li>Players must arrive 30 minutes before their tee time</li>
                        <li>Proper golf attire is required</li>
                        <li>Proof of handicap may be required on the day</li>
                    </ul>
                    
                    <p>If you need to make any changes to your booking or have any questions, please contact us.</p>
                </div>
                
                <div class="footer">
                    <p>Royal Melbourne Golf Club</p>
                    <p>Thank you for choosing to play with us</p>
                </div>
            </div>
        </body>
        </html>
    `;
};

module.exports = {
    getBookingEmailTemplate
};