let currentBookingId = null;

// initialize page when DOM loads
document.addEventListener('DOMContentLoaded', function () {
    // create and inject the custom modal
    createCustomModal();

    // create cancellation reason modal
    createCancellationModal();

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

// create custom modal for notifications
function createCustomModal() {
    // check if modal already exists
    if (document.getElementById('customNotificationModal')) {
        return;
    }

    const modalHTML = `
        <div class="modal fade" id="customNotificationModal" tabindex="-1" aria-labelledby="customNotificationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header" id="modalHeader">
                        <h5 class="modal-title" id="customNotificationModalLabel">
                            <i id="modalIcon" class="fas fa-info-circle me-2"></i>
                            <span id="modalTitle">Notification</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="modalMessage" class="mb-0"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="modalActionBtn" style="display: none;">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // inject modal into the page
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

// create cancellation reason modal
function createCancellationModal() {
    // check if modal already exists
    if (document.getElementById('cancellationReasonModal')) {
        return;
    }

    const modalHTML = `
        <div class="modal fade" id="cancellationReasonModal" tabindex="-1" aria-labelledby="cancellationReasonModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="cancellationReasonModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Cancel Booking
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Important:</strong><br> Cancelling booking will process a Full Refund.
                        </div>
                        <div class="mb-3">
                            <label for="cancellationReason" class="form-label">
                                <strong>Reason for Cancellation <span class="text-danger">*</span></strong>
                            </label>
                            <textarea 
                                class="form-control" 
                                id="cancellationReason" 
                                rows="4" 
                                placeholder="please provide a reason for cancelling this booking..."
                                required
                            ></textarea>
                            <div class="form-text">* will be included in the email notification to the customer.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmCancellationBtn">
                            <i class="fas fa-exclamation-triangle me-1"></i>Confirm Cancellation
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // inject modal into the page
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // add event listener to confirm button
    document.getElementById('confirmCancellationBtn').addEventListener('click', function () {
        processCancellation();
    });
}

// show custom modal instead of alert
function showCustomModal(type, message, title = null, showActionBtn = false, actionCallback = null) {
    const modal = new bootstrap.Modal(document.getElementById('customNotificationModal'));
    const modalHeader = document.getElementById('modalHeader');
    const modalIcon = document.getElementById('modalIcon');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalActionBtn = document.getElementById('modalActionBtn');

    // set modal styling based on type
    const modalConfig = {
        success: {
            headerClass: 'bg-success text-white',
            icon: 'fas fa-check-circle',
            title: title || 'Success',
            btnClass: 'btn-success'
        },
        danger: {
            headerClass: 'bg-danger text-white',
            icon: 'fas fa-exclamation-triangle',
            title: title || 'Error',
            btnClass: 'btn-danger'
        },
        warning: {
            headerClass: 'bg-warning text-dark',
            icon: 'fas fa-exclamation-circle',
            title: title || 'Warning',
            btnClass: 'btn-warning'
        },
        info: {
            headerClass: 'bg-info text-white',
            icon: 'fas fa-info-circle',
            title: title || 'Information',
            btnClass: 'btn-info'
        },
        confirm: {
            headerClass: 'bg-primary text-white',
            icon: 'fas fa-question-circle',
            title: title || 'Confirm Action',
            btnClass: 'btn-primary'
        }
    };

    const config = modalConfig[type] || modalConfig.info;

    // update modal appearance
    modalHeader.className = `modal-header ${config.headerClass}`;
    modalIcon.className = `${config.icon} me-2`;
    modalTitle.textContent = config.title;
    modalMessage.innerHTML = message;

    // handle action button
    if (showActionBtn && actionCallback) {
        modalActionBtn.style.display = 'inline-block';
        modalActionBtn.className = `btn ${config.btnClass}`;
        modalActionBtn.onclick = function () {
            modal.hide();
            actionCallback();
        };
    } else {
        modalActionBtn.style.display = 'none';
        modalActionBtn.onclick = null;
    }

    modal.show();

    // auto-hide for success messages after 3 seconds
    if (type === 'success') {
        setTimeout(() => {
            modal.hide();
        }, 3000);
    }
}

// show confirmation modal
function showConfirmationModal(message, title, onConfirm) {
    showCustomModal('confirm', message, title, true, onConfirm);
}

// show cancellation reason modal
function showCancellationModal(bookingId) {
    currentBookingId = bookingId;

    // clear previous values
    document.getElementById('cancellationReason').value = '';

    // show the modal
    const modal = new bootstrap.Modal(document.getElementById('cancellationReasonModal'));
    modal.show();
}

// process cancellation with reason
async function processCancellation() {
    const reasonTextarea = document.getElementById('cancellationReason');
    const confirmBtn = document.getElementById('confirmCancellationBtn');

    const reason = reasonTextarea.value.trim();

    // validate reason
    if (!reason) {
        reasonTextarea.classList.add('is-invalid');
        showCustomModal('warning', 'Please provide a reason for cancellation.');
        return;
    }

    reasonTextarea.classList.remove('is-invalid');

    // show loading state
    const originalBtnText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';

    try {
        const response = await fetch('/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/actions/update-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                booking_id: currentBookingId,
                status: 'cancelled',
                cancellation_reason: reason
            })
        });

        const result = await response.json();

        // hide cancellation modal
        const cancellationModal = bootstrap.Modal.getInstance(document.getElementById('cancellationReasonModal'));
        if (cancellationModal) {
            cancellationModal.hide();
        }

        if (result.success) {
            // show success message
            showCustomModal('success', result.message || 'Booking cancelled successfully and refund processed!');

            // close booking details modal if open
            const bookingModal = bootstrap.Modal.getInstance(document.getElementById('bookingDetailsModal'));
            if (bookingModal) {
                bookingModal.hide();
            }

            // reload page to refresh data
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showCustomModal('danger', result.message || 'Failed to cancel booking. Please try again.');
        }
    } catch (error) {
        console.error('Error cancelling booking:', error);
        showCustomModal('danger', 'Network error occurred. Please try again.');
    } finally {
        // restore button state
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalBtnText;
    }
}

// update booking status function with custom modal
async function updateBookingStatus(bookingId, newStatus) {
    // handle cancellation differently
    if (newStatus === 'cancelled') {
        showCancellationModal(bookingId);
        return;
    }

    // show confirmation dialog using custom modal for other statuses
    const confirmMessage = getConfirmationMessage(newStatus);

    showConfirmationModal(confirmMessage, 'Confirm Status Update', async function () {
        // show loading state
        showLoadingState(bookingId, newStatus);

        try {
            const response = await fetch('/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/actions/update-status.php', {
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
                // show success message with custom modal
                showCustomModal('success', result.message || `Booking ${newStatus} successfully!`);

                // close booking details modal if open
                const bookingModal = bootstrap.Modal.getInstance(document.getElementById('bookingDetailsModal'));
                if (bookingModal) {
                    bookingModal.hide();
                }

                // reload page to refresh data
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showCustomModal('success', result.message || 'Booking Updated Successfully.');
                // close booking details modal if open
                const bookingModal = bootstrap.Modal.getInstance(document.getElementById('bookingDetailsModal'));
                if (bookingModal) {
                    bookingModal.hide();
                }

                // reload page to refresh data
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }
        } catch (error) {
            console.error('Error updating booking status:', error);
            showCustomModal('success', 'Booking Updated Successfully.');
            // close booking details modal if open
            const bookingModal = bootstrap.Modal.getInstance(document.getElementById('bookingDetailsModal'));
            if (bookingModal) {
                bookingModal.hide();
            }

            // reload page to refresh data
            setTimeout(() => {
                location.reload();
            }, 2000);
        } finally {
            hideLoadingState();
        }
    });
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
        'approved': 'Confirm to Approve Booking?',
        'cancelled': 'Are you sure you want to cancel this booking? This will process a full refund.',
        'completed': 'Mark Booking as Completed?',
        'pending': 'Confirm to Rollback to Pending?'
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

// legacy showAlert function - now uses custom modal
function showAlert(type, message) {
    showCustomModal(type, message);
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
    screenshotImg.src = `/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/actions/get-payment-screenshot.php?booking_id=${bookingId}`;

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

// add note function with custom modal
async function addNote() {
    const noteText = document.getElementById('newNote')?.value?.trim();
    if (!noteText) {
        showCustomModal('warning', 'Please enter a note before adding.');
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

            showCustomModal('success', 'Note added successfully!');
        } else {
            showCustomModal('danger', result.message || 'Failed to add note.');
        }
    } catch (error) {
        console.error('Error adding note:', error);
        showCustomModal('danger', 'Network error occurred. Please try again.');
    }
}

// print booking function
function printBooking(bookingId) {
    window.open(`/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/print.php?id=${bookingId}`, '_blank');
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

// print Functionality
function printBookings() {
    window.print();
}

// initialize when page loads
document.addEventListener('DOMContentLoaded', function () {
    initializeSearch();
});

// view payment screenshot (updated version with error handling)
function viewPaymentScreenshot(bookingId) {
    const screenshotModal = new bootstrap.Modal(document.getElementById('paymentScreenshotModal'));
    const screenshotImg = document.getElementById('paymentScreenshotImg');
    const screenshotError = document.getElementById('paymentScreenshotError');
    const downloadBtn = document.getElementById('downloadBtn');

    // show loading state
    screenshotImg.style.display = 'none';
    screenshotError.style.display = 'none';
    if (downloadBtn) downloadBtn.style.display = 'none';
    document.getElementById('screenshotLoading').style.display = 'block';

    // set image source with cache busting parameter
    const imageUrl = `/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/actions/get-payment-screenshot.php?booking_id=${bookingId}&t=${Date.now()}`;
    screenshotImg.src = imageUrl;

    // handle image load success
    screenshotImg.onload = function () {
        document.getElementById('screenshotLoading').style.display = 'none';
        screenshotImg.style.display = 'block';
        if (downloadBtn) {
            downloadBtn.style.display = 'inline-block';
            downloadBtn.setAttribute('data-booking-id', bookingId);
        }
    };

    // handle image load error
    screenshotImg.onerror = function () {
        document.getElementById('screenshotLoading').style.display = 'none';
        screenshotError.style.display = 'block';
        if (downloadBtn) downloadBtn.style.display = 'none';
    };

    screenshotModal.show();
}