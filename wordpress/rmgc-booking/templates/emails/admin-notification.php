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
        .label { 
            font-weight: bold; 
        }
        .actions {
            margin: 20px 0;
            text-align: center;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #005b94;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>New Booking Request</h2>
        </div>
        
        <div class='content'>
            <h3>Visitor Details</h3>
            <p><span class='label'>Name:</span> <?php echo esc_html($vars['booking']['firstName'] . ' ' . $vars['booking']['lastName']); ?></p>
            <p><span class='label'>Email:</span> <?php echo esc_html($vars['booking']['email']); ?></p>
            <p><span class='label'>Phone:</span> <?php echo esc_html($vars['booking']['phone']); ?></p>
            <p><span class='label'>Location:</span> <?php echo esc_html($vars['booking']['state'] . ', ' . $vars['booking']['country']); ?></p>
            
            <h3>Golf Details</h3>
            <p><span class='label'>Club:</span> <?php echo esc_html($vars['booking']['clubName']); ?></p>
            <p><span class='label'>Handicap:</span> <?php echo esc_html($vars['booking']['handicap']); ?></p>
            <p><span class='label'>Club Location:</span> <?php echo esc_html($vars['booking']['clubState'] . ', ' . $vars['booking']['clubCountry']); ?></p>
            
            <h3>Booking Details</h3>
            <p><span class='label'>Date:</span> <?php echo esc_html($vars['formatted_date']); ?></p>
            <p><span class='label'>Number of Players:</span> <?php echo esc_html($vars['booking']['players']); ?></p>
            <p><span class='label'>Time Preferences:</span> <?php echo esc_html($vars['formatted_time_prefs']); ?></p>

            <div class='actions'>
                <a href='<?php echo esc_url(admin_url('admin.php?page=rmgc-booking')); ?>' class='button'>View Booking</a>
            </div>
        </div>
        
        <div class='footer'>
            <p>This is an automated message from your website's booking system.</p>
        </div>
    </div>
</body>
</html>