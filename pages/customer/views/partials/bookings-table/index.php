
<?php
// pages/customer/views/partials/bookings-table/index.php

// Session and authentication checks
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_email'])) {
    header('Location: /NEW-PM-JI-RESERVIFY/public/index.php');
    exit;
}

// Include dependencies
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/src/controllers/CustomerBookingController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/src/utils/pagination/index.php';

use Controllers\CustomerBookingController;
use Utils\Pagination;

// Initialize controller
$controller = new CustomerBookingController();

// Get request parameters
$filters = [
    'page' => isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1,
    'limit' => 3,
    'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
    'filter_date' => isset($_GET['filter_date']) ? trim($_GET['filter_date']) : '',
    'filter_status' => isset($_GET['filter_status']) ? trim($_GET['filter_status']) : ''
];

// Get data from controller
$result = $controller->getUserBookings($_SESSION['user_email'], $filters);

if (!$result['success']) {
    die('Error: ' . $result['error']);
}

$data = $result['data'];
$bookings = $data['bookings'];
$pagination = $data['pagination'];
$currentFilters = $data['filters'];
$statusOptions = $controller->getStatusOptions();
?>

<!-- Filter Form -->
<form class="bookings-filter-form form-inline mb-3" method="get" action="">
    <input type="hidden" name="page" value="1">

    <!-- Search Input -->
    <div class="form-group mr-2">
        <input type="text" class="form-control" name="search" placeholder="Search"
            value="<?= htmlspecialchars($currentFilters['search']) ?>">
    </div>

    <!-- Date Filter -->
    <div class="form-group mr-2">
        <input type="date" class="form-control" name="filter_date" 
            value="<?= htmlspecialchars($currentFilters['filter_date']) ?>">
    </div>

    <!-- Status Filter -->
    <div class="form-group mr-2">
        <select class="form-control" name="filter_status">
            <?php foreach ($statusOptions as $value => $label): ?>
                <option value="<?= $value ?>" <?= $currentFilters['filter_status'] === $value ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Submit & Reset -->
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-search"></i> Filter
    </button>
    <?php if ($currentFilters['search'] || $currentFilters['filter_date'] || $currentFilters['filter_status']): ?>
        <a href="?page=1" class="btn btn-secondary ml-2">
            <i class="fas fa-undo"></i> Reset
        </a>
    <?php endif; ?>
</form>

<!-- Main Content -->
<section>
    <?php if (count($bookings) > 0): ?>
        <!-- Bookings Table -->
        <div class="bookings-table-container">
            <div class="table-responsive">
                <table class="bookings-table table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Event Details</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th class="actions-column">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $num = 1 + $pagination['offset']; ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <!-- Row Number -->
                                <td><?= $num++ ?></td>
                                
                                <!-- Event Details -->
                                <td class="event-info">
                                    <strong><?= htmlspecialchars($booking['event_type']) ?></strong>
                                    <small class="text-muted d-block">
                                        <?= htmlspecialchars($booking['reference_id']) ?>
                                    </small>
                                </td>
                                
                                <!-- Date & Time -->
                                <td class="datetime-info">
                                    <strong><?= htmlspecialchars($booking['formatted_date']) ?></strong>
                                    <small class="text-muted d-block">
                                        <?= $booking['formatted_start_time'] ?> - <?= $booking['formatted_end_time'] ?>
                                    </small>
                                    <small class="text-info d-block">
                                        <?= $booking['time_difference_text'] ?>
                                    </small>
                                </td>
                                
                                <!-- Status -->
                                <td>
                                    <span class="badge badge-<?= $booking['status_badge_class'] ?>">
                                        <?= $booking['status_display'] ?>
                                    </span>
                                    
                                    <?php if ($booking['is_cancelled'] && !empty($booking['cancellation']['reason'])): ?>
                                        <!-- Cancellation Details -->
                                        <div class="cancellation-details mt-2">
                                            <?php if (!empty($booking['cancellation']['reason'])): ?>
                                                <small class="text-muted d-block">
                                                    <strong>Reason:</strong> <?= htmlspecialchars($booking['cancellation']['reason']) ?>
                                                </small>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($booking['cancellation']['admin_notes'])): ?>
                                                <small class="text-muted d-block">
                                                    <strong>Admin Notes:</strong> <?= htmlspecialchars($booking['cancellation']['admin_notes']) ?>
                                                </small>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($booking['cancellation']['formatted_cancelled_at'])): ?>
                                                <small class="text-muted d-block">
                                                    <strong>Cancelled:</strong> <?= $booking['cancellation']['formatted_cancelled_at'] ?>
                                                </small>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($booking['cancellation']['refund_status'])): ?>
                                                <small class="text-muted d-block">
                                                    <strong>Refund:</strong> <?= ucfirst($booking['cancellation']['refund_status']) ?>
                                                    <?php if (!empty($booking['cancellation']['refund_amount']) && $booking['cancellation']['refund_amount'] > 0): ?>
                                                        (₱<?= number_format($booking['cancellation']['refund_amount'], 2) ?>)
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Actions -->
                                <td class="actions-column">
                                    <!-- View Details Button -->
                                    <button class="btn btn-info btn-sm toggle-details" data-target="#details-<?= $booking['id'] ?>">
                                        <i class="fas fa-eye"></i> Details
                                    </button>

                                    <!-- Action Buttons Based on Status -->
                                    <?php if ($booking['status'] === 'cancelled_by_user'): ?>
                                        <a href="/NEW-PM-JI-RESERVIFY/pages/customer/rebook.php?reference_id=<?= htmlspecialchars($booking['reference_id']) ?>"
                                            class="btn btn-success btn-sm">
                                            <i class="fas fa-redo"></i> Re-book
                                        </a>
                                    <?php elseif ($booking['can_cancel']): ?>
                                        <button type="button" class="btn btn-danger btn-sm cancel-booking"
                                            data-booking-id="<?= $booking['id'] ?>"
                                            data-reference-id="<?= htmlspecialchars($booking['reference_id']) ?>"
                                            data-event-status="<?= htmlspecialchars($booking['status']) ?>"
                                            data-event-type="<?= htmlspecialchars($booking['event_type']) ?>"
                                            data-event-date="<?= htmlspecialchars($booking['reservation_date']) ?>"
                                            data-event-time="<?= $booking['formatted_start_time'] . ' - ' . $booking['formatted_end_time'] ?>">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    <?php elseif (in_array(strtolower($booking['status']), ['confirmed', 'pending'])): ?>
                                        <button type="button" class="btn btn-secondary btn-sm" disabled
                                            title="Cannot cancel - less than 24 hours to event">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    <?php endif; ?>

                                    <!-- Hidden Details for Modal -->
                                    <div id="details-<?= $booking['id'] ?>" class="booking-details" style="display:none;"
                                        data-reference-id="<?= htmlspecialchars($booking['reference_id']) ?>"
                                        data-event-type="<?= htmlspecialchars($booking['event_type']) ?>"
                                        data-event-date="<?= htmlspecialchars($booking['reservation_date']) ?>"
                                        data-start-time="<?= htmlspecialchars($booking['start_time']) ?>"
                                        data-end-time="<?= htmlspecialchars($booking['end_time']) ?>"
                                        data-location="<?= htmlspecialchars($booking['full_address']) ?>"
                                        data-amount-paid="<?= htmlspecialchars($booking['payment']['amount_paid'] ?? '0') ?>"
                                        data-balance="<?= htmlspecialchars($booking['payment']['balance'] ?? '0') ?>"
                                        data-payment-method="<?= htmlspecialchars($booking['payment']['method'] ?? '') ?>"
                                        data-payment-type="<?= htmlspecialchars($booking['payment']['type'] ?? '') ?>"
                                        data-payment-status="<?= htmlspecialchars($booking['payment']['status'] ?? 'pending') ?>"
                                        data-payment-date="<?= htmlspecialchars($booking['payment']['payment_date'] ?? '') ?>"
                                        data-payment-screenshot="<?= htmlspecialchars($booking['payment']['screenshot_path'] ?? '') ?>"
                                        data-payment-screenshot-thumbnail="<?= htmlspecialchars($booking['payment']['screenshot_thumbnail'] ?? '') ?>"
                                        data-status="<?= htmlspecialchars($booking['status']) ?>"
                                        data-duration="<?= htmlspecialchars($booking['duration']) ?>"
                                        data-cancellation-reason="<?= htmlspecialchars($booking['cancellation']['reason'] ?? '') ?>"
                                        data-cancelled-at="<?= htmlspecialchars($booking['cancellation']['cancelled_at'] ?? '') ?>"
                                        data-refund-status="<?= htmlspecialchars($booking['cancellation']['refund_status'] ?? '') ?>"
                                        data-refund-amount="<?= htmlspecialchars($booking['cancellation']['refund_amount'] ?? '0') ?>"
                                        data-cancellation-admin-notes="<?= htmlspecialchars($booking['cancellation']['admin_notes'] ?? '') ?>">
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <?= Pagination::render($pagination['total'], $pagination['limit'], $pagination['page'], basename($_SERVER['PHP_SELF'])) ?>
        <?php endif; ?>

    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <i class="fas fa-calendar-times fa-3x"></i>
            <p>No bookings found. Make a booking now!</p>
            <a href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/index.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Make a Booking
            </a>
        </div>
    <?php endif; ?>
</section>

<!-- Booking Details Modal -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/views/partials/booking-details-modal/index.php'; ?>

<!-- Cancellation Modal -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/views/partials/cancellation-modal/index.php'; ?>

<div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;"></div>

<script src="/NEW-PM-JI-RESERVIFY/pages/customer/views/actions/view-booking-details.js"></script>
<script src="/NEW-PM-JI-RESERVIFY/pages/customer/views/actions/cancel-booking.js"></script>