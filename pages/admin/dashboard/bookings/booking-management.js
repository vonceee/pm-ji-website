// global variables
let currentBookingId = null;

// initialize page when DOM loads
document.addEventListener('DOMContentLoaded', function () {
    // auto-dismiss alerts after 5 seconds
    setTimeout(function () {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
                alert.classList.add('fade');
            }
        });
    }, 5000);

    // initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// update booking status function
async function updateBookingStatus(bookingId, newStatus) {
    // Show confirmation dialog
    const confirmMessage = getConfirmationMessage(newStatus);
    if (!confirm(confirmMessage)) {
        return;
    }

    // show loading state
    showLoadingState(bookingId, newStatus);

    try {
        const response = await fetch('/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/update-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                booking_id: bookingId,
                status: newStatus,
                admin_notes: document.getElementById('newNote')?.value || ''
            })
        });

        const result = await response.json();

        if (result.success) {
            // show success message
            showAlert('success', result.message || `Booking ${newStatus} successfully!`);

            // close modal if open
            const modal = bootstrap.Modal.getInstance(document.getElementById('bookingDetailsModal'));
            if (modal) {
                modal.hide();
            }

            // reload page to refresh data
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert('danger', result.message || 'An error occurred while updating the booking.');
        }
    } catch (error) {
        console.error('Error updating booking status:', error);
        showAlert('danger', 'Network error occurred. Please try again.');
    } finally {
        hideLoadingState();
    }
}

// update booking status from modal
function updateBookingStatusFromModal(newStatus) {
    if (currentBookingId) {
        updateBookingStatus(currentBookingId, newStatus);
    }
}

// get confirmation message based on status
function getConfirmationMessage(status) {
    const messages = {
        'approved': 'Are you sure you want to approve this booking? The customer will be notified via email.',
        'cancelled': 'Are you sure you want to cancel this booking? This action cannot be undone and the customer will be notified.',
        'completed': 'Mark this booking as completed? This indicates the event has finished successfully.',
        'pending': 'Move this booking back to pending status?'
    };
    return messages[status] || 'Are you sure you want to update this booking?';
}

// show loading state
function showLoadingState(bookingId, status) {
    const buttons = document.querySelectorAll(`[onclick*="${bookingId}"]`);
    buttons.forEach(button => {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    });
}

// hide loading state
function hideLoadingState() {
    const buttons = document.querySelectorAll('button[disabled]');
    buttons.forEach(button => {
        button.disabled = false;
        // reset button content based on its class
        if (button.classList.contains('btn-success')) {
            button.innerHTML = '<i class="fas fa-check"></i>';
        } else if (button.classList.contains('btn-danger')) {
            button.innerHTML = '<i class="fas fa-times"></i>';
        } else if (button.classList.contains('btn-primary')) {
            button.innerHTML = '<i class="fas fa-check-double"></i>';
        } else if (button.classList.contains('btn-warning')) {
            button.innerHTML = '<i class="fas fa-undo"></i>';
        } else if (button.classList.contains('btn-outline-info')) {
            button.innerHTML = '<i class="fas fa-eye"></i>';
        }
    });
}

// show alert message
function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());

    // create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    // insert at top of container
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);

    // auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.classList.remove('show');
            setTimeout(() => {
                alertDiv.remove();
            }, 150);
        }
    }, 5000);
}

// view payment screenshot
function viewPaymentScreenshot(bookingId) {
    const screenshotModal = new bootstrap.Modal(document.getElementById('paymentScreenshotModal'));
    const screenshotImg = document.getElementById('paymentScreenshotImg');
    const screenshotError = document.getElementById('paymentScreenshotError');

    // show loading state
    screenshotImg.style.display = 'none';
    screenshotError.style.display = 'none';
    document.getElementById('screenshotLoading').style.display = 'block';

    // set image source
    screenshotImg.src = `/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/get-payment-screenshot.php?booking_id=${bookingId}`;

    // handle image load success
    screenshotImg.onload = function () {
        document.getElementById('screenshotLoading').style.display = 'none';
        screenshotImg.style.display = 'block';
    };

    // handle image load error
    screenshotImg.onerror = function () {
        document.getElementById('screenshotLoading').style.display = 'none';
        screenshotError.style.display = 'block';
    };

    screenshotModal.show();
}

// view booking details - UPDATED VERSION with Payment Screenshot
function viewBookingDetails(booking) {
    currentBookingId = booking.id;

    // populate basic information
    document.getElementById('modalReferenceId').textContent = `#${booking.reference_id}`;
    document.getElementById('modalRefId').textContent = `#${booking.reference_id}`;
    document.getElementById('modalDate').textContent = formatDate(booking.reservation_date);
    document.getElementById('modalTime').textContent = `${booking.start_time} - ${booking.end_time}`;
    document.getElementById('modalDuration').textContent = `${booking.duration} hours`;
    document.getElementById('modalEvent').textContent = booking.event_type;

    // status with badge
    const statusSpan = document.getElementById('modalStatus');
    statusSpan.textContent = booking.status.charAt(0).toUpperCase() + booking.status.slice(1);
    statusSpan.className = `status-badge status-${booking.status.toLowerCase()}`;

    // customer information
    document.getElementById('modalCustomerName').textContent = `${booking.first_name} ${booking.last_name}`;
    document.getElementById('modalCustomerEmail').textContent = booking.email || 'N/A';
    document.getElementById('modalCustomerPhone').textContent = booking.phone || 'N/A';

    // payment information
    document.getElementById('modalPaymentMethod').textContent = booking.payment_method || 'N/A';
    document.getElementById('modalPaymentType').textContent = booking.payment_type || 'N/A';

    const paymentStatusSpan = document.getElementById('modalPaymentStatus');
    if (booking.payment_status) {
        paymentStatusSpan.textContent = booking.payment_status.charAt(0).toUpperCase() + booking.payment_status.slice(1);
        paymentStatusSpan.className = `payment-badge payment-${booking.payment_status.toLowerCase()}`;
    } else {
        paymentStatusSpan.textContent = 'N/A';
        paymentStatusSpan.className = 'text-muted';
    }

    const amountPaid = parseFloat(booking.amount_paid) || 0;
    const balance = parseFloat(booking.balance) || 0;
    const total = amountPaid + balance;

    document.getElementById('modalAmountPaid').textContent = `₱${amountPaid.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
    document.getElementById('modalBalance').textContent = `₱${balance.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;

    // check if modalTotal element exists (from the separate modal file)
    const totalElement = document.getElementById('modalTotal');
    if (totalElement) {
        totalElement.textContent = `₱${total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
    }

    document.getElementById('modalPaymentDate').textContent = booking.payment_date ? formatDate(booking.payment_date) : 'N/A';

    // payment screenshot button - NEW ADDITION
    const screenshotBtn = document.getElementById('modalPaymentScreenshotBtn');
    if (screenshotBtn) {
        if (booking.payment_method && booking.payment_status && booking.payment_status !== 'pending') {
            screenshotBtn.style.display = 'inline-block';
            screenshotBtn.onclick = () => viewPaymentScreenshot(booking.id);
        } else {
            screenshotBtn.style.display = 'none';
        }
    }

    // event details - map to correct field IDs
    // for main index.php modal structure
    const locationElement = document.getElementById('modalLocation');
    if (locationElement) {
        locationElement.textContent = booking.full_address || `${booking.city}, ${booking.barangay}`;
    }

    const cityElement = document.getElementById('modalCity');
    if (cityElement) {
        cityElement.textContent = booking.city || 'N/A';
    }

    // for separate modal file structure
    const venueElement = document.getElementById('modalVenue');
    if (venueElement) {
        venueElement.textContent = booking.full_address || `${booking.city}, ${booking.barangay}`;
    }

    const guestsElement = document.getElementById('modalGuests');
    if (guestsElement) {
        guestsElement.textContent = booking.guests || 'Not specified';
    }

    const packageElement = document.getElementById('modalPackage');
    if (packageElement) {
        packageElement.textContent = booking.package || 'Standard Package';
    }

    const requestsElement = document.getElementById('modalRequests');
    if (requestsElement) {
        requestsElement.textContent = booking.special_requests || 'None';
    }

    // timeline
    const timeline = document.getElementById('modalTimeline');
    if (timeline) {
        timeline.innerHTML = generateTimeline(booking);
    }

    // notes
    const notes = document.getElementById('modalNotes');
    if (notes) {
        notes.innerHTML = generateNotes(booking);
    }

    // clear new note field if it exists
    const newNoteField = document.getElementById('newNote');
    if (newNoteField) {
        newNoteField.value = '';
    }

    // update modal footer buttons based on current status
    updateModalButtons(booking.status);

    // show modal
    const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
    modal.show();
}

// format date helper
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// generate timeline HTML
function generateTimeline(booking) {
    let timelineHTML = '';

    // booking created
    timelineHTML += `
        <div class="timeline-item">
            <small class="text-muted">${formatDateTime(booking.created_at)}</small><br>
            <strong>Booking Created</strong><br>
            Customer submitted booking request
        </div>
    `;

    // payment received (if exists)
    if (booking.payment_date) {
        timelineHTML += `
            <div class="timeline-item">
                <small class="text-muted">${formatDateTime(booking.payment_date)}</small><br>
                <strong>Payment Received</strong><br>
                ${booking.payment_type} payment of ₱${parseFloat(booking.amount_paid || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}
            </div>
        `;
    }

    // current status
    const statusText = {
        'pending': 'Awaiting admin approval',
        'approved': 'Booking confirmed and approved',
        'completed': 'Event completed successfully',
        'cancelled': 'Booking cancelled'
    };

    timelineHTML += `
        <div class="timeline-item">
            <small class="text-muted">${formatDateTime(booking.updated_at || booking.created_at)}</small><br>
            <strong>Status: ${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}</strong><br>
            ${statusText[booking.status] || 'Status updated'}
        </div>
    `;

    return timelineHTML;
}

// generate notes HTML
function generateNotes(booking) {
    let notesHTML = '';

    // you can expand this to show actual notes from database
    if (booking.customer_notes) {
        notesHTML += `<p><strong>Customer Note:</strong> ${booking.customer_notes}</p>`;
    }

    if (booking.admin_notes) {
        notesHTML += `<p><strong>Admin Note:</strong> ${booking.admin_notes}</p>`;
    }

    if (booking.special_requests) {
        notesHTML += `<p><strong>Special Requests:</strong> ${booking.special_requests}</p>`;
    }

    if (!booking.customer_notes && !booking.admin_notes && !booking.special_requests) {
        notesHTML = '<p class="text-muted">No notes available.</p>';
    }

    return notesHTML;
}

// format date and time
function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// update modal buttons based on status
function updateModalButtons(status) {
    // try to find the modal footer in the current modal structure
    const modalFooter = document.querySelector('#bookingDetailsModal .modal-footer');
    if (!modalFooter) return;

    let buttonsHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';

    if (status === 'pending') {
        buttonsHTML += `
            <button type="button" class="btn btn-success" onclick="updateBookingStatus(currentBookingId, 'approved')">
                <i class="fas fa-check me-1"></i>Approve Booking
            </button>
            <button type="button" class="btn btn-danger" onclick="updateBookingStatus(currentBookingId, 'cancelled')">
                <i class="fas fa-times me-1"></i>Cancel Booking
            </button>
        `;
    } else if (status === 'approved') {
        buttonsHTML += `
            <button type="button" class="btn btn-primary" onclick="updateBookingStatus(currentBookingId, 'completed')">
                <i class="fas fa-check-double me-1"></i>Mark Complete
            </button>
            <button type="button" class="btn btn-warning" onclick="updateBookingStatus(currentBookingId, 'pending')">
                <i class="fas fa-undo me-1"></i>Back to Pending
            </button>
        `;
    }

    modalFooter.innerHTML = buttonsHTML;

    // also update individual button visibility (for the simpler modal structure)
    const approveBtn = document.getElementById('approveBtn');
    const cancelBtn = document.getElementById('cancelBtn');

    if (approveBtn && cancelBtn) {
        if (status === 'pending') {
            approveBtn.style.display = 'inline-block';
            cancelBtn.style.display = 'inline-block';
        } else {
            approveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
        }
    }
}

// add note function
async function addNote() {
    const noteText = document.getElementById('newNote')?.value?.trim();
    if (!noteText) {
        showAlert('warning', 'Please enter a note before adding.');
        return;
    }

    try {
        const response = await fetch('/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/add-note.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                booking_id: currentBookingId,
                note: noteText,
                type: 'admin'
            })
        });

        const result = await response.json();

        if (result.success) {
            // add note to the display
            const notesContainer = document.getElementById('modalNotes');
            if (notesContainer) {
                const newNoteDiv = document.createElement('p');
                newNoteDiv.innerHTML = `<strong>Admin Note:</strong> ${noteText}`;
                notesContainer.appendChild(newNoteDiv);
            }

            // clear the input
            document.getElementById('newNote').value = '';

            showAlert('success', 'Note added successfully!');
        } else {
            showAlert('danger', result.message || 'Failed to add note.');
        }
    } catch (error) {
        console.error('Error adding note:', error);
        showAlert('danger', 'Network error occurred. Please try again.');
    }
}

// print booking function
function printBooking(bookingId) {
    window.open(`/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/print.php?id=${bookingId}`, '_blank');
}

// bulk actions (if you want to add multiple selection)
function initializeBulkActions() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const bookingCheckboxes = document.querySelectorAll('.booking-checkbox');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            bookingCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionButtons();
        });
    }

    bookingCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionButtons);
    });
}

// Update Bulk Action Buttons
function updateBulkActionButtons() {
    const selectedCheckboxes = document.querySelectorAll('.booking-checkbox:checked');
    const bulkActionButtons = document.querySelector('.bulk-actions');

    if (bulkActionButtons) {
        if (selectedCheckboxes.length > 0) {
            bulkActionButtons.style.display = 'block';
        } else {
            bulkActionButtons.style.display = 'none';
        }
    }
}

// Search and Filter Functionality
function initializeSearch() {
    const searchInput = document.getElementById('searchBookings');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.booking-table tbody tr');

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
}

// Export Functionality
function exportBookings(format) {
    const activeTab = document.querySelector('.nav-link.active').getAttribute('data-bs-target');
    let status = '';

    switch (activeTab) {
        case '#pending':
            status = 'pending';
            break;
        case '#approved':
            status = 'approved';
            break;
        case '#history':
            status = 'completed,cancelled';
            break;
    }

    window.open(`/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/export.php?format=${format}&status=${status}`, '_blank');
}

// Print Functionality
function printBookings() {
    window.print();
}

// initialize when page loads
document.addEventListener('DOMContentLoaded', function () {
    initializeBulkActions();
    initializeSearch();
});