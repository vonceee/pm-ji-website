<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

$admin_username = $_SESSION['admin_username'];

// get PDO connection from your Database class
$pdo = Database::getConnection();

// get filter parameters
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$statusFilter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

// build WHERE clause for date filtering
$dateWhere = '';
$params = [];

if (!empty($dateFrom)) {
    $dateWhere .= " AND b.reservation_date >= :date_from";
    $params['date_from'] = $dateFrom;
}

if (!empty($dateTo)) {
    $dateWhere .= " AND b.reservation_date <= :date_to";
    $params['date_to'] = $dateTo;
}

// fetch pending bookings with user and payment info
$stmtPending = $pdo->prepare("
    SELECT 
        b.*,
        u.first_name,
        u.last_name,
        u.email,
        u.contact_no as phone,
        p.amount_paid,
        p.balance,
        p.payment_method,
        p.payment_type,
        p.status as payment_status,
        p.payment_date
    FROM tbl_bookings b
    LEFT JOIN tbl_users u ON b.user_id = u.id
    LEFT JOIN tbl_payments p ON b.id = p.booking_id
    WHERE b.status = 'pending' $dateWhere
    ORDER BY b.created_at DESC
");

foreach ($params as $key => $value) {
    $stmtPending->bindValue(":$key", $value);
}
$stmtPending->execute();
$pendingBookings = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

// fetch approved bookings with user and payment info
$stmtApproved = $pdo->prepare("
    SELECT 
        b.*,
        u.first_name,
        u.last_name,
        u.email,
        u.contact_no as phone,
        p.amount_paid,
        p.balance,
        p.payment_method,
        p.payment_type,
        p.status as payment_status,
        p.payment_date
    FROM tbl_bookings b
    LEFT JOIN tbl_users u ON b.user_id = u.id
    LEFT JOIN tbl_payments p ON b.id = p.booking_id
    WHERE b.status = 'approved' $dateWhere
    ORDER BY b.reservation_date ASC, b.start_time ASC
");

foreach ($params as $key => $value) {
    $stmtApproved->bindValue(":$key", $value);
}
$stmtApproved->execute();
$approvedBookings = $stmtApproved->fetchAll(PDO::FETCH_ASSOC);

// fetch booking history with user and payment info
$stmtHistory = $pdo->prepare("
    SELECT 
    b.*,
    u.first_name,
    u.last_name,
    u.email,
    u.contact_no AS phone,
    p.amount_paid,
    p.balance,
    p.payment_method,
    p.payment_type,
    p.status AS payment_status,
    p.payment_date,
    p.refund_amount,
    p.refund_date
FROM tbl_bookings b
LEFT JOIN tbl_users u ON b.user_id = u.id
LEFT JOIN tbl_payments p ON b.id = p.booking_id
WHERE b.status NOT IN ('pending', 'approved') $dateWhere
ORDER BY b.updated_at DESC
");

foreach ($params as $key => $value) {
    $stmtHistory->bindValue(":$key", $value);
}
$stmtHistory->execute();
$historyBookings = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

// count bookings for each status
$pendingCount = count($pendingBookings);
$approvedCount = count($approvedBookings);
$historyCount = count($historyBookings);

// Check if filters are active
$hasActiveFilters = !empty($dateFrom) || !empty($dateTo);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Bookings</title>
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/bookings-tab.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings-tab/components/modals/booking-details-modal.css">
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
                <button class="btn btn-outline-primary" onclick="window.print()">
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
                    <!-- Maintain the view parameter to stay on bookings page -->
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
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange('today')">
                                        Today
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange('this_week')">
                                        This Week
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange('this_month')">
                                        This Month
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange('last_30_days')">
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

</body>

</html>