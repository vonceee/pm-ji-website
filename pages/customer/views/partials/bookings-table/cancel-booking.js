document.addEventListener('DOMContentLoaded', function () {
    let bookingToCancel = null;

    // Handle cancel booking button clicks
    document.querySelectorAll('.cancel-booking').forEach(button => {
        button.addEventListener('click', function () {
            bookingToCancel = {
                id: this.dataset.bookingId,
                referenceNumber: this.dataset.referenceNumber,
                eventType: this.dataset.eventType,
                eventDate: this.dataset.eventDate,
                eventTime: this.dataset.eventTime
            };

            // Populate modal with booking details
            document.getElementById('cancel-event-type').textContent = bookingToCancel.eventType;
            document.getElementById('cancel-event-date').textContent = bookingToCancel.eventDate;
            document.getElementById('cancel-event-time').textContent = bookingToCancel.eventTime;
            document.getElementById('cancel-reference-number').textContent = bookingToCancel.referenceNumber;

            // Clear previous reason
            document.getElementById('cancellation-reason').value = '';
            document.getElementById('cancellation-reason').classList.remove('is-invalid');

            // Show modal
            $('#cancelBookingModal').modal('show');
        });
    });

    // Handle confirm cancellation
    document.getElementById('confirm-cancel-booking').addEventListener('click', function () {
        if (!bookingToCancel) return;

        const reason = document.getElementById('cancellation-reason').value.trim();

        // Validate reason
        if (!reason) {
            document.getElementById('cancellation-reason').classList.add('is-invalid');
            showAlert('danger', 'Please provide a reason for cancellation');
            return;
        }

        const confirmButton = this;
        const originalText = confirmButton.innerHTML;

        // Show loading state
        confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
        confirmButton.disabled = true;

        // Send cancellation request
        fetch('/NEW-PM-JI-RESERVIFY/pages/customer/actions/cancel_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                booking_id: bookingToCancel.id,
                reason: reason
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', `
                    <strong>Booking cancelled successfully!</strong><br>
                    Reference: ${data.data.reference_number}<br>
                    ${data.data.refund_amount > 0 ? `Refund Amount: ₱${parseFloat(data.data.refund_amount).toFixed(2)}` : ''}
                `);
                    $('#cancelBookingModal').modal('hide');
                    // Reload page to reflect changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert('danger', data.message || 'Failed to cancel booking. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'An error occurred while cancelling the booking. Please try again.');
            })
            .finally(() => {
                // Reset button state
                confirmButton.innerHTML = originalText;
                confirmButton.disabled = false;
            });
    });

    // Validate reason input on change
    document.getElementById('cancellation-reason').addEventListener('input', function () {
        if (this.value.trim()) {
            this.classList.remove('is-invalid');
        }
    });

    // Function to show alerts
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alert-container');
        const alertId = 'alert-' + Date.now();

        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;

        alertContainer.insertAdjacentHTML('beforeend', alertHtml);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alertElement = document.getElementById(alertId);
            if (alertElement) {
                $(alertElement).alert('close');
            }
        }, 5000);
    }

    // Enhanced modal details population (if using the existing modal system)
    window.populateModal = function (details) {
        const data = details.dataset;

        // Basic details
        document.getElementById('modalReferenceNumber').textContent = `Reference: ${data.referenceId}`;
        document.getElementById('modalEventType').textContent = data.eventType;
        document.getElementById('modalEventDate').textContent = formatDate(data.eventDate);
        document.getElementById('modalEventTime').textContent = `${formatTime(data.startTime)} – ${formatTime(data.endTime)}`;
        document.getElementById('modalDuration').textContent = data.duration;
        document.getElementById('modalLocation').textContent = data.location;
        document.getElementById('modalAmountPaid').textContent = `₱${parseFloat(data.amountPaid || 0).toFixed(2)}`;
        document.getElementById('modalBalance').textContent = `₱${parseFloat(data.balance || 0).toFixed(2)}`;
        document.getElementById('modalPaymentMethod').textContent = `${data.paymentMethod} / ${data.paymentType}`;

        // Status badges
        document.getElementById('modalStatusBadge').innerHTML = `
            <div class="status-badge ${data.status}">
                <div class="status-dot"></div>
                ${data.status.charAt(0).toUpperCase() + data.status.slice(1).replace('_', ' ')}
            </div>
        `;

        document.getElementById('modalPaymentStatus').innerHTML = `
            <span class="payment-status ${data.paymentStatus}">
                ${data.paymentStatus.charAt(0).toUpperCase() + data.paymentStatus.slice(1)}
            </span>
        `;

        // Cancellation details (if cancelled)
        if (data.status === 'cancelled_by_user' && data.cancellationReason) {
            const cancellationInfo = document.getElementById('modalCancellationInfo') || createCancellationInfoElement();
            cancellationInfo.innerHTML = `
                <div class="alert alert-info mt-3">
                    <h6><i class="fas fa-info-circle"></i> Cancellation Information</h6>
                    <p><strong>Reason:</strong> ${data.cancellationReason}</p>
                    <p><strong>Cancelled At:</strong> ${formatDateTime(data.cancelledAt)}</p>
                    ${data.refundStatus ? `<p><strong>Refund Status:</strong> <span class="badge badge-info">${data.refundStatus}</span></p>` : ''}
                    ${data.refundAmount > 0 ? `<p><strong>Refund Amount:</strong> ₱${parseFloat(data.refundAmount).toFixed(2)}</p>` : ''}
                </div>
            `;
        }
    };

    function createCancellationInfoElement() {
        const element = document.createElement('div');
        element.id = 'modalCancellationInfo';
        document.querySelector('#bookingDetailsModal .modal-body').appendChild(element);
        return element;
    }

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    function formatTime(timeStr) {
        const [hours, minutes] = timeStr.split(':');
        const date = new Date();
        date.setHours(parseInt(hours), parseInt(minutes));
        return date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    function formatDateTime(dateTimeStr) {
        if (!dateTimeStr) return 'N/A';
        const date = new Date(dateTimeStr);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }
});