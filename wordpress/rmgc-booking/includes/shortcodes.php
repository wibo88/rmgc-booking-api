<?php
function rmgc_booking_form_shortcode() {
    ob_start();
    ?>
    <div id="rmgc-booking-form" class="rmgc-booking-container">
        <form id="rmgc-booking" class="rmgc-form">
            <!-- Previous sections remain the same until booking section -->
            
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
        /* Original form styles remain */
        
        /* New Calendar Styles */
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

        .custom-header {
            background: #005b94;
            padding: 5px 10px 10px 20px;
            height: 70px;
            position: relative;
            border-top: 5px solid #005b94;
            border-bottom: 1px solid #ddd;
        }

        .custom-header h2,
        .custom-header h3 {
            text-align: center;
            text-transform: uppercase;
            color: #FFF;
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
            color: #fff;
            cursor: pointer;
            text-align: center;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            transition: background 0.3s;
            line-height: 30px;
        }

        .custom-header nav span:hover {
            background: rgba(255,255,255,0.3);
        }

        .custom-header nav span:first-child {
            left: 5px;
        }

        .custom-header nav span:last-child {
            right: 5px;
        }

        .custom-header nav span:before {
            font-family: monospace;
            font-size: 20px;
            font-weight: bold;
        }

        .custom-header nav span.custom-prev:before {
            content: '<';
        }

        .custom-header nav span.custom-next:before {
            content: '>';
        }

        .fc-calendar-container {
            height: 400px;
            padding: 30px;
            background: #f6f6f6;
            box-shadow: inset 0 1px rgba(255,255,255,0.8);
        }

        .fc-calendar {
            width: 100%;
            height: 100%;
        }

        .fc-calendar .fc-head {
            background: transparent;
            color: #005b94;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }

        .fc-calendar .fc-row {
            width: 100%;
            border-bottom: 1px solid #ddd;
        }

        .fc-calendar .fc-body {
            position: relative;
            width: 100%;
            height: calc(100% - 30px);
            border: 1px solid #ddd;
        }

        .fc-calendar .fc-row > div {
            background: #fff;
            cursor: pointer;
            padding: 4px;
            border-right: 1px solid #ddd;
            text-align: center;
            overflow: hidden;
            position: relative;
        }

        .fc-calendar .fc-row > div:empty {
            background: transparent;
        }

        .fc-calendar .fc-row > div > span.fc-date {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #686a6e;
            font-weight: 400;
            pointer-events: none;
        }

        .fc-calendar .fc-row > div.fc-today {
            background: #005b94;
            box-shadow: inset 0 -1px 1px rgba(0,0,0,0.1);
        }

        .fc-calendar .fc-row > div.fc-today > span.fc-date {
            color: #fff;
            text-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }

        .fc-calendar .fc-row > div.fc-disabled {
            background: #f9f9f9;
            color: #ccc;
            cursor: not-allowed;
        }

        /* Rest of the form styles remain the same */
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('rmgc_booking_form', 'rmgc_booking_form_shortcode');