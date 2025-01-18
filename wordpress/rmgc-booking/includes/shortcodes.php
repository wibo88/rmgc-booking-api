<?php
function rmgc_booking_form_shortcode() {
    ob_start();
    ?>
    <div id="rmgc-booking-form" class="rmgc-booking-container">
        <!-- Previous form HTML remains the same -->
    </div>
    <style>
        /* Previous styles remain */
        
        #embedded-calendar {
            width: 100%;
            margin-bottom: 15px;
            position: relative;
            z-index: 100;
        }
        
        .ui-datepicker {
            width: 100%;
            max-width: 300px;
            background: #fff;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .ui-datepicker table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .ui-datepicker td {
            padding: 2px;
            text-align: center;
        }
        
        .ui-datepicker td span, 
        .ui-datepicker td a {
            display: block;
            padding: 5px;
            text-align: center;
            text-decoration: none;
        }
        
        .form-section {
            position: relative;
            z-index: 1;
        }
        
        .form-group.half {
            position: relative;
            z-index: 50;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('rmgc_booking_form', 'rmgc_booking_form_shortcode');