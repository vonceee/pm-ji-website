// pages/admin/reports/reports.js

$(document).ready(function() {
    // Initialize date range picker
    initializeDateRangePicker();
    
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Auto-refresh functionality
    setupAutoRefresh();
});

/**
 * Initialize date range picker
 */
function initializeDateRangePicker() {
    const dateRangeInput = $('#dateRange');
    const startDate = dateRangeInput.data('start');
    const endDate = dateRangeInput.data('end');

    let start = startDate ? moment(startDate) : moment().startOf('month');
    let end = endDate ? moment(endDate) : moment().endOf('month');

    // Validate dates
    if (!start.isValid()) {
        start = moment().startOf('month');
    }
    if (!end.isValid()) {
        end = moment().endOf('month');
    }

    function cb(start, end) {
        $('#dateRange').val(start.format('MMM DD, YYYY') + ' - ' + end.format('MMM DD, YYYY'));
        $('input[name="start"]').val(start.format('YYYY-MM-DD'));
        $('input[name="end"]').val(end.format('YYYY-MM-DD'));
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
            'This Quarter': [moment().startOf('quarter'), moment().endOf('quarter')],
            'This Year': [moment().startOf('year'), moment().endOf('year')]
        }
    }, cb);

    cb(start, end);
}

/**
 * Update report type and show/hide relevant filters
 */
function updateReportType() {
    const reportType = $('select[name="type"]').val();
    
    // Show/hide filters based on report type
    const statusFilter = $('select[name="status"]').closest('.col-md-2');
    const eventTypeFilter = $('select[name="event_type"]').closest('.col-md-2');
    const paymentStatusFilter = $('select[name="payment_status"]').closest('.col-md-2');
    
    // Reset visibility
    statusFilter.show();
    eventTypeFilter.show();
    paymentStatusFilter.show();
    
    switch (reportType) {
        case 'revenue_report':
            statusFilter.hide();
            paymentStatusFilter.hide();
            break;
        case 'payment_report':
            statusFilter.hide();
            eventTypeFilter.hide();
            break;
        case 'event_analysis':
            statusFilter.hide();
            eventTypeFilter.hide();
            paymentStatusFilter.hide();
            break;
    }
}

/**
 * Reset all filters to default values
 */
function resetFilters() {
    $('select[name="type"]').val('booking_summary');
    $('select[name="status"]').val('');
    $('select[name="event_type"]').val('');
    $('select[name="payment_status"]').val('');
    
    // Reset date range to current month
    const start = moment().startOf('month');
    const end = moment().endOf('month');
    
    $('#dateRange').data('daterangepicker').setStartDate(start);
    $('#dateRange').data('daterangepicker').setEndDate(end);
    $('#dateRange').val(start.format('MMM DD, YYYY') + ' - ' + end.format('MMM DD, YYYY'));
    
    $('input[name="start"]').val(start.format('YYYY-MM-DD'));
    $('input[name="end"]').val(end.format('YYYY-MM-DD'));
    
    updateReportType();
}

/**
 * Export report to PDF
 */
function exportToPDF() {
    // Show loading state
    const exportBtn = $('button[onclick="exportToPDF()"]');
    const originalText = exportBtn.html();
    exportBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Generating PDF...');
    exportBtn.prop('disabled', true);
    
    // Get report content
    const reportContent = $('.report-content')[0];
    const reportHeader = $('.report-header')[0];
    
    // Configure PDF options
    const opt = {
        margin: [10, 10, 10, 10],
        filename: `report-${moment().format('YYYY-MM-DD-HHmm')}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { 
            scale: 2,
            useCORS: true,
            allowTaint: true
        },
        jsPDF: { 
            unit: 'mm', 
            format: 'a4', 
            orientation: 'portrait' 
        }
    };
    
    // Create PDF
    html2pdf().set(opt).from(reportHeader).toPdf().get('pdf').then(function(pdf) {
        // Add report content to next page
        pdf.addPage();
        html2pdf().set(opt).from(reportContent).toPdf().get('pdf').then(function(contentPdf) {
            // Merge and save
            contentPdf.save();
            
            // Restore button state
            exportBtn.html(originalText);
            exportBtn.prop('disabled', false);
        });
    }).catch(function(error) {
        console.error('PDF generation failed:', error);
        alert('Failed to generate PDF. Please try again.');
        
        // Restore button state
        exportBtn.html(originalText);
        exportBtn.prop('disabled', false);
    });
}

/**
 * Setup auto-refresh functionality
 */
function setupAutoRefresh() {
    // Auto-refresh every 10 minutes for live data
    setInterval(function() {
        const currentUrl = window.location.href;
        if (currentUrl.indexOf('auto_refresh=1') === -1) {
            const separator = currentUrl.indexOf('?') !== -1 ? '&' : '?';
            window.location.href = currentUrl + separator + 'auto_refresh=1';
        }
    }, 600000); // 10 minutes
}

/**
 * Format currency values
 */
function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Format percentage values
 */
function formatPercentage(value) {
    return parseFloat(value).toFixed(1) + '%';
}

/**
 * Generate chart (if Chart.js is available)
 */
function generateChart(canvasId, chartData, chartType = 'bar') {
    if (typeof Chart !== 'undefined') {
        const ctx = document.getElementById(canvasId);
        if (ctx) {
            new Chart(ctx, {
                type: chartType,
                data: chartData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Report Chart'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        }
                    }
                }
            });
        }
    }
}

/**
 * Print optimized function
 */
window.addEventListener('beforeprint', function() {
    // Add print-specific styles
    document.body.classList.add('printing');
});

window.addEventListener('afterprint', function() {
    // Remove print-specific styles
    document.body.classList.remove('printing');
});

/**
 * Table sorting functionality
 */
function sortTable(columnIndex, tableId) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Determine sort direction
    const currentSort = table.getAttribute('data-sort-column');
    const currentDirection = table.getAttribute('data-sort-direction') || 'asc';
    const newDirection = (currentSort == columnIndex && currentDirection === 'asc') ? 'desc' : 'asc';
    
    // Sort rows
    rows.sort((a, b) => {
        const aValue = a.children[columnIndex].textContent.trim();
        const bValue = b.children[columnIndex].textContent.trim();
        
        // Try to parse as numbers first
        const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
        const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return newDirection === 'asc' ? aNum - bNum : bNum - aNum;
        }
        
        // String comparison
        return newDirection === 'asc' 
            ? aValue.localeCompare(bValue)
            : bValue.localeCompare(aValue);
    });
    
    // Clear and re-append sorted rows
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
    
    // Update sort indicators
    table.setAttribute('data-sort-column', columnIndex);
    table.setAttribute('data-sort-direction', newDirection);
    
    // Update header indicators
    const headers = table.querySelectorAll('th');
    headers.forEach((header, index) => {
        header.classList.remove('sort-asc', 'sort-desc');
        if (index === columnIndex) {
            header.classList.add(newDirection === 'asc' ? 'sort-asc' : 'sort-desc');
        }
    });
}

/**
 * Filter table rows
 */
function filterTable(searchTerm, tableId) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm.toLowerCase()) ? '' : 'none';
    });
}

/**
 * Export table to CSV
 */
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    const csvData = [];
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('th, td');
        const rowData = Array.from(cols).map(col => {
            return '"' + col.textContent.trim().replace(/"/g, '""') + '"';
        });
        csvData.push(rowData.join(','));
    });
    
    const csvContent = csvData.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = filename || 'report.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}