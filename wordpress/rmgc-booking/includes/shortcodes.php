<?php
function rmgc_booking_form_shortcode() {
    ob_start();
    ?>
    <div id="rmgc-booking-form" class="rmgc-booking-container">
        <form id="rmgc-booking" class="rmgc-form">
            <!-- Your Details Section -->
            <div class="form-section">
                <h3>Your Details</h3>
                <div class="form-row">
                    <div class="form-group half">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName" required>
                    </div>
                    <div class="form-group half">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group half">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group half">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group half">
                        <label for="state">State</label>
                        <input type="text" id="state" name="state">
                    </div>
                    <div class="form-group half">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country">
                    </div>
                </div>
            </div>

            <!-- Golf Details Section -->
            <div class="form-section">
                <h3>Golf Details</h3>
                <div class="form-row">
                    <div class="form-group half">
                        <label for="clubName">Club Name</label>
                        <input type="text" id="clubName" name="clubName">
                    </div>
                    <div class="form-group half">
                        <label for="handicap">Handicap</label>
                        <input type="number" id="handicap" name="handicap" min="0" max="24" required>
                        <small>Maximum handicap allowed is 24</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group half">
                        <label for="clubState">Club State</label>
                        <input type="text" id="clubState" name="clubState">
                    </div>
                    <div class="form-group half">
                        <label for="clubCountry">Club Country</label>
                        <input type="text" id="clubCountry" name="clubCountry">
                    </div>
                </div>
            </div>

            <!-- Booking Details Section -->
            <div class="form-section">
                <h3>Your Booking</h3>
                <small class="section-desc">Visitor Booking times (subject to availability) Mondays, Tuesdays and Fridays 10.30am - 11.30am</small>
                
                <div class="form-row">
                    <div class="form-group full">
                        <div class="custom-calendar-wrap">
                            <div id="custom-inner" class="custom-inner">
                                <div class="custom-header clearfix">
                                    <nav>
                                        <span id="custom-prev" class="custom-prev"></span>
                                        <span id="custom-next" class="custom-next"></span>
                                    </nav>
                                    <h2 id="custom-month" class="custom-month"></h2>
                                    <h3 id="custom-year" class="custom-year"></h3>
                                </div>
                                <div id="embedded-calendar" class="fc-calendar-container"></div>
                                <input type="hidden" id="bookingDate" name="date" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="players">Number of Players</label>
                        <select id="players" name="players" required>
                            <option value="">Select number of players</option>
                            <option value="1">1 Player</option>
                            <option value="2">2 Players</option>
                            <option value="3">3 Players</option>
                            <option value="4">4 Players</option>
                        </select>
                    </div>
                    <div class="form-group half">
                        <div class="time-preferences">
                            <label>Time of Day Preference</label>
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="timePreference[]" value="early_morning">
                                    Early Morning
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="timePreference[]" value="morning">
                                    Morning
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="timePreference[]" value="afternoon">
                                    Afternoon
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="timePreference[]" value="late_afternoon">
                                    Late Afternoon
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="g-recaptcha" data-sitekey="<?php echo esc_attr(get_option('rmgc_recaptcha_site_key')); ?>" data-size="compact" data-theme="light"></div>
            
            <button type="submit" class="submit-button">Submit Booking Request</button>
        </form>
        <div id="rmgc-booking-message"></div>
    </div>
    <style>
        /* Form Styles */
        .rmgc-form {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .form-section h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.2em;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        .form-group {
            padding: 0 10px;
            margin-bottom: 20px;
            box-sizing: border-box;
        }
        .form-group.half {
            width: 50%;
        }
        .form-group.full {
            width: 100%;
        }
        @media (max-width: 768px) {
            .form-group.half {
                width: 100%;
            }
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group small {
            display: block;
            color: #666;
            margin-top: 5px;
            font-size: 0.85em;
        }
        .time-preferences {
            margin-top: 0;
        }
        .checkbox-group {
            margin-top: 10px;
        }
        .checkbox-label {
            display: block;
            margin-bottom: 8px;
            font-weight: normal;
            color: #333;
        }
        .checkbox-label input[type="checkbox"] {
            margin-right: 8px;
            width: auto;
        }
        .section-desc {
            display: block;
            margin-bottom: 20px;
            color: #666;
            font-style: italic;
        }
        .submit-button {
            background-color: #005b94;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 20px;
        }
        .submit-button:hover {
            background-color: #004675;
        }
        #rmgc-booking-message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }
        #rmgc-booking-message.error {
            background-color: #ffe6e6;
            color: #d63031;
            border: 1px solid #fab1a0;
        }
        #rmgc-booking-message.success {
            background-color: #e6ffe6;
            color: #27ae60;
            border: 1px solid #a8e6cf;
        }
        .g-recaptcha {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }

        /* Calendar Styles */
        .custom-calendar-wrap {
            margin: 10px auto;
            position: relative;
            overflow: hidden;
        }
        .custom-inner {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .custom-inner:before,
        .custom-inner:after {
            content: '';
            width: 99%;
            height: 50%;
            position: absolute;
            background: #f6f6f6;
            bottom: -4px;
            left: 0.5%;
            z-index: -1;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .custom-inner:after {
            content: '';
            width: 98%;
            bottom: -7px;
            left: 1%;
            z-index: -2;
        }
        .custom-header {
            background: #ef4f69;
            padding: 5px 10px 10px 20px;
            height: 70px;
            position: relative;
            border-top: 5px solid #ef4f69;
            border-bottom: 1px solid #ddd;
        }
        .custom-header h2,
        .custom-header h3 {
            text-align: center;
            text-transform: uppercase;
            color: #fff;
        }
        .custom-header h2 {
            font-weight: 700;
            font-size: 18px;
            margin-top: 10px;
        }
        .custom-header h3 {
            font-size: 10px;
            font-weight: 700;
        }
        .custom-header nav span {
            position: absolute;
            top: 17px;
            width: 30px;
            height: 30px;
            color: transparent;
            cursor: pointer;
            margin: 0 1px;
            font-size: 20px;
            line-height: 30px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            text-align: center;
        }
        .custom-header nav span:first-child {
            left: 5px;
        }
        .custom-header nav span:last-child {
            right: 5px;
        }
        .custom-header nav span:hover {
            background: rgba(255,255,255,0.3);
        }
        .custom-header nav span:before {
            color: #fff;
            position: absolute;
            text-align: center;
            width: 100%;
        }
        .custom-header nav span.custom-prev:before {
            content: '\25c2';
        }
        .custom-header nav span.custom-next:before {
            content: '\25b8';
        }

        /* Hide jQuery UI Datepicker header */
        .ui-datepicker-header {
            display: none !important;
        }
        
        .fc-calendar-container {
            height: 400px;
            width: auto;
            padding: 30px;
            background: #f6f6f6;
            box-shadow: inset 0 1px rgba(255,255,255,0.8);
        }
        
        .ui-datepicker {
            width: 100% !important;
            padding: 0 !important;
            border: none !important;
            background: transparent !important;
        }
        
        .ui-datepicker th {
            background: transparent;
            color: #ef4f69;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            padding: 8px 0;
        }
        
        .ui-datepicker td {
            background: #fff;
            border: 1px solid #ddd;
            padding: 0 !important;
            text-align: center;
        }
        
        .ui-datepicker td span,
        .ui-datepicker td a {
            text-align: center !important;
            padding: 8px !important;
            background: #fff !important;
            border: none !important;
            color: #686a6e !important;
            font-weight: 400 !important;
        }
        
        .ui-datepicker td.ui-datepicker-current-day a {
            background: #ef4f69 !important;
            color: #fff !important;
        }
        
        .ui-datepicker td.ui-datepicker-today a {
            background: #f6f6f6 !important;
            font-weight: bold !important;
        }
        
        .ui-datepicker td.ui-state-disabled {
            opacity: 0.35;
            background: #f9f9f9;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('rmgc_booking_form', 'rmgc_booking_form_shortcode');