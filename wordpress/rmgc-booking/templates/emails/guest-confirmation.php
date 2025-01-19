<html>
<head>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
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
        .footer { 
            text-align: center; 
            padding: 20px; 
            font-size: 0.9em; 
            color: #666; 
        }
        .important-info {
            background-color: #f5f5f5;
            border-left: 4px solid #005b94;
            padding: 15px;
            margin: 20px 0;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Booking Request Received</h2>
        </div>
        
        <div class='content'>
            <p>Dear <?php echo esc_html($vars['booking']['firstName']); ?>,</p>
            
            <p>Thank you for your booking request at Royal Melbourne Golf Club. We have received your request for:</p>
            
            <div class='important-info'>
                <p><strong>Date:</strong> <?php echo esc_html($vars['formatted_date']); ?></p>
                <p><strong>Number of Players:</strong> <?php echo esc_html($vars['booking']['players']); ?></p>
                <p><strong>Time Preferences:</strong> <?php echo esc_html($vars['formatted_time_prefs']); ?></p>
            </div>
            
            <p>Our team will review your request and contact you shortly to confirm your booking.</p>
            
            <p>Please note the following important information:</p>
            <ul>
                <li>Bookings are subject to availability</li>
                <li>Please arrive 30 minutes before your confirmed tee time</li>
                <li>Proper golf attire is required (no denim, t-shirts, or cargo shorts)</li>
                <li>You will need to present proof of your current handicap on the day</li>
                <li>Green fees must be paid upon arrival</li>
            </ul>
            
            <p>If you need to make any changes to your booking request or have any questions, please contact our Pro Shop:</p>
            <p>Phone: (03) 9598 6755<br>
            Email: proshop@royalmelbourne.com.au</p>
        </div>
        
        <div class='footer'>
            <p><strong>Royal Melbourne Golf Club</strong></p>
            <p>Thank you for choosing to play with us</p>
        </div>
    </div>
</body>
</html>