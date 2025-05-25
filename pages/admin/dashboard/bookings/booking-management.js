// Global variables
let currentBookingId = null;

// Initialize page when DOM loads
document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function () {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
                alert.classList.add('fade');
            }
        });
    }, 5000);

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Update booking status function
async function updateBookingStatus(bookingId, newStatus) {
    // Show confirmation dialog
    const confirmMessage = getConfirmationMessage(newStatus);
    if (!confirm(confirmMessage)) {
        return;
    }

    // Show loading state
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
            // Show success message
            showAlert('success', result.message || `Booking ${newStatus} successfully!`);

            // Close modal if open
            const modal = bootstrap.Modal.getInstance(document.getElementById('bookingDetailsModal'));
            if (modal) {
                modal.hide();
            }

            // Reload page to refresh data
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

// Get confirmation message based on status
function getConfirmationMessage(status) {
    const messages = {
        'approved': 'Are you sure you want to approve this booking? The customer will be notified via email.',
        'cancelled': 'Are you sure you want to cancel this booking? This action cannot be undone and the customer will be notified.',
        'completed': 'Mark this booking as completed? This indicates the event has finished successfully.',
        'pending': 'Move this booking back to pending status?'
    };
    return messages[status] || 'Are you sure you want to update this booking?';
}

// Show loading state
function showLoadingState(bookingId, status) {
    const buttons = document.querySelectorAll(`[onclick*="${bookingId}"]`);
    buttons.forEach(button => {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    });
}

// Hide loading state
function hideLoadingState() {
    const buttons = document.querySelectorAll('button[disabled]');
    buttons.forEach(button => {
        button.disabled = false;
        // Reset button content based on its class
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

// Show alert message
function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());

    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    // Insert at top of container
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.classList.remove('show');
            setTimeout(() => {
                alertDiv.remove();
            }, 150);
        }
    }, 5000);
}

// View booking details
function viewBookingDetails(booking) {
    currentBookingId = booking.id;

    // Populate basic information
    document.getElementById('modalReferenceId').textContent = `#${booking.reference_id}`;
    document.getElementById('modalRefId').textContent = `#${booking.reference_id}`;
    document.getElementById('modalDate').textContent = formatDate(booking.reservation_date);
    document.getElementById('modalTime').textContent = `${booking.start_time} - ${booking.end_time}`;
    document.getElementById('modalDuration').textContent = `${booking.duration} hours`;
    document.getElementById('modalEvent').textContent = booking.event_type;

    // Status with badge
    const statusSpan = document.getElementById('modalStatus');
    statusSpan.textContent = booking.status.charAt(0).toUpperCase() + booking.status.slice(1);
    statusSpan.className = `status-badge status-${booking.status.toLowerCase()}`;

    // Customer information
    document.getElementById('modalCustomerName').textContent = `${booking.first_name} ${booking.last_name}`;
    document.getElementById('modalCustomerEmail').textContent = booking.email || 'N/A';
    document.getElementById('modalCustomerPhone').textContent = booking.phone || 'N/A';

    // Payment information
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
    document.getElementById('modalTotal').textContent = `₱${total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
    document.getElementById('modalPaymentDate').textContent = booking.payment_date ? formatDate(booking.payment_date) : 'N/A';

    // Event details (you might need to add these fields to your database)
    document.getElementById('modalVenue').textContent = booking.full_address || `${booking.city}, ${booking.barangay}`;
    document.getElementById('modalGuests').textContent = booking.guests || 'Not specified';
    document.getElementById('modalPackage').textContent = booking.package || 'Standard Package';
    document.getElementById('modalRequests').textContent = booking.special_requests || 'None';

    // Timeline (basic implementation)
    const timeline = document.getElementById('modalTimeline');
    timeline.innerHTML = generateTimeline(booking);

    // Notes
    const notes = document.getElementById('modalNotes');
    notes.innerHTML = generateNotes(booking);

    // Clear new note field
    document.getElementById('newNote').value = '';

    // Update modal footer buttons based on current status
    updateModalButtons(booking.status);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
    modal.show();
}

// Format date helper
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Generate timeline HTML
function generateTimeline(booking) {
    let timelineHTML = '';

    // Booking created
    timelineHTML += `
        <div class="timeline-item">
            <small class="text-muted">${formatDateTime(booking.created_at)}</small><br>
            <strong>Booking Created</strong><br>
            Customer submitted booking request
        </div>
    `;

    // Payment received (if exists)
    if (booking.payment_date) {
        timelineHTML += `
            <div class="timeline-item">
                <small class="text-muted">${formatDateTime(booking.payment_date)}</small><br>
                <strong>Payment Received</strong><br>
                ${booking.payment_type} payment of ₱${parseFloat(booking.amount_paid || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}
            </div>
        `;
    }

    // Current status
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

// Generate notes HTML
function generateNotes(booking) {
    let notesHTML = '';

    // You can expand this to show actual notes from database
    if (booking.customer_notes) {
        notesHTML += `<p><strong>Customer Note:</strong> ${booking.customer_notes}</p>`;
    }

    if (booking.admin_notes) {
        notesHTML += `<p><strong>Admin Note:</strong> ${booking.admin_notes}</p>`;
    }

    if (!booking.customer_notes && !booking.admin_notes) {
        notesHTML = '<p class="text-muted">No notes available.</p>';
    }

    return notesHTML;
}

// Format date and time
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

// Update modal buttons based on status
function updateModalButtons(status) {
    const modalFooter = document.querySelector('#bookingDetailsModal .modal-footer');
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
}

// Add note function
async function addNote() {
    const noteText = document.getElementById('newNote').value.trim();
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
            // Add note to the display
            const notesContainer = document.getElementById('modalNotes');
            const newNoteDiv = document.createElement('p');
            newNoteDiv.innerHTML = `<strong>Admin Note:</strong> ${noteText}`;
            notesContainer.appendChild(newNoteDiv);

            // Clear the input
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

// Bulk actions (if you want to add multiple selection)
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

// Update bulk action buttons
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

// Search and filter functionality
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

// Export functionality
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

// Print functionality
function printBookings() {
    window.print();
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function () {
    initializeBulkActions();
    initializeSearch();
});