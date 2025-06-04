// payment Management JavaScript

// mark payment as fully paid
function markAsPaid(paymentId, balanceAmount) {
    Swal.fire({
        title: 'Mark Payment as Paid',
        html: `
            <div class="payment-form">
                <p>Confirm that the balance of <strong>₱${balanceAmount.toLocaleString()}</strong> has been paid.</p>
                <div class="form-group">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Mark as Paid',
        confirmButtonColor: '#28a745',
        cancelButtonText: 'Cancel',
        width: '500px',
        preConfirm: () => {
            const notes = document.getElementById('paymentNotes').value;

            return {
                paymentMethod: paymentMethod,
                notes: notes
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            processPayment('mark_paid', paymentId, {
                payment_method: result.value.paymentMethod,
                notes: result.value.notes
            });
        }
    });
}

// record partial payment
function recordPartialPayment(paymentId, maxBalance) {
    Swal.fire({
        title: 'Record Partial Payment',
        html: `
            <div class="payment-form">
                <p>Outstanding Balance: <strong>₱${maxBalance.toLocaleString()}</strong></p>
                <div class="form-group">
                    <label for="partialAmount">Amount Received:</label>
                    <input type="number" id="partialAmount" class="form-control" 
                           min="0.01" max="${maxBalance}" step="0.01" 
                           placeholder="Enter amount received">
                </div>
                <div class="form-group">
                    <label for="partialPaymentMethod">Payment Method:</label>
                    <select id="partialPaymentMethod" class="form-control">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="gcash">GCash</option>
                        <option value="paymaya">PayMaya</option>
                        <option value="check">Check</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="partialNotes">Notes (Optional):</label>
                    <textarea id="partialNotes" class="form-control" rows="3" 
                              placeholder="add any notes about this payment..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Record Payment',
        confirmButtonColor: '#ffc107',
        cancelButtonText: 'Cancel',
        width: '500px',
        preConfirm: () => {
            const amount = parseFloat(document.getElementById('partialAmount').value);
            const paymentMethod = document.getElementById('partialPaymentMethod').value;
            const notes = document.getElementById('partialNotes').value;

            // validate amount
            if (!amount || isNaN(amount) || amount <= 0) {
                Swal.showValidationMessage('Please enter a valid amount');
                return false;
            }

            if (amount > maxBalance) {
                Swal.showValidationMessage(`Amount cannot exceed ₱${maxBalance.toLocaleString()}`);
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
            processPayment('partial_payment', paymentId, {
                amount: result.value.amount,
                payment_method: result.value.paymentMethod,
                notes: result.value.notes
            });
        }
    });
}

// process payment via AJAX
function processPayment(action, paymentId, data) {
    // show loading state
    Swal.fire({
        title: 'Processing...',
        text: 'please wait while we update the payment.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    // prepare form data
    const formData = new FormData();
    formData.append('action', action);
    formData.append('payment_id', paymentId);

    Object.keys(data).forEach(key => {
        formData.append(key, data[key]);
    });

    // send AJAX request
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    confirmButtonColor: '#28a745'
                }).then(() => {
                    // refresh the page to show updated data
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    confirmButtonColor: '#28a745'
                }).then(() => {
                    // refresh the page to show updated data
                    location.reload();
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                confirmButtonColor: '#28a745'
            }).then(() => {
                // refresh the page to show updated data
                location.reload();
            });
        });
}

// view payment details
function viewPaymentDetails(paymentId) {
    // add loading state to the button
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    // fetch payment details (you can implement this endpoint)
    fetch(`?action=get_payment_details&payment_id=${paymentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showPaymentDetailsModal(data.payment);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to load payment details.',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to load payment details.',
                confirmButtonColor: '#dc3545'
            });
        })
        .finally(() => {
            // restore button state
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

// show payment details in modal
function showPaymentDetailsModal(payment) {
    const eventDate = new Date(payment.reservation_date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    Swal.fire({
        title: 'Payment Details',
        html: `
            <div class="payment-details">
                <div class="detail-section">
                    <h6>Booking Information</h6>
                    <p><strong>Reference ID:</strong> ${payment.reference_id}</p>
                    <p><strong>Client:</strong> ${payment.client_name}</p>
                    <p><strong>Event Type:</strong> ${payment.event_type}</p>
                    <p><strong>Date:</strong> ${eventDate}</p>
                    <p><strong>Time:</strong> ${payment.start_time} - ${payment.end_time}</p>
                    <p><strong>Location:</strong> ${payment.city}</p>
                </div>
                <div class="detail-section">
                    <h6>Payment Information</h6>
                    <p><strong>Amount Paid:</strong> ₱${parseFloat(payment.amount_paid).toLocaleString()}</p>
                    <p><strong>Outstanding Balance:</strong> ₱${parseFloat(payment.balance).toLocaleString()}</p>
                    <p><strong>Payment Method:</strong> ${payment.payment_method}</p>
                    <p><strong>Status:</strong> <span class="status-${payment.status}">${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}</span></p>
                    <p><strong>Last Updated:</strong> ${payment.updated_at ? new Date(payment.updated_at).toLocaleString() : 'N/A'}</p>
                </div>
            </div>
        `,
        width: '600px',
        confirmButtonText: 'Close',
        confirmButtonColor: '#6c757d'
    });
}

// refresh data
function refreshData() {
    location.reload();
}

// auto-refresh every 5 minutes
setTimeout(function () {
    location.reload();
}, 300000);

// add custom styles for SweetAlert forms
const style = document.createElement('style');
style.textContent = `
    .payment-form .form-group {
        margin-bottom: 1rem;
        text-align: left;
    }
    
    .payment-form label {
        display: block;
        margin-bottom: 0.25rem;
        font-weight: 600;
        color: #333;
    }
    
    .payment-form .form-control {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.875rem;
    }
    
    .payment-form .form-control:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .payment-details {
        text-align: left;
    }
    
    .payment-details .detail-section {
        margin-bottom: 1.5rem;
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 4px;
    }
    
    .payment-details .detail-section:last-child {
        margin-bottom: 0;
    }
    
    .payment-details h6 {
        margin-bottom: 1rem;
        color: #495057;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 0.5rem;
    }
    
    .payment-details p {
        margin-bottom: 0.5rem;
    }
    
    .payment-details p:last-child {
        margin-bottom: 0;
    }
`;
document.head.appendChild(style);