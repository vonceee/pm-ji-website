<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/controllers/BookingController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/BookingModel.php';

use controllers\BookingController;

$controller = new BookingController();
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$bookings = $controller->getFilteredBookings($dateFrom, $dateTo);
$pendingBookings = $bookings['pending'];
$approvedBookings = $bookings['approved'];
$historyBookings = $bookings['history'];

$pendingCount = count($pendingBookings);
$approvedCount = count($approvedBookings);
$historyCount = count($historyBookings);
$hasActiveFilters = !empty($dateFrom) || !empty($dateTo);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Bookings</title>
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/bookings-tab.css">
    <link rel="stylesheet"
        href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/modals/booking-details-modal.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>
    <div class="container-fluid">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error_message'];
                unset($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="booking-header">
            <h2>Manage Bookings</h2>
            <div class="btn-group">
                <button class="btn btn-outline-primary" onclick="showPrintReportModal()">
                    <i class="fas fa-print me-1"></i> Print Report
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter me-1"></i> Filter
                    <?php if ($hasActiveFilters): ?>
                        <span class="badge bg-warning text-dark ms-1">Active</span>
                    <?php endif; ?>
                </button>
                <?php if ($hasActiveFilters): ?>
                    <a href="?view=bookings" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Clear Filters
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Active Filters Display -->
        <?php if ($hasActiveFilters): ?>
            <div class="active-filters mb-3">
                <h6 class="mb-2">Active Filters:</h6>
                <div class="filter-tags">
                    <?php if (!empty($dateFrom)): ?>
                        <span class="badge bg-info me-2">
                            From: <?= date('M d, Y', strtotime($dateFrom)) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($dateTo)): ?>
                        <span class="badge bg-info me-2">
                            To: <?= date('M d, Y', strtotime($dateTo)) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs" id="bookingTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                    type="button" role="tab" aria-controls="pending" aria-selected="true">
                    <i class="fas fa-clock me-2"></i>Pending Bookings
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge bg-warning text-dark"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button"
                    role="tab" aria-controls="approved" aria-selected="false">
                    <i class="fas fa-check me-2"></i>Approved Bookings
                    <?php if ($approvedCount > 0): ?>
                        <span class="badge bg-success" style="color: white"><?= $approvedCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button"
                    role="tab" aria-controls="history" aria-selected="false">
                    <i class="fas fa-history me-2"></i>History
                    <?php if ($historyCount > 0): ?>
                        <span class="badge bg-secondary"><?= $historyCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="bookingTabsContent">
            <!-- Pending Bookings Tab -->
            <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                <?php if (empty($pendingBookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-check"></i>
                        <h4><?= $hasActiveFilters ? 'No Pending Bookings Found' : 'No Pending Bookings' ?></h4>
                        <?php if ($hasActiveFilters): ?>
                            <p>Try adjusting your filter criteria or <a href="?view=bookings">clear all filters</a>.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/tabs/pending-bookings.php'; ?>
                <?php endif; ?>
            </div>

            <!-- Approved Bookings Tab -->
            <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                <?php if (empty($approvedBookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-check"></i>
                        <h4><?= $hasActiveFilters ? 'No Approved Bookings Found' : 'No Approved Bookings' ?></h4>
                        <?php if ($hasActiveFilters): ?>
                            <p>Try adjusting your filter criteria or <a href="?view=bookings">clear all filters</a>.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/tabs/approved-bookings.php'; ?>
                <?php endif; ?>
            </div>

            <!-- History Tab -->
            <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                <?php if (empty($historyBookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <h4><?= $hasActiveFilters ? 'No Booking History Found' : 'No Booking History' ?></h4>
                        <?php if ($hasActiveFilters): ?>
                            <p>Try adjusting your filter criteria or <a href="?view=bookings">clear all filters</a>.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/tabs/completed-bookings.php'; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">
                        <i class="fas fa-filter me-2"></i>Filter Bookings
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="GET" id="filterForm">
                    <!-- maintain the view parameter to stay on bookings page -->
                    <input type="hidden" name="view" value="bookings">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from"
                                    value="<?= htmlspecialchars($dateFrom) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to"
                                    value="<?= htmlspecialchars($dateTo) ?>">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <label class="form-label">Quick Date Ranges</label>
                                <div class="btn-group-vertical d-grid gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="setDateRange('today')">
                                        Today
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="setDateRange('this_week')">
                                        This Week
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="setDateRange('this_month')">
                                        This Month
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="setDateRange('last_30_days')">
                                        Last 30 Days
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="?view=bookings" class="btn btn-outline-secondary">Clear All</a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/modals/booking-details-modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/booking-management.js"></script>
    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/date-range-helper.js"></script>
    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/print-report.js"></script>

</body>

</html>