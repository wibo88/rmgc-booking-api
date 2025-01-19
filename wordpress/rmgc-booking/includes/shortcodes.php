<?php
function rmgc_booking_form_shortcode() {
    ob_start();
    ?>
    <!-- Previous sections remain the same up to the calendar section -->
            <!-- Booking Details Section -->
            <div class="form-section">
                <h3>Your Booking</h3>
                <small class="section-desc">Visitor Booking times (subject to availability) Mondays, Tuesdays and Fridays 10.30am - 11.30am</small>
                
                <div class="form-row calendar-row">
                    <div class="form-group full">
                        <div class="custom-calendar-wrap">
                            <div id="custom-inner" class="custom-inner">
                                <div class="custom-header clearfix">
                                    <nav>
                                        <span id="custom-prev" class="custom-prev"></span>
                                        <span id="custom-next" class="custom-next"></span>
                                    </nav>
                                    <h3 id="custom-month-year" class="custom-month-year"></h3>
                                </div>
                                <div id="embedded-calendar" class="fc-calendar-container"></div>
                                <input type="hidden" id="bookingDate" name="date" required>
                            </div>
                        </div>
                    </div>
                </div>
    <!-- Rest of the form HTML remains the same -->
    <style>
        /* Previous styles remain the same up to calendar styles */

        /* Calendar Styles */
        .custom-calendar-wrap {
            margin: 10px auto;
            position: relative;
            overflow: hidden;
            max-width: 400px;
        }
        .custom-inner {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .custom-header {
            background: #d3bc8d;
            padding: 0 15px;
            height: 60px;
            position: relative;
            border-top: 5px solid #d3bc8d;
            border-bottom: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .custom-header .custom-month-year {
            text-align: center;
            text-transform: uppercase;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            padding: 0;
            line-height: 1;
        }
        .custom-header nav span {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
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
            transition: background 0.3s;
        }
        .custom-header nav span:first-child {
            left: 15px;
        }
        .custom-header nav span:last-child {
            right: 15px;
        }
        .custom-header nav span:hover {
            background: rgba(255,255,255,0.3);
        }
        .custom-header nav span:before {
            color: #fff;
            position: absolute;
            text-align: center;
            width: 100%;
            font-size: 24px;
            line-height: 30px;
        }
        .custom-header nav span.custom-prev:before {
            content: '‹';
        }
        .custom-header nav span.custom-next:before {
            content: '›';
        }

        /* Hide jQuery UI Datepicker header */
        .ui-datepicker-header {
            display: none !important;
        }
        
        .ui-datepicker {
            width: 100% !important;
            padding: 20px !important;
            border: none !important;
            background: #f8f8f8 !important;
        }
        
        .ui-datepicker th {
            background: transparent;
            color: #d3bc8d;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            padding: 10px 0;
        }
        
        .ui-datepicker td {
            background: #fff;
            border: 1px solid #eee;
            padding: 0 !important;
        }
        
        .ui-datepicker td span,
        .ui-datepicker td a {
            text-align: center !important;
            padding: 12px 8px !important;
            background: #fff !important;
            border: none !important;
            color: #333 !important;
            font-weight: 400 !important;
            transition: all 0.2s !important;
        }
        
        .ui-datepicker td a:hover {
            background: #f8f8f8 !important;
            color: #d3bc8d !important;
        }
        
        .ui-datepicker td.ui-datepicker-current-day a {
            background: #d3bc8d !important;
            color: #fff !important;
            font-weight: 600 !important;
        }
        
        .ui-datepicker td.ui-datepicker-today a {
            border: 2px solid #d3bc8d !important;
            padding: 10px 6px !important;
        }
        
        .ui-datepicker td.ui-state-disabled {
            opacity: 0.3;
            background: #f9f9f9;
        }

        /* Rest of the styles remain the same */
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('rmgc_booking_form', 'rmgc_booking_form_shortcode');