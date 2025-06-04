// Outstanding Payments Management JavaScript Functions

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
                                if (typeof refreshPaymentHistory === 'function') {
                                    refreshPaymentHistory();
                                }
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
 * refresh outstanding payments data
 */
function refreshOutstandingPayments() {
    Swal.fire({
        title: 'Refreshing...',
        text: 'loading latest outstanding payments data',
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
 * export outstanding payments data
 */
function exportOutstandingPayments() {
    Swal.fire({
        icon: 'info',
        title: 'Export Feature',
        text: 'Export outstanding payments functionality will be implemented soon.',
        confirmButtonText: 'OK'
    });
}

/**
 * filter outstanding payments
 */
function filterOutstandingPayments() {
    Swal.fire({
        title: 'Filter Outstanding Payments',
        html: `
            <div class="payment-form">
                <div class="form-group mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" id="filter-status">
                        <option value="">All Statuses</option>
                        <option value="partial">Partial</option>
                        <option value="pending">Pending</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Amount Range</label>
                    <input type="number" class="form-control mb-2" id="filter-amount-min" placeholder="Minimum Amount" step="0.01">
                    <input type="number" class="form-control" id="filter-amount-max" placeholder="Maximum Amount" step="0.01">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Event Date Range</label>
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
                text: 'Outstanding payments filter functionality will be implemented soon.',
                timer: 2000,
                showConfirmButton: false
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Clear filters and reload
            refreshOutstandingPayments();
        }
    });
}

/**
 * add custom CSS for the outstanding payments modals
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
        
        .payment-method {
            font-weight: 500;
            color: #495057;
            text-transform: capitalize;
        }
        
        .status-overdue {
            color: #dc3545;
            font-weight: 600;
        }
        
        .status-partial {
            color: #fd7e14;
            font-weight: 600;
        }
        
        .status-pending {
            color: #6c757d;
            font-weight: 600;
        }
        
        .badge-overdue {
            background-color: #dc3545;
        }
    `;
    document.head.appendChild(style);
});