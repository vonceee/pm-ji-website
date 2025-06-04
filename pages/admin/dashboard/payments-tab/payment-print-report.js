function showPaymentPrintReportModal() {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-print me-2"></i>Print Payment Report
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="printPaymentReportForm">
                    <div class="modal-body">
                        <!-- Date Shortcuts -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quick Select:</label>
                            <div class="btn-group-sm d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setPaymentReportDateRange('today')">Today</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setPaymentReportDateRange('thisWeek')">This Week</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setPaymentReportDateRange('thisMonth')">This Month</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setPaymentReportDateRange('lastMonth')">Last Month</button>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Manual Date Selection -->
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">From Date</label>
                                <input type="date" class="form-control" id="payment_print_date_from" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">To Date</label>
                                <input type="date" class="form-control" id="payment_print_date_to" required>
                            </div>
                        </div>
                        
                        <!-- Report Options -->
                        <div class="mt-3">
                            <label class="form-label fw-bold">Report Options:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_outstanding" checked>
                                <label class="form-check-label" for="include_outstanding">
                                    Include Outstanding Payments
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_completed" checked>
                                <label class="form-check-label" for="include_completed">
                                    Include Completed Payments
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_refunds" checked>
                                <label class="form-check-label" for="include_refunds">
                                    Include Payment Refunds
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();

    // Clean up modal when hidden
    modal.addEventListener('hidden.bs.modal', function () {
        document.body.removeChild(modal);
    });

    document.getElementById('printPaymentReportForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fromDate = document.getElementById('payment_print_date_from').value;
        const toDate = document.getElementById('payment_print_date_to').value;

        if (fromDate && toDate) {
            generatePaymentReport(fromDate, toDate);
            bsModal.hide();
        }
    });
}

function setPaymentReportDateRange(range) {
    const today = new Date();
    let fromDate, toDate;

    switch (range) {
        case 'today':
            fromDate = toDate = new Date(today);
            break;
        case 'thisWeek':
            fromDate = new Date(today);
            fromDate.setDate(today.getDate() - today.getDay());
            toDate = new Date(today);
            toDate.setDate(fromDate.getDate() + 6);
            break;
        case 'thisMonth':
            fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
            toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case 'lastMonth':
            fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            toDate = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
        case 'last30Days':
            fromDate = new Date(today);
            fromDate.setDate(today.getDate() - 30);
            toDate = new Date(today);
            break;
    }

    // Update the correct input fields in the modal
    document.getElementById('payment_print_date_from').value = formatDate(fromDate);
    document.getElementById('payment_print_date_to').value = formatDate(toDate);
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

function generatePaymentReport(fromDate, toDate) {
    // Open new window for PDF
    const reportWindow = window.open('', '_blank');

    if (!reportWindow) {
        alert('Please allow popups for this site to generate reports.');
        return;
    }

    reportWindow.document.write(`
        <html>
        <head>
            <title>Payment Report - ${fromDate} to ${toDate}</title>
            <style>
                @page { size: landscape; margin: 0.5in; }
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; }
                .section-title { font-size: 14px; font-weight: bold; margin: 20px 0 10px 0; }
                .status-partial { color: #856404; }
                .status-paid { color: #155724; }
                .status-pending { color: #721c24; }
                .loading { text-align: center; margin: 50px 0; }
                .error { color: red; text-align: center; margin: 50px 0; }
                .currency { text-align: right; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>Payment Report</h2>
                <p>Period: ${new Date(fromDate).toLocaleDateString()} - ${new Date(toDate).toLocaleDateString()}</p>
                <p>Generated on: ${new Date().toLocaleDateString()}</p>
            </div>
            <div id="reportContent" class="loading">Loading payment report data...</div>
            <script>
                // Get the base URL more reliably
                const baseUrl = window.location.protocol + '//' + window.location.host;
                const reportUrl = baseUrl + '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/generate-payment-report.php?date_from=${fromDate}&date_to=${toDate}';
                
                // Fetch data and populate report
                fetch(reportUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.text();
                    })
                    .then(html => {
                        document.getElementById('reportContent').innerHTML = html;
                        // Wait a bit longer for content to render
                        setTimeout(() => {
                            window.print();
                        }, 1000);
                    })
                    .catch(error => {
                        console.error('Error loading payment report:', error);
                        document.getElementById('reportContent').innerHTML = 
                            '<div class="error"><p>Error loading payment report data.</p><p>Please check the console for details.</p></div>';
                    });
            <\/script>
        </body>
        </html>
    `);
}

// Enhanced print functionality for the current page
function printCurrentPaymentPage() {
    const printWindow = window.open('', '_blank');

    if (!printWindow) {
        alert('Please allow popups for this site to print.');
        return;
    }

    // Get current active tab content
    const activeTab = document.querySelector('.tab-content.active');
    const activeTabContent = activeTab ? activeTab.innerHTML : '';

    // Get current filters info
    const activeFilters = document.querySelector('.active-filters');
    const filtersContent = activeFilters ? activeFilters.outerHTML : '';

    printWindow.document.write(`
        <html>
        <head>
            <title>Payment Management - Current View</title>
            <style>
                @page { size: landscape; margin: 0.5in; }
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; }
                .section-header h5 { font-size: 16px; font-weight: bold; margin-bottom: 15px; }
                .status-partial { color: #856404; font-weight: bold; }
                .status-paid { color: #155724; font-weight: bold; }
                .status-pending { color: #721c24; font-weight: bold; }
                .currency { text-align: right; }
                .active-filters { margin-bottom: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; }
                .filter-tags .badge { display: inline-block; padding: 5px 10px; margin-right: 5px; background-color: #17a2b8; color: white; border-radius: 3px; }
                .empty-state { display: none; }
                .btn, .modal, .alert { display: none; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>Payment Management Report</h2>
                <p>Generated on: ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</p>
            </div>
            ${filtersContent}
            ${activeTabContent}
            <script>
                setTimeout(() => {
                    window.print();
                    window.close();
                }, 500);
            <\/script>
        </body>
        </html>
    `);
}