// view details modal population (if using the existing modal system)
window.populateModal = function (details) {
    const data = details.dataset;
    
    console.log('Populating modal with data:', data); // Debug log

    // basic details
    document.getElementById('modalReferenceNumber').textContent = `Reference: ${data.referenceId || 'N/A'}`;
    document.getElementById('modalEventType').textContent = data.eventType || 'N/A';
    document.getElementById('modalEventDate').textContent = formatDate(data.eventDate);
    document.getElementById('modalEventTime').textContent = `${formatTime(data.startTime)} – ${formatTime(data.endTime)}`;
    document.getElementById('modalDuration').textContent = data.duration || 'N/A';
    document.getElementById('modalLocation').textContent = data.location || 'N/A';
    document.getElementById('modalAmountPaid').textContent = `₱${parseFloat(data.amountPaid || 0).toFixed(2)}`;
    document.getElementById('modalBalance').textContent = `₱${parseFloat(data.balance || 0).toFixed(2)}`;
    document.getElementById('modalPaymentMethod').textContent = `${data.paymentMethod || 'N/A'} / ${data.paymentType || 'N/A'}`;

    // payment date
    const paymentDateElement = document.getElementById('modalPaymentDate');
    if (data.paymentDate && data.paymentDate !== 'N/A') {
        paymentDateElement.textContent = formatDate(data.paymentDate);
    } else {
        paymentDateElement.textContent = 'N/A';
    }

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

    // handle payment screenshot
    const screenshotSection = document.getElementById('paymentScreenshotSection');
    const screenshotImg = document.getElementById('paymentScreenshot');
    
    if (data.paymentScreenshot && data.paymentScreenshot !== '' && data.paymentScreenshot !== 'N/A') {
        // construct the full path to the payment screenshot
        const screenshotPath = `/NEW-PM-JI-RESERVIFY/uploads/payment-screenshots/${data.paymentScreenshot}`;
        
        screenshotImg.src = screenshotPath;
        screenshotImg.alt = `Payment Screenshot for ${data.referenceId}`;
        screenshotSection.style.display = 'block';
        
        // Add error handling for broken images
        screenshotImg.onerror = function() {
            console.log('failed to load screenshot:', screenshotPath);
            screenshotSection.style.display = 'none';
        };
        
        screenshotImg.onload = function() {
            console.log('Screenshot loaded successfully:', screenshotPath);
        };
    } else {
        screenshotSection.style.display = 'none';
    }

    // Clear any existing cancellation info first
    const existingCancellationInfo = document.getElementById('modalCancellationInfo');
    if (existingCancellationInfo) {
        existingCancellationInfo.remove();
    }

    // cancellation details (if cancelled)
    if (data.status === 'cancelled_by_user' && data.cancellationReason) {
        const cancellationInfo = createCancellationInfoElement();
        cancellationInfo.innerHTML = `
            <div class="alert alert-info mt-3">
                <h6><i class="fas fa-info-circle"></i> Cancellation Information</h6>
                <p><strong>Reason:</strong> ${data.cancellationReason}</p>
                <p><strong>Cancelled At:</strong> ${formatDateTime(data.cancelledAt)}</p>
                ${data.refundStatus ? `<p><strong>Refund Status:</strong> <span class="badge badge-info">${data.refundStatus}</span></p>` : ''}
                ${data.refundAmount && parseFloat(data.refundAmount) > 0 ? `<p><strong>Refund Amount:</strong> ₱${parseFloat(data.refundAmount).toFixed(2)}</p>` : ''}
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
    if (!dateStr || dateStr === 'N/A') return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(timeStr) {
    if (!timeStr || timeStr === 'N/A') return 'N/A';
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
    if (!dateTimeStr || dateTimeStr === 'N/A') return 'N/A';
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

// screenshot modal functions
function openScreenshotModal(src) {
    const modal = document.getElementById('screenshotModal');
    const fullSizeImg = document.getElementById('fullSizeScreenshot');
    
    fullSizeImg.src = src;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeScreenshotModal() {
    const modal = document.getElementById('screenshotModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// main modal functions
function openModal(detailsId) {
    console.log('Opening modal for:', detailsId); // Debug log
    
    // Get the details element that contains the data attributes
    const detailsElement = document.querySelector(detailsId);
    
    if (!detailsElement) {
        console.error('Details element not found:', detailsId);
        return;
    }
    
    console.log('Found details element:', detailsElement); // Debug log
    console.log('Element dataset:', detailsElement.dataset); // Debug log
    
    // Use the window.populateModal function to populate the modal
    window.populateModal(detailsElement);
    
    // show modal
    const modal = document.getElementById('bookingDetailsModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('bookingDetailsModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// wire up buttons - Use event delegation for dynamically added buttons
document.addEventListener('click', function(e) {
    if (e.target.matches('.toggle-details') || e.target.closest('.toggle-details')) {
        const btn = e.target.matches('.toggle-details') ? e.target : e.target.closest('.toggle-details');
        const target = btn.getAttribute('data-target');
        console.log('Button clicked, target:', target); // Debug log
        openModal(target);
    }
});

// click outside & Escape to close main modal
document.getElementById('bookingDetailsModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeModal();
});

// click outside & Escape to close screenshot modal
document.getElementById('screenshotModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeScreenshotModal();
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModal();
        closeScreenshotModal();
    }
});