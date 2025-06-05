function showPrintReportModal() {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-print me-2"></i>Print Report
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="printReportForm">
                    <div class="modal-body">
                        <!-- Date Shortcuts -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quick Select:</label>
                            <div class="btn-group-sm d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('today')">Today</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('thisWeek')">This Week</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('thisMonth')">This Month</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('lastMonth')">Last Month</button>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Manual Date Selection -->
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">From Date</label>
                                <input type="date" class="form-control" id="print_date_from" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">To Date</label>
                                <input type="date" class="form-control" id="print_date_to" required>
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

    document.getElementById('printReportForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fromDate = document.getElementById('print_date_from').value;
        const toDate = document.getElementById('print_date_to').value;

        if (fromDate && toDate) {
            generateReport(fromDate, toDate);
            bsModal.hide();
        }
    });
}

function setDateRange(range) {
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
    document.getElementById('print_date_from').value = formatDate(fromDate);
    document.getElementById('print_date_to').value = formatDate(toDate);
}

function formatDate(date) {
    const d = new Date(date);
    return d.toISOString().split('T')[0];
}


// Handle form submission to stay on bookings page
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const params = new URLSearchParams();

            // Add form data to URL parameters
            for (let [key, value] of formData.entries()) {
                if (value) {
                    params.append(key, value);
                }
            }

            // Stay on the current page but add filter parameters
            const currentUrl = window.location.pathname;
            const newUrl = currentUrl + (params.toString() ? '?' + params.toString() : '');

            window.location.href = newUrl;
        });
    }

    // Validate date range for main form
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');

    if (dateFromInput) {
        dateFromInput.addEventListener('change', function () {
            const fromDate = this.value;
            if (dateToInput && fromDate && dateToInput.value && fromDate > dateToInput.value) {
                dateToInput.value = fromDate;
            }
        });
    }

    if (dateToInput) {
        dateToInput.addEventListener('change', function () {
            const toDate = this.value;
            if (dateFromInput && toDate && dateFromInput.value && toDate < dateFromInput.value) {
                dateFromInput.value = toDate;
            }
        });
    }
});

function generateReport(fromDate, toDate) {
    // Open new window for PDF
    const reportWindow = window.open('', '_blank');

    if (!reportWindow) {
        alert('Please allow popups for this site to generate reports.');
        return;
    }

    reportWindow.document.write(`
        <html>
        <head>
            <title>Bookings Report - ${fromDate} to ${toDate}</title>
            <style>
                @page { size: landscape; margin: 0.5in; }
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; }
                .section-title { font-size: 14px; font-weight: bold; margin: 20px 0 10px 0; }
                .status-pending { color: #856404; }
                .status-approved { color: #155724; }
                .loading { text-align: center; margin: 50px 0; }
                .error { color: red; text-align: center; margin: 50px 0; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>Bookings Report</h2>
                <p>Period: ${new Date(fromDate).toLocaleDateString()} - ${new Date(toDate).toLocaleDateString()}</p>
                <p>Generated on: ${new Date().toLocaleDateString()}</p>
            </div>
            <div id="reportContent" class="loading">Loading report data...</div>
            <script>
                // Get the base URL more reliably
                const baseUrl = window.location.protocol + '//' + window.location.host;
                const reportUrl = baseUrl + '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/generate-report.php?date_from=${fromDate}&date_to=${toDate}';
                
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
                        console.error('Error loading report:', error);
                        document.getElementById('reportContent').innerHTML = 
                            '<div class="error"><p>Error loading report data.</p><p>Please check the console for details.</p></div>';
                    });
            <\/script>
        </body>
        </html>
    `);
}