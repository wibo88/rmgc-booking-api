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
        .booking-details {
            background-color: #e6f3ff;
            border: 1px solid #005b94;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }
        .important-info {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .checklist {
            padding-left: 20px;
        }
        .checklist li {
            margin-bottom: 10px;
            list-style-type: none;
            position: relative;
        }
        .checklist li:before {
            content: "✓";
            position: absolute;
            left: -20px;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Booking Confirmed</h2>
        </div>
        
        <div class='content'>
            <p>Dear <?php echo esc_html($vars['booking']['firstName']); ?>,</p>
            
            <p>Great news! Your booking request at Royal Melbourne Golf Club has been confirmed.</p>
            
            <div class='booking-details'>
                <h3>Your Tee Time Details</h3>
                <p><strong>Date:</strong> <?php echo esc_html($vars['formatted_date']); ?></p>
                <?php if ($vars['assigned_time']): ?>
                    <p><strong>Time:</strong> <?php echo esc_html($vars['assigned_time']); ?></p>
                <?php endif; ?>
                <p><strong>Number of Players:</strong> <?php echo esc_html($vars['booking']['players']); ?></p>
            </div>
            
            <div class='important-info'>
                <h3>Important Information</h3>
                <ul class='checklist'>
                    <li>Please arrive 30 minutes before your tee time</li>
                    <li>Report to the Pro Shop upon arrival</li>
                    <li>Bring proof of your current handicap</li>
                    <li>Proper golf attire is required</li>
                    <li>Green fees are payable upon arrival</li>
                </ul>
            </div>
            
            <h3>Golf Course Etiquette</h3>
            <ul>
                <li>Keep pace with the group in front</li>
                <li>Repair all divots and ball marks</li>
                <li>Rake bunkers after use</li>
                <li>Mobile phones should be on silent</li>
            </ul>
            
            <p>If you need to make any changes or have questions, please contact our Pro Shop:</p>
            <p>Phone: (03) 9598 6755<br>
            Email: proshop@royalmelbourne.com.au</p>
            
            <p><em>Please note: Cancellations must be made at least 48 hours in advance.</em></p>
        </div>
        
        <div class='footer'>
            <p><strong>Royal Melbourne Golf Club</strong></p>
            <p>We look forward to welcoming you to our course</p>
        </div>
    </div>
</body>
</html>