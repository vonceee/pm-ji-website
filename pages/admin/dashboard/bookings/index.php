<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

$admin_username = $_SESSION['admin_username'];

// get PDO connection from your Database class
$pdo = Database::getConnection();

// fetch pending bookings
$stmtPending = $pdo->prepare("SELECT * FROM tbl_bookings WHERE status = 'pending'");
$stmtPending->execute();
$pendingBookings = $stmtPending->fetchAll();

// fetch approved bookings
$stmtApproved = $pdo->prepare("SELECT * FROM tbl_bookings WHERE status = 'approved'");
$stmtApproved->execute();
$approvedBookings = $stmtApproved->fetchAll();

// fetch booking history (e.g., completed bookings)
$stmtHistory = $pdo->prepare("SELECT * FROM tbl_bookings WHERE status = 'completed'");
$stmtHistory->execute();
$historyBookings = $stmtHistory->fetchAll();
?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/index.css">
</head>

<body>

    <div>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message'];
                unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
    </div>
    <div>
        <h2 class="mb-4">Manage Bookings</h2>

        <!-- Bootstrap Tabs Navigation -->
        <ul class="nav nav-tabs" id="bookingTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                    type="button" role="tab" aria-controls="pending" aria-selected="true">
                    Pending Bookings
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved"
                    type="button" role="tab" aria-controls="approved" aria-selected="false">
                    Approved Bookings
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history"
                    type="button" role="tab" aria-controls="history" aria-selected="false">
                    Booking History
                </button>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content" id="bookingTabsContent">
            <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                <?php include __DIR__ . '/components/pending_bookings.php'; ?>
            </div>
            <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                <?php include __DIR__ . '/components/approved_bookings.php'; ?>
            </div>
            <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                <?php include __DIR__ . '/components/history_bookings.php'; ?>
            </div>
        </div>
    </div><!-- End Container -->

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-labelledby="bookingDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Reference ID: <span id="modalReferenceId"></span> &mdash; <span id="modalCustomerName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <h6>Basic Details</h6>
                            <ul class="list-unstyled mb-0">
                                <li><strong>Date:</strong> <span id="modalDate"></span></li>
                                <li><strong>Time:</strong> <span id="modalTime"></span></li>
                                <li><strong>Event:</strong> <span id="modalEvent"></span></li>
                                <li><strong>Location:</strong> <span id="modalLocation"></span></li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6>Customer Details</h6>
                            <ul class="list-unstyled mb-0">
                                <li><strong>Name:</strong> <span id="modalCustomerFullName"></span></li>
                                <li><strong>Contact:</strong> <span id="modalCustomerContact"></span></li>
                                <li><strong>Email:</strong> <span id="modalCustomerEmail"></span></li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6>Payment Details</h6>
                            <ul class="list-unstyled mb-0">
                                <li><strong>Method:</strong> <span id="modalPaymentMethod"></span></li>
                                <li><strong>Status:</strong> <span id="modalPaymentStatus"></span></li>
                                <li><strong>Total Amount:</strong> ₱<span id="modalTotalAmount"></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS (with Popper) for tab/collapse functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.booking-row').forEach(function (row) {
                row.addEventListener('click', function () {
                    document.getElementById('modalReferenceId').textContent = this.dataset.referenceId || '';
                    document.getElementById('modalCustomerName').textContent = this.dataset.customerName || '';
                    document.getElementById('modalDate').textContent = this.dataset.date || '';
                    document.getElementById('modalTime').textContent = this.dataset.time || '';
                    document.getElementById('modalEvent').textContent = this.dataset.event || '';
                    document.getElementById('modalLocation').textContent = this.dataset.location || '';
                    document.getElementById('modalCustomerFullName').textContent = this.dataset.customerFullname || '';
                    document.getElementById('modalCustomerContact').textContent = this.dataset.customerContact || '';
                    document.getElementById('modalCustomerEmail').textContent = this.dataset.customerEmail || '';
                    document.getElementById('modalPaymentMethod').textContent = this.dataset.paymentMethod || '';
                    document.getElementById('modalPaymentStatus').textContent = this.dataset.paymentStatus || '';
                    document.getElementById('modalTotalAmount').textContent = this.dataset.totalAmount || '';
                    var modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
                    modal.show();
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/js/bootstrap.min.js"></script>

</body>

</html>