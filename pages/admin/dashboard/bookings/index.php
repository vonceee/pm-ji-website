<?php
/**
 * Admin Bookings Tab - Presentation Layer
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/classes/BookingController.php';

// initialize session
$admin_username = $_SESSION['admin_username'];

// initialize controller
$bookingController = new BookingController();

// handle filtering
$filters = [];
if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
}
if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
}
if (isset($_GET['event_type']) && !empty($_GET['event_type'])) {
    $filters['event_type'] = $_GET['event_type'];
}

// initialize booking data
$bookingData = $bookingController->initializeBookingData($filters);

// extract data for easier access in the view
$pendingBookings = $bookingData['pending_bookings'];
$approvedBookings = $bookingData['approved_bookings'];
$historyBookings = $bookingData['history_bookings'];
$counts = $bookingData['counts'];
$stats = $bookingData['stats'];

// check for errors
$hasError = isset($bookingData['error']);
$errorMessage = $hasError ? $bookingData['error'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/index.css">
</head>

<body>
    <div class="container-fluid">
        <!-- Alert Messages -->
        <?php require_once $_SERVER ['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/components/alert-messages.php'; ?>

        <!-- error handling for data loading -->
        <?php if ($hasError): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($errorMessage) ?>
                <button type="button" class="btn btn-outline-light btn-sm float-end" onclick="location.reload()">
                    <i class="fas fa-refresh me-1"></i> Retry
                </button>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="booking-header">
            <h2>Manage Bookings</h2>
            <div class="btn-group">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print Report
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
        </div>

        <!-- Statistics Summary -->
        <?php if (!empty($stats)): ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-clock me-2"></i>Pending
                            </h5>
                            <h3 class="mb-0"><?= $stats['pending'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-check me-2"></i>Approved
                            </h5>
                            <h3 class="mb-0"><?= $stats['approved'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-calendar-check me-2"></i>Completed
                            </h5>
                            <h3 class="mb-0"><?= $stats['completed'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-times me-2"></i>Cancelled
                            </h5>
                            <h3 class="mb-0"><?= $stats['cancelled'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs" id="bookingTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                    type="button" role="tab" aria-controls="pending" aria-selected="true">
                    <i class="fas fa-clock me-2"></i>Pending Bookings
                    <?php if ($counts['pending'] > 0): ?>
                        <span class="badge bg-warning text-dark"><?= $counts['pending'] ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button"
                    role="tab" aria-controls="approved" aria-selected="false">
                    <i class="fas fa-check me-2"></i>Approved Bookings
                    <?php if ($counts['approved'] > 0): ?>
                        <span class="badge bg-success"><?= $counts['approved'] ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button"
                    role="tab" aria-controls="history" aria-selected="false">
                    <i class="fas fa-history me-2"></i>History
                    <?php if ($counts['history'] > 0): ?>
                        <span class="badge bg-secondary"><?= $counts['history'] ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="bookingTabsContent">
            <!-- Pending Bookings Tab -->
            <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                <?php if (empty($pendingBookings)): ?>
                    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/components/empty-state.php'; 
                    renderEmptyState('calendar-check', 'No Pending Bookings', 'All bookings have been processed!'); ?>
                <?php else: ?>
                    <?php 
                    $bookings = $pendingBookings;
                    $bookingType = 'pending';
                    require_once 'components/booking-table.php'; 
                    ?>
                <?php endif; ?>
            </div>

            <!-- Approved Bookings Tab -->
            <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                <?php if (empty($approvedBookings)): ?>
                    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/components/empty-state.php'; 
                    renderEmptyState('calendar-check', 'No Approved Bookings', 'No bookings are currently approved.'); ?>
                <?php else: ?>
                    <?php 
                    $bookings = $approvedBookings;
                    $bookingType = 'approved';
                    require_once 'components/booking-table.php'; 
                    ?>
                <?php endif; ?>
            </div>

            <!-- History Tab -->
            <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                <?php if (empty($historyBookings)): ?>
                    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/components/empty-state.php'; 
                    renderEmptyState('history', 'No Booking History', 'No completed or cancelled bookings found.'); ?>
                <?php else: ?>
                    <?php 
                    $bookings = $historyBookings;
                    $bookingType = 'history';
                    require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/components/history_bookings.php'; require_once 'components/booking-table.php'; 
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Include Modals -->
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/components/booking-details-modal.php'; ?>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/components/cancellation-modal.php'; require_once 'components/filter-modal.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/booking-management.js"></script>

    <script>
        // make booking data available to JavaScript
        window.bookingData = {
            pending: <?= json_encode($pendingBookings) ?>,
            approved: <?= json_encode($approvedBookings) ?>,
            history: <?= json_encode($historyBookings) ?>,
            stats: <?= json_encode($stats) ?>
        };
    </script>

</body>

</html>