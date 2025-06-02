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

// on document ready (or bottom of <body>)
function openModal(detailsId) {
    // populate
    populateModal(document.querySelector(detailsId));
    // show
    const modal = document.getElementById('bookingDetailsModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('bookingDetailsModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

function populateModal(details) {
    // read data-attrs
    const { referenceId, eventType, eventDate, startTime, endTime,
        location, amountPaid, balance, paymentMethod,
        paymentStatus, status, duration } = details.dataset;

    document.getElementById('modalReferenceNumber').textContent = `Reference: ${referenceId}`;
    document.getElementById('modalEventType').textContent = eventType;
    document.getElementById('modalEventDate').textContent = formatDate(eventDate);
    document.getElementById('modalEventTime').textContent = `${formatTime(startTime)} – ${formatTime(endTime)}`;
    document.getElementById('modalDuration').textContent = duration;
    document.getElementById('modalLocation').textContent = location;
    document.getElementById('modalAmountPaid').textContent = `₱${parseFloat(amountPaid).toFixed(2)}`;
    document.getElementById('modalBalance').textContent = `₱${parseFloat(balance).toFixed(2)}`;
    document.getElementById('modalPaymentMethod').textContent = `${paymentMethod} / ${details.dataset.paymentType}`;

    // status badges
    document.getElementById('modalStatusBadge').innerHTML = `
    <div class="status-badge ${status}">
      <div class="status-dot"></div>
      ${status.charAt(0).toUpperCase() + status.slice(1)}
    </div>
  `;
    document.getElementById('modalPaymentStatus').innerHTML = `
    <span class="payment-status ${paymentStatus}">
      ${paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1)}
    </span>
  `;
}

// wire up buttons
document.querySelectorAll('.toggle-details').forEach(btn => {
    btn.addEventListener('click', () => {
        openModal(btn.getAttribute('data-target'));
    });
});

// click‐outside & Escape to close
document.getElementById('bookingDetailsModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeModal();
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
});

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

// close modal when clicking outside
document.getElementById('bookingDetailsModal').addEventListener('click', function (e) {
    if (e.target === this) {
        closeModal();
    }
});

// close modal with Escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});