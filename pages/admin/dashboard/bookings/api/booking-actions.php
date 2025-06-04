<?php
/**
 * API Handler for booking actions
 * handles AJAX requests for booking management
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Include required files
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/BookingController.php';

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    if (!isset($input['action']) || !isset($input['booking_id'])) {
        throw new Exception('Missing required fields: action and booking_id');
    }

    $action = $input['action'];
    $bookingId = (int) $input['booking_id'];
    $additionalData = $input['data'] ?? [];

    // Validate booking ID
    if ($bookingId <= 0) {
        throw new Exception('Invalid booking ID');
    }

    // Validate action
    $allowedActions = ['approve', 'reject', 'complete', 'cancel'];
    if (!in_array($action, $allowedActions)) {
        throw new Exception('Invalid action specified');
    }

    // Initialize controller and process action
    $controller = new BookingController();
    $result = $controller->processBookingAction($bookingId, $action, $additionalData);

    // Set success message in session if action was successful
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

<?php
/**
 * API Handler for fetching booking details
 * api/booking-details.php
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/BookingService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $bookingId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($bookingId <= 0) {
        throw new Exception('Invalid booking ID');
    }

    $bookingService = new BookingService();
    $booking = $bookingService->getBookingById($bookingId);

    if (!$booking) {
        throw new Exception('Booking not found');
    }

    echo json_encode([
        'success' => true,
        'data' => $booking
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

<?php
/**
 * Enhanced JavaScript for booking management
 * assets/js/enhanced-booking-management.js
 */
?>

<script>
    class BookingManager {
        constructor() {
            this.baseUrl = '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/api/';
            this.init();
        }

        init() {
            // Initialize event listeners
            this.bindEvents();

            // Initialize tooltips
            this.initTooltips();

            // Auto-refresh pending bookings every 30 seconds
            this.startAutoRefresh();
        }

        bindEvents() {
            // Handle tab switching
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', (e) => {
                    this.onTabChange(e.target.getAttribute('data-bs-target'));
                });
            });
        }

        initTooltips() {
            // Initialize Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        startAutoRefresh() {
            // Refresh pending bookings every 30 seconds
            setInterval(() => {
                if (document.querySelector('#pending-tab').classList.contains('active')) {
                    this.refreshPendingBookings();
                }
            }, 30000);
        }

        onTabChange(targetTab) {
            // Handle tab-specific logic
            console.log('Switched to tab:', targetTab);
        }

        async approveBooking(bookingId) {
            if (!confirm('Are you sure you want to approve this booking?')) {
                return;
            }

            try {
                const result = await this.performBookingAction('approve', bookingId);
                if (result.success) {
                    this.showSuccess(result.message);
                    this.refreshCurrentTab();
                } else {
                    this.showError(result.message);
                }
            } catch (error) {
                this.showError('Failed to approve booking');
                console.error('Error approving booking:', error);
            }
        }

        async rejectBooking(bookingId) {
            if (!confirm('Are you sure you want to reject this booking?')) {
                return;
            }

            try {
                const result = await this.performBookingAction('reject', bookingId);
                if (result.success) {
                    this.showSuccess(result.message);
                    this.refreshCurrentTab();
                } else {
                    this.showError(result.message);
                }
            } catch (error) {
                this.showError('Failed to reject booking');
                console.error('Error rejecting booking:', error);
            }
        }

        async completeBooking(bookingId) {
            if (!confirm('Are you sure you want to mark this booking as complete?')) {
                return;
            }

            try {
                const result = await this.performBookingAction('complete', bookingId);
                if (result.success) {
                    this.showSuccess(result.message);
                    this.refreshCurrentTab();
                } else {
                    this.showError(result.message);
                }
            } catch (error) {
                this.showError('Failed to complete booking');
                console.error('Error completing booking:', error);
            }
        }

        async cancelBooking(bookingId) {
            if (!confirm('Are you sure you want to cancel this booking?')) {
                return;
            }

            try {
                const result = await this.performBookingAction('cancel', bookingId);
                if (result.success) {
                    this.showSuccess(result.message);
                    this.refreshCurrentTab();
                } else {
                    this.showError(result.message);
                }
            } catch (error) {
                this.showError('Failed to cancel booking');
                console.error('Error cancelling booking:', error);
            }
        }

        async performBookingAction(action, bookingId, additionalData = {}) {
            const response = await fetch(this.baseUrl + 'booking-actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: action,
                    booking_id: bookingId,
                    data: additionalData
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        }

        async viewBookingDetails(booking) {
            try {
                // Populate modal with booking details
                this.populateBookingModal(booking);

                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
                modal.show();
            } catch (error) {
                this.showError('Failed to load booking details');
                console.error('Error loading booking details:', error);
            }
        }

        populateBookingModal(booking) {
            // Populate modal fields with booking data
            document.getElementById('modal-reference-id').textContent = booking.reference_id;
            document.getElementById('modal-customer-name').textContent = `${booking.first_name} ${booking.last_name}`;
            document.getElementById('modal-customer-email').textContent = booking.email;
            document.getElementById('modal-customer-phone').textContent = booking.phone || 'N/A';
            document.getElementById('modal-event-type').textContent = booking.event_type;
            document.getElementById('modal-reservation-date').textContent = new Date(booking.reservation_date).toLocaleDateString();
            document.getElementById('modal-time-range').textContent = `${booking.start_time} - ${booking.end_time}`;
            document.getElementById('modal-duration').textContent = `${booking.duration} hours`;
            document.getElementById('modal-location').textContent = booking.city;
            document.getElementById('modal-status').textContent = booking.status;
            document.getElementById('modal-payment-status').textContent = booking.payment_status || 'Unpaid';
            document.getElementById('modal-amount-paid').textContent = `₱${parseFloat(booking.amount_paid || 0).toFixed(2)}`;

            if (booking.balance) {
                document.getElementById('modal-balance').textContent = `₱${parseFloat(booking.balance).toFixed(2)}`;
            }
        }

        printBooking(bookingId) {
            // Open print window for specific booking
            window.open(`${this.baseUrl}print-booking.php?id=${bookingId}`, '_blank');
        }

        refreshCurrentTab() {
            // Refresh the currently active tab
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        refreshPendingBookings() {
            // Silently refresh pending bookings count
            // This could be implemented as an AJAX call to update just the numbers
            console.log('Refreshing pending bookings...');
        }

        showSuccess(message) {
            this.showAlert(message, 'success');
        }

        showError(message) {
            this.showAlert(message, 'danger');
        }

        showAlert(message, type) {
            const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

            const alertContainer = document.createElement('div');
            alertContainer.innerHTML = alertHtml;

            const container = document.querySelector('.container-fluid');
            container.insertBefore(alertContainer.firstElementChild, container.firstElementChild);

            // Auto-remove alert after 5 seconds
            setTimeout(() => {
                const alert = container.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
    }

    // Global functions for backward compatibility
    let bookingManager;

    document.addEventListener('DOMContentLoaded', function () {
        bookingManager = new BookingManager();
    });

    // Expose functions globally
    window.approveBooking = (id) => bookingManager.approveBooking(id);
    window.rejectBooking = (id) => bookingManager.rejectBooking(id);
    window.completeBooking = (id) => bookingManager.completeBooking(id);
    window.cancelBooking = (id) => bookingManager.cancelBooking(id);
    window.viewBookingDetails = (booking) => bookingManager.viewBookingDetails(booking);
    window.printBooking = (id) => bookingManager.printBooking(id);
</script>