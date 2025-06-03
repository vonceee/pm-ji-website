// Simple, direct approach - replace your entire date-range-picker.js with this:

$(function () {
    console.log('Starting date picker initialization...');
    
    // Check if all required libraries are loaded
    if (typeof moment === 'undefined') {
        console.error('Moment.js is not loaded!');
        return;
    }
    
    if (typeof $.fn.daterangepicker === 'undefined') {
        console.error('DateRangePicker is not loaded!');
        return;
    }
    
    const $input = $('#dateRange');
    if ($input.length === 0) {
        console.error('Date range input element not found!');
        return;
    }
    
    // Get initial dates
    const startDate = $input.data('start') || moment().startOf('month').format('YYYY-MM-DD');
    const endDate = $input.data('end') || moment().endOf('month').format('YYYY-MM-DD');
    
    console.log('Initial dates:', startDate, endDate);
    
    // Initialize picker with minimal config
    $input.daterangepicker({
        startDate: moment(startDate),
        endDate: moment(endDate),
        locale: {
            format: 'MMM DD, YYYY'
        },
        ranges: {
            'Today': [moment(), moment()],
            'This Week': [moment().startOf('week'), moment().endOf('week')],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });
    
    // Set initial display
    const initialDisplay = moment(startDate).format('MMM DD, YYYY') + ' - ' + moment(endDate).format('MMM DD, YYYY');
    $input.val(initialDisplay);
    
    console.log('Date picker initialized, initial display:', initialDisplay);
    
    // Handle date selection
    $input.on('apply.daterangepicker', function(ev, picker) {
        console.log('Date range applied event triggered');
        
        const newStart = picker.startDate.format('YYYY-MM-DD');
        const newEnd = picker.endDate.format('YYYY-MM-DD');
        
        console.log('New date range:', newStart, 'to', newEnd);
        
        // Direct URL redirect - simplest approach
        const newUrl = window.location.pathname + '?start=' + newStart + '&end=' + newEnd;
        
        console.log('Redirecting to:', newUrl);
        
        // Force page reload with new parameters
        window.location.href = newUrl;
    });
    
    console.log('Date picker setup complete');
});

// Remove auto-refresh for testing
// setTimeout(function () {
//     location.reload();
// }, 300000);