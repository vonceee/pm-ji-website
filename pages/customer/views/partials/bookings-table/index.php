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
        <input type="text" class="form-control" name="search" placeholder="Search bookings..."
            value="<?= htmlspecialchars($currentFilters['search']) ?>">
    </div>

    <!-- Date Filter -->
    <div class="form-group mr-2">
        <input type="date" class="form-control" name="filter_date" 
            value="<?= htmlspecialchars($currentFilters['filter_date']) ?>"
            title="Filter by date">
    </div>

    <!-- Status Filter -->
    <div class="form-group mr-2">
        <select class="form-control" name="filter_status" title="Filter by status">
            <?php foreach ($statusOptions as $value => $label): ?>
                <option value="<?= htmlspecialchars($value) ?>" <?= $currentFilters['filter_status'] === $value ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
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

<!-- Results Summary -->
<?php if (isset($pagination['total'])): ?>
    <div class="results-summary mb-3">
        <small class="text-muted">
            Showing <?= count($bookings) ?> of <?= $pagination['total'] ?> bookings
            <?php if ($currentFilters['search'] || $currentFilters['filter_date'] || $currentFilters['filter_status']): ?>
                (filtered)
            <?php endif; ?>
        </small>
    </div>
<?php endif; ?>

<!-- Main Content -->
<section>
    <?php if (count($bookings) > 0): ?>
        <!-- Bookings Table -->
        <div class="bookings-table-container">
            <div class="table-responsive">
                <table class="bookings-table table table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Event Details</th>
                            <th scope="col">Date & Time</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="actions-column">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $num = 1 + ($pagination['offset'] ?? 0); ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <!-- Row Number -->
                                <th scope="row"><?= $num++ ?></th>
                                
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
                                        <?= htmlspecialchars($booking['formatted_start_time']) ?> - <?= htmlspecialchars($booking['formatted_end_time']) ?>
                                    </small>
                                    <?php if (!empty($booking['time_difference_text'])): ?>
                                        <small class="text-info d-block">
                                            <?= htmlspecialchars($booking['time_difference_text']) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Status -->
                                <td>
                                    <span class="badge badge-<?= htmlspecialchars($booking['status_badge_class']) ?>">
                                        <?= htmlspecialchars($booking['status_display']) ?>
                                    </span>
                                    
                                    <?php if (!empty($booking['is_cancelled']) && !empty($booking['cancellation']['reason'])): ?>
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
                                                    <strong>Cancelled:</strong> <?= htmlspecialchars($booking['cancellation']['formatted_cancelled_at']) ?>
                                                </small>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($booking['cancellation']['refund_status'])): ?>
                                                <small class="text-muted d-block">
                                                    <strong>Refund:</strong> <?= ucfirst(htmlspecialchars($booking['cancellation']['refund_status'])) ?>
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
                                    <button class="btn btn-info btn-sm toggle-details mb-1" 
                                            data-target="#details-<?= $booking['id'] ?>"
                                            title="View booking details">
                                        <i class="fas fa-eye"></i> Details
                                    </button>

                                    <!-- Action Buttons Based on Status -->
                                    <?php if ($booking['status'] === 'cancelled_by_user'): ?>
                                        <a href="/NEW-PM-JI-RESERVIFY/pages/customer/rebook.php?reference_id=<?= urlencode($booking['reference_id']) ?>"
                                            class="btn btn-success btn-sm mb-1"
                                            title="Re-book this event">
                                            <i class="fas fa-redo"></i> Re-book
                                        </a>
                                    <?php elseif ($booking['status'] === 'approved'): ?>
                                        <!-- Reschedule Button for Approved Bookings -->
                                        <button type="button" class="btn btn-warning btn-sm reschedule-booking mb-1"
                                            data-booking-id="<?= $booking['id'] ?>"
                                            data-reference-id="<?= htmlspecialchars($booking['reference_id']) ?>"
                                            data-event-type="<?= htmlspecialchars($booking['event_type']) ?>"
                                            data-current-date="<?= htmlspecialchars($booking['reservation_date']) ?>"
                                            data-current-start-time="<?= htmlspecialchars($booking['start_time']) ?>"
                                            data-current-end-time="<?= htmlspecialchars($booking['end_time']) ?>"
                                            data-duration="<?= htmlspecialchars($booking['duration']) ?>"
                                            title="Reschedule this booking">
                                            <i class="fas fa-calendar-alt"></i> Reschedule
                                        </button>
                                    <?php endif; ?>

                                    <?php if (!empty($booking['can_cancel'])): ?>
                                        <button type="button" class="btn btn-danger btn-sm cancel-booking mb-1"
                                            data-booking-id="<?= $booking['id'] ?>"
                                            data-reference-id="<?= htmlspecialchars($booking['reference_id']) ?>"
                                            data-event-status="<?= htmlspecialchars($booking['status']) ?>"
                                            data-event-type="<?= htmlspecialchars($booking['event_type']) ?>"
                                            data-event-date="<?= htmlspecialchars($booking['reservation_date']) ?>"
                                            data-event-time="<?= htmlspecialchars($booking['formatted_start_time'] . ' - ' . $booking['formatted_end_time']) ?>"
                                            title="Cancel this booking">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    <?php elseif (in_array(strtolower($booking['status']), ['confirmed', 'pending'])): ?>
                                        <button type="button" class="btn btn-secondary btn-sm mb-1" disabled
                                            title="Cannot cancel - less than 24 hours to event">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    <?php endif; ?>

                                    <!-- Hidden Details for Modal -->
                                    <div id="details-<?= $booking['id'] ?>" class="booking-details" style="display:none;"
                                        data-reference-id="<?= htmlspecialchars($booking['reference_id']) ?>"
                                        data-event-type="<?= htmlspecialchars($booking['event_type']) ?>"
                                        data-event-date="<?= htmlspecialchars($booking['reservation_date'] ?? '') ?>"
                                        data-start-time="<?= htmlspecialchars($booking['start_time'] ?? '') ?>"
                                        data-end-time="<?= htmlspecialchars($booking['end_time'] ?? '') ?>"
                                        data-location="<?= htmlspecialchars($booking['full_address'] ?? '') ?>"
                                        data-amount-paid="<?= htmlspecialchars($booking['payment']['amount_paid'] ?? '0') ?>"
                                        data-balance="<?= htmlspecialchars($booking['payment']['balance'] ?? '0') ?>"
                                        data-payment-method="<?= htmlspecialchars($booking['payment']['method'] ?? '') ?>"
                                        data-payment-type="<?= htmlspecialchars($booking['payment']['type'] ?? '') ?>"
                                        data-payment-status="<?= htmlspecialchars($booking['payment']['status'] ?? 'pending') ?>"
                                        data-payment-date="<?= htmlspecialchars($booking['payment']['payment_date'] ?? '') ?>"
                                        data-payment-screenshot="<?= htmlspecialchars($booking['payment']['screenshot_path'] ?? '') ?>"
                                        data-payment-screenshot-thumbnail="<?= htmlspecialchars($booking['payment']['screenshot_thumbnail'] ?? '') ?>"
                                        data-status="<?= htmlspecialchars($booking['status']) ?>"
                                        data-duration="<?= htmlspecialchars($booking['duration'] ?? '') ?>"
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
        <?php if (isset($pagination['total']) && $pagination['total'] > $pagination['limit']): ?>
            <div class="pagination-wrapper">
                <?= Pagination::render(
                    $pagination['total'], 
                    $pagination['limit'], 
                    $pagination['page'],
                    null, // Use current URL
                    [
                        'search' => $currentFilters['search'],
                        'filter_date' => $currentFilters['filter_date'],
                        'filter_status' => $currentFilters['filter_status']
                    ]
                ) ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state text-center py-5">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
            <?php if ($currentFilters['search'] || $currentFilters['filter_date'] || $currentFilters['filter_status']): ?>
                <h4>No bookings match your filters</h4>
                <p class="text-muted">Try adjusting your search criteria or clearing the filters.</p>
                <a href="?page=1" class="btn btn-secondary">
                    <i class="fas fa-undo"></i> Clear Filters
                </a>
            <?php else: ?>
                <h4>No bookings found</h4>
                <p class="text-muted">You haven't made any bookings yet. Make your first booking now!</p>
                <a href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/index.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Make a Booking
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Booking Details Modal -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/views/partials/booking-details-modal/index.php'; ?>

<!-- Cancellation Modal -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/views/partials/cancellation-modal/index.php'; ?>

<!-- Reschedule Modal -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/views/partials/reschedule-modal/index.php'; ?>

<div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;"></div>

<script src="/NEW-PM-JI-RESERVIFY/pages/customer/views/actions/view-booking-details.js"></script>
<script src="/NEW-PM-JI-RESERVIFY/pages/customer/views/actions/cancel-booking.js"></script>
<script src="/NEW-PM-JI-RESERVIFY/pages/customer/views/actions/reschedule-booking.js"></script>