<?php
function rmgc_booking_form_shortcode() {
    ob_start();
    ?>
    <!-- Previous HTML remains the same until booking details section -->

            <!-- Booking Details Section -->
            <div class="form-section">
                <h3>Your Booking</h3>
                <small class="section-desc">Visitor Booking times (subject to availability) Mondays, Tuesdays and Fridays 10.30am - 11.30am</small>
                
                <!-- Calendar gets its own full-width row -->
                <div class="form-row calendar-row">
                    <div class="form-group full">
                        <label for="bookingDate">Preferred Date</label>
                        <div id="embedded-calendar"></div>
                        <input type="hidden" id="bookingDate" name="date" required>
                    </div>
                </div>

                <!-- Players and time preferences in their own row -->
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

            <!-- Rest of the form HTML remains the same -->

    <style>
        /* Previous styles remain the same until form-group styles */
        
        .form-group.full {
            width: 100%;
            position: relative;
            z-index: 1000;
        }
        
        .calendar-row {
            margin-bottom: 30px;
        }

        /* Modern calendar styling */
        #embedded-calendar {
            width: 100%;
            max-width: 400px;
            margin: 0 auto 15px;
            position: relative;
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
        
        .ui-datepicker .ui-datepicker-prev span,
        .ui-datepicker .ui-datepicker-next span {
            color: white;
        }
        
        /* Disabled dates styling */
        .ui-datepicker td.ui-state-disabled span {
            color: #ccc;
            background: #f5f5f5;
            border-radius: 8px;
        }
        
        /* Rest of the existing styles remain the same */
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('rmgc_booking_form', 'rmgc_booking_form_shortcode');