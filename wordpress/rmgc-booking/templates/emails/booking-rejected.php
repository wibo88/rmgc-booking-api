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
        .booking-info {
            background-color: #f5f5f5;
            border-left: 4px solid #005b94;
            padding: 15px;
            margin: 20px 0;
        }
        .alternative-options {
            background-color: #e6f3ff;
            border: 1px solid #005b94;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Booking Update</h2>
        </div>
        
        <div class='content'>
            <p>Dear <?php echo esc_html($vars['booking']['firstName']); ?>,</p>
            
            <p>Thank you for your interest in playing at Royal Melbourne Golf Club. Unfortunately, we are unable to accommodate your booking request for:</p>
            
            <div class='booking-info'>
                <p><strong>Date:</strong> <?php echo esc_html($vars['formatted_date']); ?></p>
                <p><strong>Number of Players:</strong> <?php echo esc_html($vars['booking']['players']); ?></p>
            </div>
            
            <p>This could be due to several factors including:</p>
            <ul>
                <li>Course availability</li>
                <li>Previously scheduled events</li>
                <li>Course maintenance</li>
                <li>Member competitions</li>
            </ul>
            
            <div class='alternative-options'>
                <h3>Alternative Options</h3>
                <p>We would be happy to assist you in finding an alternative date for your visit. Please feel free to:</p>
                <ul>
                    <li>Submit a new booking request for a different date</li>
                    <li>Contact our Pro Shop to discuss available times</li>
                    <li>Join our waiting list for cancellations</li>
                </ul>
            </div>
            
            <p>If you would like to explore other dates or have any questions, please contact our Pro Shop:</p>
            <p>Phone: (03) 9598 6755<br>
            Email: proshop@royalmelbourne.com.au</p>
        </div>
        
        <div class='footer'>
            <p><strong>Royal Melbourne Golf Club</strong></p>
            <p>We apologize for any inconvenience and hope to welcome you to our course in the future</p>
        </div>
    </div>
</body>
</html>