<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/controllers/BookingController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/BookingModel.php';

use controllers\BookingController;

$controller = new BookingController();

// store; filter values from URL parameters (for date-filtering, in Displayed Bookings)
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// track; current tab, page number (for pagination)
$tab = $_GET['tab'] ?? 'pending';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// set; the current page for each tab, only the active tab uses the actual page number
$pendingPage = ($tab === 'pending') ? $page : 1;
$approvedPage = ($tab === 'approved') ? $page : 1;
$historyPage = ($tab === 'completed') ? $page : 1;

// determine; which tab is currently active and set the current page accordingly
$activeTab = $_GET['tab'] ?? 'pending';
$currentPage = $activeTab === 'approved' ? $approvedPage : ($activeTab === 'history' ? $historyPage : $pendingPage);

// fetch; bookings, counts for each tab/status (using BookingController)
$bookings = $controller->getFilteredBookings($dateFrom, $dateTo, $currentPage);
$counts = $controller->getBookingsCounts($dateFrom, $dateTo);

// store; bookings, counts for each tab/status
$pendingBookings = $bookings['pending'];
$approvedBookings = $bookings['approved'];
$historyBookings = $bookings['history'];

$pendingCount = $counts['pending'];
$approvedCount = $counts['approved'];
$historyCount = $counts['history'];

// generate; pagination data for each tab/status (using BookingController)
$pendingPagination = $controller->getPaginationData($pendingCount, $pendingPage);
$approvedPagination = $controller->getPaginationData($approvedCount, $approvedPage);
$historyPagination = $controller->getPaginationData($historyCount, $historyPage);

// track; any filters currently active
$hasActiveFilters = !empty($dateFrom) || !empty($dateTo);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Bookings</title>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/bookings-tab.css">
    <link rel="stylesheet"
        href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/modals/booking-details-modal/booking-details-modal.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/src/utils/pagination/pagination.css">
    <!-- Bootstrap JS -->
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

        <!-- Filter Tags -->
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

        <!-- Navigation Tabs (Pending, Approved, and History) -->
        <ul class="nav nav-tabs" id="bookingTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'pending' ? 'active' : '' ?>" 
                        id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                        type="button" role="tab" aria-controls="pending" 
                        aria-selected="<?= $activeTab === 'pending' ? 'true' : 'false' ?>"
                        onclick="setActiveTab('pending')">
                    <i class="fas fa-clock me-2"></i>Pending Bookings
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge bg-warning text-dark"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'approved' ? 'active' : '' ?>" 
                        id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" 
                        type="button" role="tab" aria-controls="approved" 
                        aria-selected="<?= $activeTab === 'approved' ? 'true' : 'false' ?>"
                        onclick="setActiveTab('approved')">
                    <i class="fas fa-check me-2"></i>Approved Bookings
                    <?php if ($approvedCount > 0): ?>
                        <span class="badge bg-success" style="color: white"><?= $approvedCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'history' ? 'active' : '' ?>" 
                        id="history-tab" data-bs-toggle="tab" data-bs-target="#history" 
                        type="button" role="tab" aria-controls="history" 
                        aria-selected="<?= $activeTab === 'history' ? 'true' : 'false' ?>"
                        onclick="setActiveTab('history')">
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
            <div class="tab-pane fade <?= $activeTab === 'pending' ? 'show active' : '' ?>" 
                 id="pending" role="tabpanel" aria-labelledby="pending-tab">
                <?php if (empty($pendingBookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-check"></i>
                        <h4><?= $hasActiveFilters ? 'No Pending Bookings Found' : 'No Pending Bookings' ?></h4>
                        <?php if ($hasActiveFilters): ?>
                            <p>Try adjusting your filter criteria or <a href="?view=bookings">clear all filters</a>.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Pending Bookings Table -->
                    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/tabs/pending-bookings.php'; ?>
                    
                    <!-- Pagination -->
                    <?php if ($activeTab === 'pending'): ?>
                        <?php 
                        $paginationData = $pendingPagination;
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/src/utils/pagination/index.php'; 
                        ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Approved Bookings Tab -->
            <div class="tab-pane fade <?= $activeTab === 'approved' ? 'show active' : '' ?>" 
                 id="approved" role="tabpanel" aria-labelledby="approved-tab">
                <?php if (empty($approvedBookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-check"></i>
                        <h4><?= $hasActiveFilters ? 'No Approved Bookings Found' : 'No Approved Bookings' ?></h4>
                        <?php if ($hasActiveFilters): ?>
                            <p>Try adjusting your filter criteria or <a href="?view=bookings">clear all filters</a>.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Approved Bookings Table -->
                    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/tabs/approved-bookings.php'; ?>
                    
                    <!-- Pagination -->
                    <?php if ($activeTab === 'approved'): ?>
                        <?php 
                        $paginationData = $approvedPagination;
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/src/utils/pagination/index.php'; 
                        ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- History Tab -->
            <div class="tab-pane fade <?= $activeTab === 'history' ? 'show active' : '' ?>" 
                 id="history" role="tabpanel" aria-labelledby="history-tab">
                <?php if (empty($historyBookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <h4><?= $hasActiveFilters ? 'No Booking History Found' : 'No Booking History' ?></h4>
                        <?php if ($hasActiveFilters): ?>
                            <p>Try adjusting your filter criteria or <a href="?view=bookings">clear all filters</a>.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Completed Bookings Table -->
                    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/tabs/completed-bookings.php'; ?>
                    
                    <!-- Pagination -->
                    <?php if ($activeTab === 'history'): ?>
                        <?php 
                        $paginationData = $historyPagination;
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/src/utils/pagination/index.php'; 
                        ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/modals/filter-modal/index.php'; ?>
    </div>

    <!-- Booking Details Modal -->
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/modals/booking-details-modal/index.php'; ?>

    <!-- scripts for tab, pagination, date-filtering, print functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/booking-management.js"></script>
    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/date-range-helper.js"></script>
    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/print-report.js"></script>
    <script src="/NEW-PM-JI-RESERVIFY/src/utils/pagination/pagination.js"></script>

</body>

</html>