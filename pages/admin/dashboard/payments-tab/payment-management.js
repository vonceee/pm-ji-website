// Payment Management JavaScript Functions

let currentHistoryPage = 1;
let isLoadingHistory = false;

$(document).ready(function () {
    // initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});

/**
 * mark a payment as fully paid
 */
function markAsPaid(paymentId, balance) {
    Swal.fire({
        title: 'Mark as Fully Paid',
        html: `
            <div class="payment-form">
                <div class="form-group mb-3">
                    <label class="form-label">Outstanding Balance</label>
                    <div class="balance-display">₱${parseFloat(balance).toLocaleString('en-US', { minimumFractionDigits: 2 })}</div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Mark as Paid',
        confirmButtonColor: '#28a745',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'payment-modal'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // show loading
            Swal.fire({
                title: 'Processing...',
                text: 'marking payment as fully paid',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // make AJAX request
            $.ajax({
                url: window.location.href,
                method: 'POST',
                data: {
                    action: 'mark_paid',
                    payment_id: paymentId
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // remove the row from the table or refresh the page
                            $(`tr[data-payment-id="${paymentId}"]`).fadeOut(300, function () {
                                $(this).remove();

                                // check if table is empty
                                if ($('#outstanding-tab tbody tr').length === 0) {
                                    location.reload();
                                }
                            });

                            // refresh payment history if it's the active tab
                            if ($('#history-tab').hasClass('active')) {
                                refreshPaymentHistory();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error);
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        }
    });
}

/**
 * switch between tabs
 */
function switchTab(tabName) {
    // remove active class from all tabs and buttons
    $('.tab-content').removeClass('active');
    $('.tab-button').removeClass('active');

    // add active class to selected tab and button
    $(`#${tabName}-tab`).addClass('active');
    $(`.tab-button[onclick="switchTab('${tabName}')"]`).addClass('active');

    // load payment history when switching to history tab
    if (tabName === 'history' && currentHistoryPage === 1) {
        loadPaymentHistory(1, true);
    }
}

/**
 * load payment history
 */
function loadPaymentHistory(page = 1, replace = false) {
    if (isLoadingHistory) return;
    
    isLoadingHistory = true;
    $('#history-loading').show();
    
    if (replace) {
        $('#load-more-history').hide();
    }

    $.ajax({
        url: window.location.href,
        method: 'GET',
        data: {
            action: 'get_payment_history',
            page: page,
            limit: 20
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                if (replace) {
                    // Replace the entire table body
                    $('#payment-history-tbody').empty();
                    currentHistoryPage = 1;
                }

                // Add new rows
                response.payments.forEach(function(payment) {
                    const row = createPaymentHistoryRow(payment);
                    $('#payment-history-tbody').append(row);
                });

                // Show/hide load more button
                if (response.hasMore) {
                    $('#load-more-history').show();
                    currentHistoryPage = page;
                } else {
                    $('#load-more-history').hide();
                }

                // Show empty state if no payments
                if (response.payments.length === 0 && page === 1) {
                    $('#payment-history-container').html(`
                        <div class="empty-state">
                            <i class="fas fa-history"></i>
                            <h4>No Payment History</h4>
                            <p>No payments have been recorded yet.</p>
                        </div>
                    `);
                }
            } else {
                console.error('Error loading payment history:', response.message);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error loading payment history:', error);
        },
        complete: function() {
            isLoadingHistory = false;
            $('#history-loading').hide();
        }
    });
}

/**
 * create payment history table row
 */
function createPaymentHistoryRow(payment) {
    const createdDate = new Date(payment.created_at);
    const dateStr = createdDate.toLocaleDateString('en-US', { 
        month: 'short', 
        day: '2-digit', 
        year: 'numeric' 
    });
    const timeStr = createdDate.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });

    return `
        <tr>
            <td>
                <div class="date-info">
                    <div>${dateStr}</div>
                    <small class="text-muted">${timeStr}</small>
                </div>
            </td>
            <td>
                <strong>${escapeHtml(payment.reference_id)}</strong>
            </td>
            <td>
                <div class="client-info">
                    <div class="client-name">${escapeHtml(payment.client_name)}</div>
                    <small class="text-muted">${escapeHtml(payment.phone_number || '')}</small>
                </div>
            </td>
            <td>
                <div class="event-info">
                    <div>${escapeHtml(payment.event_type)}</div>
                    <small class="text-muted">${escapeHtml(payment.city || '')}</small>
                </div>
            </td>
            <td>
                <span class="total-amount">₱${parseFloat(payment.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</span>
            </td>
            <td>
                <span class="amount-paid">₱${parseFloat(payment.amount_paid).toLocaleString('en-US', { minimumFractionDigits: 2 })}</span>
            </td>
            <td>
                <span class="balance-amount">₱${parseFloat(payment.balance).toLocaleString('en-US', { minimumFractionDigits: 2 })}</span>
            </td>
            <td>
                <span class="payment-status status-${payment.payment_status}">
                    ${payment.payment_status.charAt(0).toUpperCase() + payment.payment_status.slice(1)}
                </span>
            </td>
            <td>
                <span class="payment-method">${payment.payment_method ? payment.payment_method.charAt(0).toUpperCase() + payment.payment_method.slice(1) : 'N/A'}</span>
            </td>
        </tr>
    `;
}

/**
 * load more payment history
 */
function loadMoreHistory() {
    loadPaymentHistory(currentHistoryPage + 1, false);
}

/**
 * refresh payment history
 */
function refreshPaymentHistory() {
    loadPaymentHistory(1, true);
}

/**
 * filter payment history (placeholder)
 */
function filterPaymentHistory() {
    Swal.fire({
        title: 'Filter Payment History',
        html: `
            <div class="payment-form">
                <div class="form-group mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" id="filter-status">
                        <option value="">All Statuses</option>
                        <option value="paid">Paid</option>
                        <option value="partial">Partial</option>
                        <option value="pending">Pending</option>
                        <option value="overdue">Overdue</option>
                    </select>
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
            popup: 'payment-modal'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Apply filter logic here
            Swal.fire({
                icon: 'info',
                title: 'Filter Applied',
                text: 'Filter functionality will be implemented soon.',
                timer: 2000,
                showConfirmButton: false
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Clear filters and reload
            refreshPaymentHistory();
        }
    });
}

/**
 * export payment history
 */
function exportPaymentHistory() {
    Swal.fire({
        icon: 'info',
        title: 'Export Payment History',
        text: 'Export functionality will be implemented soon.',
        confirmButtonText: 'OK'
    });
}

/**
 * refresh payment data
 */
function refreshData() {
    Swal.fire({
        title: 'Refreshing...',
        text: 'loading latest payment data',
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
 * export payments data (placeholder function)
 */
function exportPayments() {
    Swal.fire({
        icon: 'info',
        title: 'Export Feature',
        text: 'Export functionality will be implemented soon.',
        confirmButtonText: 'OK'
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
    return text ? text.replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
}

/**
 * add custom CSS for the payment modals
 */
$(document).ready(function () {
    const style = document.createElement('style');
    style.textContent = `
        .payment-modal .swal2-html-container {
            text-align: left;
        }
        
        .payment-form .form-group {
            margin-bottom: 1rem;
        }
        
        .payment-form .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .balance-display {
            font-size: 1.5rem;
            font-weight: bold;
            color: #dc3545;
            text-align: center;
            padding: 0.75rem;
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            border: 2px solid #dee2e6;
        }
        
        .payment-form .form-control,
        .payment-form .form-select {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }
        
        .payment-form .form-control:focus,
        .payment-form .form-select:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .payment-form .input-group-text {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
        }
        
        .payment-form .form-text {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .total-amount {
            font-weight: 600;
            color: #6f42c1;
            font-size: 0.95rem;
        }
        
        .payment-method {
            font-weight: 500;
            color: #495057;
            text-transform: capitalize;
        }
        
        #history-loading {
            padding: 2rem;
            font-size: 1.1rem;
            color: #6c757d;
        }
        
        #load-more-history {
            margin: 1rem 0;
        }
    `;
    document.head.appendChild(style);
});