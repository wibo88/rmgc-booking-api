        #rmgc-booking-message {
            margin: 20px 0 40px;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
            min-height: 60px;
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
            width: 32px;
            height: 32px;
            color: transparent;
            cursor: pointer;
            background: rgba(255,255,255,0.2);
            border-radius: 4px;
            text-align: center;
            transition: background 0.3s, transform 0.2s;
        }
        .custom-header nav span:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-50%) scale(1.05);
        }
        .custom-header nav span:first-child {
            left: 15px;
        }
        .custom-header nav span:last-child {
            right: 15px;
        }
        .custom-header nav span:before {
            color: #fff;
            position: absolute;
            text-align: center;
            width: 32px;
            font-size: 20px;
            line-height: 32px;
            font-weight: bold;
            left: 0;
            top: 0;
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
            text-align: center;
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
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('rmgc_booking_form', 'rmgc_booking_form_shortcode');