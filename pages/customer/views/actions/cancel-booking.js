document.addEventListener('DOMContentLoaded', function () {
    let bookingToCancel = null;

    // handle cancel booking button clicks
    document.querySelectorAll('.cancel-booking').forEach(button => {
        button.addEventListener('click', function () {
            bookingToCancel = {
                id: this.dataset.bookingId,
                referenceId: this.dataset.referenceId,
                eventStatus: this.dataset.eventStatus,
                eventType: this.dataset.eventType,
                eventDate: this.dataset.eventDate,
                eventTime: this.dataset.eventTime
            };

            // populate modal with booking details
            document.getElementById('cancel-event-type').textContent = bookingToCancel.eventType;
            document.getElementById('cancel-event-date').textContent = bookingToCancel.eventDate;
            document.getElementById('cancel-event-time').textContent = bookingToCancel.eventTime;
            document.getElementById('cancel-reference-id').textContent = bookingToCancel.referenceId;
            document.getElementById('cancel-event-status').textContent = bookingToCancel.eventStatus;

            // clear previous reason
            document.getElementById('cancellation-reason').value = '';
            document.getElementById('cancellation-reason').classList.remove('is-invalid');

            // show modal
            $('#cancelBookingModal').modal('show');
        });
    });

    // handle confirm cancellation
    document.getElementById('confirm-cancel-booking').addEventListener('click', function () {
        if (!bookingToCancel) return;

        const reason = document.getElementById('cancellation-reason').value.trim();

        // validate reason
        if (!reason) {
            document.getElementById('cancellation-reason').classList.add('is-invalid');
            showAlert('danger', 'provide a reason for cancellation');
            return;
        }

        const confirmButton = this;
        const originalText = confirmButton.innerHTML;

        // show loading state
        confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
        confirmButton.disabled = true;

        // send cancellation request
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
                    <strong>Booking Cancelled Successfully!</strong><br>
                    Reference: ${data.data.reference_number}<br>
                    ${data.data.refund_amount > 0 ? `Refund Amount: ₱${parseFloat(data.data.refund_amount).toFixed(2)}` : ''}
                `);
                    $('#cancelBookingModal').modal('hide');
                    // reload page to reflect changes
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
                // reset button state
                confirmButton.innerHTML = originalText;
                confirmButton.disabled = false;
            });
    });

    // validate reason input on change
    document.getElementById('cancellation-reason').addEventListener('input', function () {
        if (this.value.trim()) {
            this.classList.remove('is-invalid');
        }
    });

    // function to show alerts
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

        // auto-dismiss after 5 seconds
        setTimeout(() => {
            const alertElement = document.getElementById(alertId);
            if (alertElement) {
                $(alertElement).alert('close');
            }
        }, 5000);
    }
});