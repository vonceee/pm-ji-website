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

function generateReport(fromDate, toDate) {
    // Open new window for PDF
    const reportWindow = window.open('', '_blank');
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
            </style>
        </head>
        <body>
            <div class="header">
                <h2>Bookings Report</h2>
                <p>Period: ${new Date(fromDate).toLocaleDateString()} - ${new Date(toDate).toLocaleDateString()}</p>
                <p>Generated on: ${new Date().toLocaleDateString()}</p>
            </div>
            <div id="reportContent">Loading...</div>
            <script>
                // Fetch data and populate report
                fetch(window.location.origin + '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/generate-report.php?date_from=${fromDate}&date_to=${toDate}')
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('reportContent').innerHTML = html;
                        setTimeout(() => window.print(), 500);
                    })
                    .catch(error => {
                        document.getElementById('reportContent').innerHTML = '<p>Error loading report data.</p>';
                    });
            </script>
        </body>
        </html>
    `);
}