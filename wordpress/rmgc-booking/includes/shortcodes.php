<?php
function rmgc_booking_form_shortcode() {
    ob_start();
    ?>
    <!-- Previous HTML content remains the same -->
    <style>
        /* Previous styles remain the same */
        
        /* Enhanced calendar styling */
        #embedded-calendar {
            width: calc(100% - 20px);  /* Account for padding */
            max-width: 320px;          /* Control maximum width */
            margin-bottom: 15px;
            position: relative;
            z-index: 1000;            /* Ensure calendar appears above other elements */
        }
        
        .ui-datepicker {
            width: 100% !important;    /* Force width */
            max-width: 320px;
            background: #fff;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            font-size: 14px;           /* Control text size */
            border: 1px solid #ddd;
        }
        
        .ui-datepicker .ui-datepicker-header {
            background: #005b94;       /* RMGC brand color */
            color: white;
            padding: 8px;
            margin: -10px -10px 10px -10px;
        }
        
        .ui-datepicker .ui-datepicker-title {
            text-align: center;
            line-height: 1.8em;
            margin: 0 2.3em;
        }
        
        .ui-datepicker table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        
        .ui-datepicker th {
            padding: 5px;
            text-align: center;
            border: 0;
            color: #666;
            font-size: 12px;
        }
        
        .ui-datepicker td {
            padding: 1px;
            text-align: center;
        }
        
        .ui-datepicker td span,
        .ui-datepicker td a {
            display: block;
            padding: 4px;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
        }
        
        .ui-datepicker td a.ui-state-active {
            background: #005b94;
            color: white;
        }
        
        .ui-datepicker td a.ui-state-highlight {
            background: #e6f3fa;
            border: 1px solid #005b94;
        }
        
        .ui-datepicker .ui-datepicker-prev,
        .ui-datepicker .ui-datepicker-next {
            position: absolute;
            top: 8px;
            width: 1.8em;
            height: 1.8em;
            cursor: pointer;
        }
        
        .ui-datepicker .ui-datepicker-prev {
            left: 8px;
        }
        
        .ui-datepicker .ui-datepicker-next {
            right: 8px;
        }
        
        /* Fix z-index for form sections */
        .form-section {
            position: relative;
            z-index: 1;
        }
        
        .form-group.half {
            position: relative;
            z-index: 1;             /* Lower than calendar */
        }
        
        .form-group.half:has(#embedded-calendar) {
            z-index: 1000;          /* Higher for calendar container */
        }

        /* Rest of your existing styles remain the same */
        
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('rmgc_booking_form', 'rmgc_booking_form_shortcode');