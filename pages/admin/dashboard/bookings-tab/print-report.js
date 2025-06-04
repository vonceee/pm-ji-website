// Add this to your existing booking-management.js or create a new file

function showPrintReportModal() {
    // Create modal HTML
    const modalHTML = `
        <div class="modal fade" id="printReportModal" tabindex="-1" aria-labelledby="printReportModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="printReportModalLabel">
                            <i class="fas fa-print me-2"></i>Generate Booking Report
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="print_date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="print_date_from" name="print_date_from">
                            </div>
                            <div class="col-md-6">
                                <label for="print_date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="print_date_to" name="print_date_to">
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <label class="form-label">Quick Date Ranges</label>
                                <div class="btn-group-vertical d-grid gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setPrintDateRange('today')">
                                        Today
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setPrintDateRange('this_week')">
                                        This Week
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setPrintDateRange('this_month')">
                                        This Month
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setPrintDateRange('last_30_days')">
                                        Last 30 Days
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setPrintDateRange('all')">
                                        All Records
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <label class="form-label">Include Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_pending" checked>
                                    <label class="form-check-label" for="include_pending">Pending</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_approved" checked>
                                    <label class="form-check-label" for="include_approved">Approved</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_history" checked>
                                    <label class="form-check-label" for="include_history">Completed/Cancelled</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="generatePrintReport()">Generate Report</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if present
    const existingModal = document.getElementById('printReportModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('printReportModal'));
    modal.show();
}

function setPrintDateRange(range) {
    const today = new Date();
    let fromDate, toDate;

    switch (range) {
        case 'today':
            fromDate = toDate = today.toISOString().split('T')[0];
            break;
        case 'this_week':
            const startOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
            const endOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + 6));
            fromDate = startOfWeek.toISOString().split('T')[0];
            toDate = endOfWeek.toISOString().split('T')[0];
            break;
        case 'this_month':
            fromDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
            break;
        case 'last_30_days':
            toDate = new Date().toISOString().split('T')[0];
            fromDate = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            break;
        case 'all':
            fromDate = '';
            toDate = '';
            break;
    }

    document.getElementById('print_date_from').value = fromDate;
    document.getElementById('print_date_to').value = toDate;
}

function generatePrintReport() {
    const fromDate = document.getElementById('print_date_from').value;
    const toDate = document.getElementById('print_date_to').value;
    const includePending = document.getElementById('include_pending').checked;
    const includeApproved = document.getElementById('include_approved').checked;
    const includeHistory = document.getElementById('include_history').checked;

    // Build URL parameters
    const params = new URLSearchParams();
    params.append('action', 'print_report');
    if (fromDate) params.append('date_from', fromDate);
    if (toDate) params.append('date_to', toDate);
    if (includePending) params.append('include_pending', '1');
    if (includeApproved) params.append('include_approved', '1');
    if (includeHistory) params.append('include_history', '1');

    // Open print report in new window
    const printUrl = window.location.pathname + '?' + params.toString();
    window.open(printUrl, '_blank', 'width=1200,height=800');

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('printReportModal'));
    modal.hide();
}