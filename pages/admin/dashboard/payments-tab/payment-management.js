// Payment Management JavaScript Functions

$(document).ready(function () {
    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});

/**
 * Mark a payment as fully paid
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
                <div class="form-group mb-3">
                    <label for="payment-method" class="form-label">Payment Method</label>
                    <select id="payment-method" class="form-select">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="gcash">GCash</option>
                        <option value="paymaya">PayMaya</option>
                        <option value="check">Check</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label for="payment-notes" class="form-label">Notes (Optional)</label>
                    <textarea id="payment-notes" class="form-control" rows="3" placeholder="Add any additional notes..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Mark as Paid',
        confirmButtonColor: '#28a745',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'payment-modal'
        },
        preConfirm: () => {
            const paymentMethod = document.getElementById('payment-method').value;
            const notes = document.getElementById('payment-notes').value;

            if (!paymentMethod) {
                Swal.showValidationMessage('Please select a payment method');
                return false;
            }

            return {
                paymentMethod: paymentMethod,
                notes: notes
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { paymentMethod, notes } = result.value;

            // Show loading
            Swal.fire({
                title: 'Processing...',
                text: 'Marking payment as fully paid',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Make AJAX request
            $.ajax({
                url: window.location.href,
                method: 'POST',
                data: {
                    action: 'mark_paid',
                    payment_id: paymentId,
                    payment_method: paymentMethod,
                    notes: notes
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
                            // Remove the row from the table or refresh the page
                            $(`tr[data-payment-id="${paymentId}"]`).fadeOut(300, function () {
                                $(this).remove();

                                // Check if table is empty
                                if ($('tbody tr').length === 0) {
                                    location.reload();
                                }
                            });
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to mark payment as paid'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to process request. Please try again.'
                    });
                }
            });
        }
    });
}

/**
 * Record a partial payment
 */
function recordPartialPayment(paymentId, balance) {
    Swal.fire({
        title: 'Record Partial Payment',
        html: `
            <div class="payment-form">
                <div class="form-group mb-3">
                    <label class="form-label">Outstanding Balance</label>
                    <div class="balance-display">₱${parseFloat(balance).toLocaleString('en-US', { minimumFractionDigits: 2 })}</div>
                </div>
                <div class="form-group mb-3">
                    <label for="partial-amount" class="form-label">Payment Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" id="partial-amount" class="form-control" 
                               min="0.01" max="${balance}" step="0.01" 
                               placeholder="0.00">
                    </div>
                    <small class="form-text text-muted">Maximum: ₱${parseFloat(balance).toLocaleString('en-US', { minimumFractionDigits: 2 })}</small>
                </div>
                <div class="form-group mb-3">
                    <label for="partial-payment-method" class="form-label">Payment Method</label>
                    <select id="partial-payment-method" class="form-select">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="gcash">GCash</option>
                        <option value="paymaya">PayMaya</option>
                        <option value="check">Check</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label for="partial-notes" class="form-label">Notes (Optional)</label>
                    <textarea id="partial-notes" class="form-control" rows="3" placeholder="Add any additional notes..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Record Payment',
        confirmButtonColor: '#ffc107',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'payment-modal'
        },
        preConfirm: () => {
            const amount = parseFloat(document.getElementById('partial-amount').value);
            const paymentMethod = document.getElementById('partial-payment-method').value;
            const notes = document.getElementById('partial-notes').value;

            if (!amount || amount <= 0) {
                Swal.showValidationMessage('Please enter a valid payment amount');
                return false;
            }

            if (amount > balance) {
                Swal.showValidationMessage('Payment amount cannot exceed the outstanding balance');
                return false;
            }

            if (!paymentMethod) {
                Swal.showValidationMessage('Please select a payment method');
                return false;
            }

            return {
                amount: amount,
                paymentMethod: paymentMethod,
                notes: notes
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { amount, paymentMethod, notes } = result.value;

            // Show loading
            Swal.fire({
                title: 'Processing...',
                text: 'Recording partial payment',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Make AJAX request
            $.ajax({
                url: window.location.href,
                method: 'POST',
                data: {
                    action: 'partial_payment',
                    payment_id: paymentId,
                    amount: amount,
                    payment_method: paymentMethod,
                    notes: notes
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
                            // Refresh the page to show updated data
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to record partial payment'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to process request. Please try again.'
                    });
                }
            });
        }
    });
}

/**
 * Switch between tabs
 */
function switchTab(tabName) {
    // Remove active class from all tabs and buttons
    $('.tab-content').removeClass('active');
    $('.tab-button').removeClass('active');

    // Add active class to selected tab and button
    $(`#${tabName}-tab`).addClass('active');
    $(`.tab-button[onclick="switchTab('${tabName}')"]`).addClass('active');
}

/**
 * Refresh payment data
 */
function refreshData() {
    Swal.fire({
        title: 'Refreshing...',
        text: 'Loading latest payment data',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Reload the page after a short delay
    setTimeout(() => {
        location.reload();
    }, 1000);
}

/**
 * Export payments data (placeholder function)
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
 * Add custom CSS for the payment modals
 */
$(document).ready(function () {
    // Add custom styles for SweetAlert modals
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
    `;
    document.head.appendChild(style);
});