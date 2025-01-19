jQuery(document).ready(function($) {
    // Initialize date picker with custom navigation
    var calendar = $('#embedded-calendar').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: 0,
        numberOfMonths: 1,
        beforeShowDay: function(date) {
            var day = date.getDay();
            // Enable only Monday (1), Tuesday (2), and Friday (5)
            return [(day === 1 || day === 2 || day === 5)];
        },
        onSelect: function(dateText) {
            $('#bookingDate').val(dateText);
        },
        onChangeMonthYear: function(year, month) {
            updateHeaderDate(year, month);
        }
    }).data('datepicker');

    // Initial header update
    var currentDate = new Date();
    updateHeaderDate(currentDate.getFullYear(), currentDate.getMonth() + 1);

    // Custom navigation handlers
    $('#custom-prev').click(function() {
        var current = $('#embedded-calendar').datepicker('getDate');
        current.setMonth(current.getMonth() - 1);
        $('#embedded-calendar').datepicker('setDate', current);
        updateHeaderDate(current.getFullYear(), current.getMonth() + 1);
    });

    $('#custom-next').click(function() {
        var current = $('#embedded-calendar').datepicker('getDate');
        current.setMonth(current.getMonth() + 1);
        $('#embedded-calendar').datepicker('setDate', current);
        updateHeaderDate(current.getFullYear(), current.getMonth() + 1);
    });

    function updateHeaderDate(year, month) {
        var monthNames = [
            "January", "February", "March", "April",
            "May", "June", "July", "August",
            "September", "October", "November", "December"
        ];
        $('#custom-month-year').text(monthNames[month - 1] + ' ' + year);
    }

    // Rest of the existing form submission code...
});