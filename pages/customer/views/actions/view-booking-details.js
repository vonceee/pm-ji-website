// view details modal population (if using the existing modal system)
window.populateModal = function (details) {
    const data = details.dataset;

    // basic details
    document.getElementById('modalReferenceNumber').textContent = `Reference: ${data.referenceId}`;
    document.getElementById('modalEventType').textContent = data.eventType;
    document.getElementById('modalEventDate').textContent = formatDate(data.eventDate);
    document.getElementById('modalEventTime').textContent = `${formatTime(data.startTime)} – ${formatTime(data.endTime)}`;
    document.getElementById('modalDuration').textContent = data.duration;
    document.getElementById('modalLocation').textContent = data.location;
    document.getElementById('modalAmountPaid').textContent = `₱${parseFloat(data.amountPaid || 0).toFixed(2)}`;
    document.getElementById('modalBalance').textContent = `₱${parseFloat(data.balance || 0).toFixed(2)}`;
    document.getElementById('modalPaymentMethod').textContent = `${data.paymentMethod} / ${data.paymentType}`;

    // status badges
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

    // cancellation details (if cancelled)
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

