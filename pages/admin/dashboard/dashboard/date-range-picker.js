$(function () {
    // Get dates from data attributes
    const dateRangeInput = $('#dateRange');
    const startDate = dateRangeInput.data('start');
    const endDate = dateRangeInput.data('end');

    // set default range: use provided dates or fallback to this month
    let start = startDate ? moment(startDate) : moment().startOf('month');
    let end = endDate ? moment(endDate) : moment().endOf('month');

    // Validate the dates
    if (!start.isValid()) {
        start = moment().startOf('month');
    }
    if (!end.isValid()) {
        end = moment().endOf('month');
    }

    function cb(start, end) {
        // Format: May 05, 2025 - May 12, 2025
        $('#dateRange').val(start.format('MMM DD, YYYY') + ' - ' + end.format('MMM DD, YYYY'));
    }

    $('#dateRange').daterangepicker({
        startDate: start,
        endDate: end,
        locale: { format: 'MMM DD, YYYY' },
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'This Year': [moment().startOf('year'), moment().endOf('year')]
        }
    }, cb);

    cb(start, end);

    // handle form submission
    $('#dateRange').on('apply.daterangepicker', function (ev, picker) {
        const startDate = picker.startDate.format('YYYY-MM-DD');
        const endDate = picker.endDate.format('YYYY-MM-DD');
        window.location.search = `?start=${startDate}&end=${endDate}`;
    });
});

// auto-refresh dashboard every (300000) 5 minutes
setTimeout(function () {
    location.reload();
}, 300000);