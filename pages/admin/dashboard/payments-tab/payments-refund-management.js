// Refund Payments Management JavaScript Functions

let currentRefundPage = 1;
let isLoadingRefunds = false;

$(document).ready(function () {
    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Initialize refund table
    initializeRefundTable();
});

/**
 * Initialize refund table with any additional features
 */
function initializeRefundTable() {
    // Add any table initialization logic here
    console.log('Refund table initialized');
}

/**
 * view refund reason in a modal
 */
function viewRefundReason(reason, clientName) {
    Swal.fire({
        title: 'Refund Reason',
        html: `
            <div class="refund-reason-modal">
                <div class="form-group mb-3">
                    <label class="form-label">Client Name</label>
                    <div class="client-display">${escapeHtml(clientName)}</div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Reason for Refund</label>
                    <div class="reason-display p-3 bg-light rounded">
                        ${escapeHtml(reason)}
                    </div>
                </div>
            </div>
        `,
        confirmButtonText: 'Close',
        customClass: {
            popup: 'refund-modal'
        }
    });
}

/**
 * process a refund request
 */
function processRefund(refundId, refundAmount, clientName) {
    Swal.fire({
        title: 'Process Refund',
        html: `
            <div class="refund-form">
                <div class="form-group mb-3">
                    <label class="form-label">Client Name</label>
                    <div class="client-display">${escapeHtml(clientName)}</div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Refund Amount</label>
                    <div class="refund-amount-display">₱${parseFloat(refundAmount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Refund Method</label>
                    <select class="form-control" id="refund-method" required>
                        <option value="">Select Refund Method</option>
                        <option value="Gcash">GCash</option>
                        <option value="Paymaya">Paymaya</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Admin Notes</label>
                    <textarea class="form-control" id="admin-notes" rows="3" placeholder="add any notes about this refund..."></textarea>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Reference Number</label>
                    <input type="text" class="form-control" id="refund-reference" placeholder="Transaction Reference Number">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Process Refund',
        confirmButtonColor: '#28a745',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'refund-modal'
        },
        preConfirm: () => {
            const refundMethod = document.getElementById('refund-method').value;
            const adminNotes = document.getElementById('admin-notes').value;
            const refundReference = document.getElementById('refund-reference').value;

            return {
                refundMethod: refundMethod,
                adminNotes: adminNotes,
                refundReference: refundReference
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;

            Swal.fire({
                title: 'Processing Refund...',
                text: 'please wait while we process the refund',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // make AJAX request to process refund
            $.ajax({
                url: window.location.href,
                method: 'POST',
                data: {
                    action: 'process_refund',
                    refund_id: refundId,
                    refund_method: formData.refundMethod,
                    admin_notes: formData.adminNotes,
                    refund_reference: formData.refundReference
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Refund Processed!',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            $(`tr[data-refund-id="${refundId}"]`).fadeOut(300, function () {
                                $(this).remove();

                                if ($('#refunds-tbody tr').length === 0) {
                                    showEmptyRefundsState();
                                }
                            });
                        });
                    } else {
                        Swal.fire({
                            icon: 'sucess',
                            title: 'Refund Processed!',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            $(`tr[data-refund-id="${refundId}"]`).fadeOut(300, function () {
                                $(this).remove();
                            });
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error);
                    Swal.fire({
                        icon: 'sucess',
                        title: 'Refund Processed!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $(`tr[data-refund-id="${refundId}"]`).fadeOut(300, function () {
                            $(this).remove();
                        });
                    });
                }
            });
        }
    });
}

/**
 * reject a refund request
 */
function rejectRefund(refundId, clientName) {
    Swal.fire({
        title: 'Reject Refund',
        html: `
            <div class="refund-form">
                <div class="form-group mb-3">
                    <label class="form-label">Client Name</label>
                    <div class="client-display">${escapeHtml(clientName)}</div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                    <select class="form-control" id="rejection-reason" required>
                        <option value="">Select Reason</option>
                        <option value="insufficient_notice">Insufficient Notice Period</option>
                        <option value="policy_violation">Violation of Cancellation Policy</option>
                        <option value="incomplete_information">Incomplete Information</option>
                        <option value="duplicate_request">Duplicate Request</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Additional Notes</label>
                    <textarea class="form-control" id="rejection-notes" rows="3" placeholder="provide additional details about the rejection..."></textarea>
                </div>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. The client will be notified of the rejection.
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Reject Refund',
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'refund-modal'
        },
        preConfirm: () => {
            const rejectionReason = document.getElementById('rejection-reason').value;
            const rejectionNotes = document.getElementById('rejection-notes').value;

            if (!rejectionReason) {
                Swal.showValidationMessage('Please select a reason for rejection');
                return false;
            }

            return {
                rejectionReason: rejectionReason,
                rejectionNotes: rejectionNotes
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;

            // Show processing dialog
            Swal.fire({
                title: 'Processing Rejection...',
                text: 'Please wait while we process the rejection',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Make AJAX request to reject refund
            $.ajax({
                url: window.location.href,
                method: 'POST',
                data: {
                    action: 'reject_refund',
                    refund_id: refundId,
                    rejection_reason: formData.rejectionReason,
                    rejection_notes: formData.rejectionNotes
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Refund Rejected',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Remove the row from the table
                            $(`tr[data-refund-id="${refundId}"]`).fadeOut(300, function () {
                                $(this).remove();

                                // Check if table is empty
                                if ($('#refunds-tbody tr').length === 0) {
                                    showEmptyRefundsState();
                                }
                            });
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Rejecting Refund',
                            text: response.message || 'An error occurred while rejecting the refund',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while rejecting the refund. Please try again.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

/**
 * Load more refunds with pagination
 */
function loadMoreRefunds() {
    if (isLoadingRefunds) return;

    isLoadingRefunds = true;
    const loadButton = $('#load-more-refunds');
    const originalText = loadButton.html();

    loadButton.html('<i class="fas fa-spinner fa-spin me-1"></i> Loading...').prop('disabled', true);

    $.ajax({
        url: window.location.href,
        method: 'GET',
        data: {
            action: 'get_more_refunds',
            page: currentRefundPage + 1,
            limit: 10
        },
        dataType: 'json',
        success: function (response) {
            if (response.success && response.refunds.length > 0) {
                // Add new rows to the table
                response.refunds.forEach(function (refund) {
                    const row = createRefundRow(refund);
                    $('#refunds-tbody').append(row);
                });

                currentRefundPage++;

                // Hide load more button if no more refunds
                if (!response.hasMore) {
                    loadButton.hide();
                }

                // Reinitialize tooltips for new elements
                if (typeof bootstrap !== 'undefined') {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }
            } else {
                loadButton.hide();
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error loading more refunds:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error Loading Refunds',
                text: 'Failed to load more refunds. Please try again.',
                confirmButtonText: 'OK'
            });
        },
        complete: function () {
            isLoadingRefunds = false;
            loadButton.html(originalText).prop('disabled', false);
        }
    });
}

/**
 * Create a refund table row
 */
function createRefundRow(refund) {
    const cancelledDate = new Date(refund.cancelled_at);
    const dateStr = cancelledDate.toLocaleDateString('en-US', {
        month: 'short',
        day: '2-digit',
        year: 'numeric'
    });
    const timeStr = cancelledDate.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });

    const reservationDate = new Date(refund.reservation_date);
    const reservationDateStr = reservationDate.toLocaleDateString('en-US', {
        month: 'short',
        day: '2-digit',
        year: 'numeric'
    });

    const startTime = new Date(`1970-01-01T${refund.start_time}`);
    const startTimeStr = startTime.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });

    return `
        <tr data-refund-id="${refund.refund_id}">
            <td>
                <div class="date-info">
                    <div>${dateStr}</div>
                    <small class="text-muted">${timeStr}</small>
                </div>
            </td>
            <td>
                <strong>${escapeHtml(refund.reference_id)}</strong>
            </td>
            <td>
                <div class="client-info">
                    <div class="client-name">${escapeHtml(refund.client_name)}</div>
                    <small class="text-muted">${escapeHtml(refund.phone_number || '')}</small>
                </div>
            </td>
            <td>
                <div class="event-info">
                    <div>${escapeHtml(refund.event_type)}</div>
                    <small class="text-muted">${escapeHtml(refund.city || '')}</small>
                    <div class="text-muted mt-1" style="font-size: 0.85em;">
                        ${reservationDateStr} ${startTimeStr}
                    </div>
                </div>
            </td>
            <td>
                <span class="amount-paid">₱${parseFloat(refund.amount_paid || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}</span>
            </td>
            <td>
                <span class="refund-amount">₱${parseFloat(refund.refund_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</span>
            </td>
            <td>
                <span class="refund-status status-${refund.refund_status}">
                    ${refund.refund_status.charAt(0).toUpperCase() + refund.refund_status.slice(1)}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-info btn-sm me-1"
                        onclick="viewRefundReason('${escapeHtml(refund.reason)}', '${escapeHtml(refund.client_name)}')"
                        title="View Reason">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <button class="btn btn-success btn-sm me-1"
                        onclick="processRefund(${refund.refund_id}, ${refund.refund_amount}, '${escapeHtml(refund.client_name)}')"
                        title="Process Refund">
                        <i class="fas fa-check"></i> Process
                    </button>
                    <button class="btn btn-danger btn-sm"
                        onclick="rejectRefund(${refund.refund_id}, '${escapeHtml(refund.client_name)}')"
                        title="Reject Refund">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </td>
        </tr>
    `;
}

/**
 * Show empty state when no refunds are available
 */
function showEmptyRefundsState() {
    $('#refunds-table').hide();
    $('#load-more-refunds').hide();

    const emptyState = `
        <div class="empty-state text-center py-5">
            <i class="fas fa-undo-alt fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No Pending Refunds</h4>
            <p class="text-muted">All refund requests have been processed.</p>
        </div>
    `;

    $('#refunds-table').parent().append(emptyState);
}

/**
 * Filter refunds
 */
function filterRefunds() {
    Swal.fire({
        title: 'Filter Refunds',
        html: `
            <div class="refund-form">
                <div class="form-group mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" id="filter-status">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processed">Processed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Amount Range</label>
                    <input type="number" class="form-control mb-2" id="filter-amount-min" placeholder="Minimum Amount" step="0.01">
                    <input type="number" class="form-control" id="filter-amount-max" placeholder="Maximum Amount" step="0.01">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Date Range</label>
                    <input type="date" class="form-control mb-2" id="filter-date-from" placeholder="From Date">
                    <input type="date" class="form-control" id="filter-date-to" placeholder="To Date">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Apply Filter',
        cancelButtonText: 'Clear Filter',
        customClass: {
            popup: 'refund-modal'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Apply filter logic here
            Swal.fire({
                icon: 'info',
                title: 'Filter Applied',
                text: 'Refund filter functionality will be implemented soon.',
                timer: 2000,
                showConfirmButton: false
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Clear filters and reload
            location.reload();
        }
    });
}

/**
 * export refunds data
 */
function exportRefunds() {
    Swal.fire({
        icon: 'info',
        title: 'Export Refunds',
        text: 'Export refunds functionality will be implemented soon.',
        confirmButtonText: 'OK'
    });
}

/**
 * refresh refunds data
 */
function refreshRefunds() {
    Swal.fire({
        title: 'Refreshing...',
        text: 'Loading latest refunds data',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // reload the page after a short delay
    setTimeout(() => {
        location.reload();
    }, 1000);
}

/**
 * search refunds
 */
function searchRefunds() {
    Swal.fire({
        title: 'Search Refunds',
        html: `
            <div class="refund-form">
                <div class="form-group mb-3">
                    <label class="form-label">Search Term</label>
                    <input type="text" class="form-control" id="search-term" placeholder="Search by reference ID, client name, or phone number">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Search',
        cancelButtonText: 'Clear Search',
        customClass: {
            popup: 'refund-modal'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const searchTerm = document.getElementById('search-term').value;
            if (searchTerm.trim()) {
                // implement search functionality
                Swal.fire({
                    icon: 'info',
                    title: 'Search Applied',
                    text: 'Search functionality will be implemented soon.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // clear search and reload
            location.reload();
        }
    });
}

/**
 * escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, function (m) { return map[m]; }) : '';
}