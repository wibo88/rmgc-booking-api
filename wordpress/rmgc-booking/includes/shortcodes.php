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
                
                <!-- Calendar in its own row -->
                <div class="form-row calendar-row">
                    <div class="form-group full">
                        <label for="bookingDate">Preferred Date</label>
                        <div id="embedded-calendar"></div>
                        <input type="hidden" id="bookingDate" name="date" required>
                    </div>
                </div>

                <!-- Players and time preferences in next row -->
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
            position: relative;
            z-index: 1;
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
            position: relative;
            z-index: 1;
        }
        .form-group.full {
            width: 100%;
            position: relative;
            z-index: 1;
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

        /* Calendar specific styles */
        .calendar-row {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
        }
        #embedded-calendar {
            width: 100%;
            max-width: 400px;
            margin: 0 auto 15px;
        }
        .ui-datepicker {
            width: 100% !important;
            max-width: 400px;
            background: #fff;
            padding: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-size: 14px;
            border: none;
            border-radius: 12px;
        }
        .ui-datepicker .ui-datepicker-header {
            background: #005b94;
            color: white;
            padding: 12px;
            margin: -16px -16px 12px -16px;
            border-radius: 12px 12px 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .ui-datepicker .ui-datepicker-title {
            text-align: center;
            line-height: 1.8em;
            font-weight: 600;
            font-size: 16px;
        }
        .ui-datepicker table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px;
            margin-top: 8px;
        }
        .ui-datepicker th {
            padding: 8px 0;
            text-align: center;
            border: 0;
            color: #666;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .ui-datepicker td {
            padding: 2px;
            text-align: center;
        }
        .ui-datepicker td span,
        .ui-datepicker td a {
            display: block;
            padding: 8px;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            border: 2px solid transparent;
            color: #333;
            font-weight: 500;
        }
        .ui-datepicker td a:hover {
            background: #e6f3fa;
            border-color: #005b94;
        }
        .ui-datepicker td a.ui-state-active {
            background: #005b94;
            color: white;
            border-color: #005b94;
        }
        .ui-datepicker td a.ui-state-highlight {
            background: #e6f3fa;
            border-color: #005b94;
            color: #005b94;
        }
        .ui-datepicker .ui-datepicker-prev,
        .ui-datepicker .ui-datepicker-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            cursor: pointer;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }
        .ui-datepicker .ui-datepicker-prev:hover,
        .ui-datepicker .ui-datepicker-next:hover {
            background: rgba(255,255,255,0.3);
        }
        .ui-datepicker .ui-datepicker-prev {
            left: 10px;
        }
        .ui-datepicker .ui-datepicker-next {
            right: 10px;
        }
        .ui-datepicker td.ui-state-disabled span {
            color: #ccc;
            background: #f5f5f5;
            border-radius: 8px;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('rmgc_booking_form', 'rmgc_booking_form_shortcode');