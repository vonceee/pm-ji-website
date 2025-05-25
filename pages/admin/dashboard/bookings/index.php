<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

$admin_username = $_SESSION['admin_username'];

// get PDO connection from your Database class
$pdo = Database::getConnection();

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
    WHERE b.status = 'pending'
    ORDER BY b.created_at DESC
");
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
    WHERE b.status = 'approved'
    ORDER BY b.reservation_date ASC, b.start_time ASC
");
$stmtApproved->execute();
$approvedBookings = $stmtApproved->fetchAll(PDO::FETCH_ASSOC);

// fetch booking history with user and payment info
$stmtHistory = $pdo->prepare("
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
        p.payment_date,
        p.refund_amount,
        p.refund_date
    FROM tbl_bookings b
    LEFT JOIN tbl_users u ON b.user_id = u.id
    LEFT JOIN tbl_payments p ON b.id = p.booking_id
    WHERE b.status IN ('completed', 'cancelled')
    ORDER BY b.updated_at DESC
");
$stmtHistory->execute();
$historyBookings = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

// Count bookings for each status
$pendingCount = count($pendingBookings);
$approvedCount = count($approvedBookings);
$historyCount = count($historyBookings);

/*
// Get summary statistics
$totalRevenue = 0;
$pendingPayments = 0;
foreach (array_merge($pendingBookings, $approvedBookings, $historyBookings) as $booking) {
    if ($booking['payment_status'] === 'paid' || $booking['payment_status'] === 'partial') {
        $totalRevenue += $booking['amount_paid'];
        if ($booking['refund_amount']) {
            $totalRevenue -= $booking['refund_amount'];
        }
    }
    if ($booking['payment_status'] === 'pending' || $booking['payment_status'] === 'partial') {
        $pendingPayments += $booking['balance'];
    }
}

*/
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
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error_message'];
                unset($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Bookings</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print Report
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
        </div>

        <!-- Bootstrap Tabs Navigation -->
        <ul class="nav nav-tabs" id="bookingTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                    type="button" role="tab" aria-controls="pending" aria-selected="true">
                    <i class="fas fa-clock me-2"></i>Pending Bookings
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge bg-warning text-dark ms-2"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button"
                    role="tab" aria-controls="approved" aria-selected="false">
                    <i class="fas fa-check me-2"></i>Approved Bookings
                    <?php if ($approvedCount > 0): ?>
                        <span class="badge bg-success ms-2"><?= $approvedCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content" id="bookingTabsContent">
            <!-- Pending Bookings Tab -->
            <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                <?php if (empty($pendingBookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-check"></i>
                        <h4>No Pending Bookings</h4>
                        <p>All bookings have been processed!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover booking-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Customer</th>
                                    <th>Event Type</th>
                                    <th>Date & Time</th>
                                    <th>Duration</th>
                                    <th>Location</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingBookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?= htmlspecialchars($booking['reference_id']) ?></strong>
                                        </td>
                                        <td>
                                            <div class="customer-info">
                                                <i class="fas fa-user me-2"></i>
                                                <?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($booking['event_type']) ?></strong>
                                        </td>
                                        <td>
                                            <div class="datetime-info">
                                                <div>
                                                    <strong><?= date('M d, Y', strtotime($booking['reservation_date'])) ?></strong>
                                                </div>
                                                <small
                                                    class="text-muted"><?= htmlspecialchars($booking['start_time'] . ' - ' . $booking['end_time']) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= $booking['duration'] ?> hrs</span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($booking['city']) ?>
                                        </td>
                                        <td>
                                            <?php if ($booking['payment_status']): ?>
                                                <div class="payment-info">
                                                    <span
                                                        class="payment-badge payment-<?= strtolower($booking['payment_status']) ?>">
                                                        <?= ucfirst($booking['payment_status']) ?>
                                                    </span>
                                                    <div><strong>₱<?= number_format($booking['amount_paid'] ?? 0, 2) ?></strong>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-success btn-sm"
                                                    onclick="updateBookingStatus(<?= $booking['id'] ?>, 'approved')"
                                                    title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm"
                                                    onclick="updateBookingStatus(<?= $booking['id'] ?>, 'cancelled')"
                                                    title="Decline">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button class="btn btn-outline-info btn-sm"
                                                    onclick="viewBookingDetails(<?= htmlspecialchars(json_encode($booking)) ?>)"
                                                    title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Approved Bookings Tab -->
            <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                <?php if (empty($approvedBookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-check"></i>
                        <h4>No Approved Bookings</h4>
                        <p>No bookings are currently approved.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover booking-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Customer</th>
                                    <th>Event Type</th>
                                    <th>Date & Time</th>
                                    <th>Duration</th>
                                    <th>Location</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approvedBookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?= htmlspecialchars($booking['reference_id']) ?></strong>
                                        </td>
                                        <td>
                                            <div class="customer-info">
                                                <i class="fas fa-user me-2"></i>
                                                <?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($booking['event_type']) ?></strong>
                                        </td>
                                        <td>
                                            <div class="datetime-info">
                                                <div>
                                                    <strong><?= date('M d, Y', strtotime($booking['reservation_date'])) ?></strong>
                                                </div>
                                                <small
                                                    class="text-muted"><?= htmlspecialchars($booking['start_time'] . ' - ' . $booking['end_time']) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= $booking['duration'] ?> hrs</span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($booking['city']) ?>
                                        </td>
                                        <td>
                                            <?php if ($booking['payment_status']): ?>
                                                <div class="payment-info">
                                                    <span
                                                        class="payment-badge payment-<?= strtolower($booking['payment_status']) ?>">
                                                        <?= ucfirst($booking['payment_status']) ?>
                                                    </span>
                                                    <div><strong>₱<?= number_format($booking['amount_paid'] ?? 0, 2) ?></strong>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm"
                                                    onclick="updateBookingStatus(<?= $booking['id'] ?>, 'completed')"
                                                    title="Mark Complete">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                                <button class="btn btn-warning btn-sm"
                                                    onclick="updateBookingStatus(<?= $booking['id'] ?>, 'pending')"
                                                    title="Back to Pending">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                                <button class="btn btn-outline-info btn-sm"
                                                    onclick="viewBookingDetails(<?= htmlspecialchars(json_encode($booking)) ?>)"
                                                    title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>


        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-labelledby="bookingDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Booking Details - <span id="modalReferenceId"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Booking Information</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Reference ID:</strong> <span id="modalRefId"></span></li>
                                        <li><strong>Date:</strong> <span id="modalDate"></span></li>
                                        <li><strong>Time:</strong> <span id="modalTime"></span></li>
                                        <li><strong>Duration:</strong> <span id="modalDuration"></span></li>
                                        <li><strong>Event Type:</strong> <span id="modalEvent"></span></li>
                                        <li><strong>Status:</strong> <span id="modalStatus"></span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Customer Information</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Name:</strong> <span id="modalCustomerName"></span></li>
                                        <li><strong>Email:</strong> <span id="modalCustomerEmail"></span></li>
                                        <li><strong>Phone:</strong> <span id="modalCustomerPhone"></span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Information</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Method:</strong> <span id="modalPaymentMethod"></span></li>
                                        <li><strong>Type:</strong> <span id="modalPaymentType"></span></li>
                                        <li><strong>Status:</strong> <span id="modalPaymentStatus"
                                                class="payment-badge payment-partial">Partial</span></li>
                                        <li><strong>Amount Paid:</strong> <span id="modalAmountPaid">₱15,000.00</span>
                                        </li>
                                        <li><strong>Balance:</strong> <span id="modalBalance">₱10,000.00</span></li>
                                        <li><strong>Total:</strong> <span id="modalTotal">₱25,000.00</span></li>
                                        <li><strong>Payment Date:</strong> <span id="modalPaymentDate">May 20,
                                                2025</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Event Details</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Venue:</strong> <span id="modalVenue">Grand Ballroom</span></li>
                                        <li><strong>Guests:</strong> <span id="modalGuests">150 people</span></li>
                                        <li><strong>Package:</strong> <span id="modalPackage">Premium Wedding
                                                Package</span></li>
                                        <li><strong>Special Requests:</strong> <span id="modalRequests">Live band,
                                                flower arrangements</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Activity Timeline</h6>
                                </div>
                                <div class="card-body">
                                    <div class="timeline" id="modalTimeline">
                                        <div class="timeline-item">
                                            <small class="text-muted">May 20, 2025 2:30 PM</small><br>
                                            <strong>Booking Created</strong><br>
                                            Customer submitted booking request
                                        </div>
                                        <div class="timeline-item">
                                            <small class="text-muted">May 20, 2025 3:15 PM</small><br>
                                            <strong>Payment Received</strong><br>
                                            Partial payment of ₱15,000.00
                                        </div>
                                        <div class="timeline-item">
                                            <small class="text-muted">May 20, 2025 4:00 PM</small><br>
                                            <strong>Status: Pending Review</strong><br>
                                            Awaiting admin approval
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-comment me-2"></i>Notes & Comments</h6>
                                </div>
                                <div class="card-body">
                                    <div id="modalNotes">
                                        <p><strong>Customer Note:</strong> Please arrange for a live band setup area.
                                            We'll need power outlets for instruments.</p>
                                        <p><strong>Admin Note:</strong> Confirmed venue availability. Need to verify
                                            catering arrangements.</p>
                                    </div>
                                    <div class="mt-3">
                                        <textarea class="form-control" rows="3" placeholder="Add a note..."
                                            id="newNote"></textarea>
                                        <button class="btn btn-primary btn-sm mt-2" onclick="addNote()">
                                            <i class="fas fa-plus me-1"></i>Add Note
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success"
                        onclick="updateBookingStatus(currentBookingId, 'approved')">
                        <i class="fas fa-check me-1"></i>Approve Booking
                    </button>
                    <button type="button" class="btn btn-danger"
                        onclick="updateBookingStatus(currentBookingId, 'cancelled')">
                        <i class="fas fa-times me-1"></i>Cancel Booking
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/booking-management.js"></script>

</body>