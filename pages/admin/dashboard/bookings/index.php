<?php

$admin_username = $_SESSION['admin_username'];

$host = 'localhost';
$db = 'db_pmji';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

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
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button"
                    role="tab" aria-controls="approved" aria-selected="false">
                    Approved Bookings
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button"
                    role="tab" aria-controls="history" aria-selected="false">
                    Booking History
                </button>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content" id="bookingTabsContent">
            <!-- Pending Bookings Tab -->
            <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                <div class="table-responsive mt-4">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Customer Email</th>
                                <th>Event</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingBookings as $booking): ?>
                                <?php
                                $stmtUser = $pdo->prepare("SELECT email, CONCAT(first_name, ' ', last_name) AS customer_name, contact_no AS customer_contact FROM tbl_users WHERE id = ?");
                                $stmtUser->execute([$booking['user_id']]);
                                $user = $stmtUser->fetch();
                                $userEmail = $user['email'];
                                $customerName = $user['customer_name'];
                                $customerContact = $user['customer_contact'];
                                ?>
                                <tr class="booking-row"
                                    data-reference-id="<?= htmlspecialchars($booking['reference_id']) ?>"
                                    data-customer-name="<?= htmlspecialchars($customerName) ?>"
                                    data-date="<?= date('M d, Y', strtotime($booking['reservation_date'])) ?>"
                                    data-time="<?= htmlspecialchars($booking['start_time']) ?>"
                                    data-event="<?= htmlspecialchars($booking['event_type']) ?>"
                                    data-location="<?= htmlspecialchars($booking['street_address'] . ', ' . $booking['barangay'] . ', ' . $booking['city']) ?>"
                                    data-customer-fullname="<?= htmlspecialchars($customerName) ?>"
                                    data-customer-contact="<?= htmlspecialchars($customerContact) ?>"
                                    data-customer-email="<?= htmlspecialchars($userEmail) ?>"
                                    data-payment-method="<?= htmlspecialchars($booking['payment_method']) ?>"
                                    data-payment-status="<?= htmlspecialchars($booking['payment_status']) ?>"
                                    data-total-amount="<?= number_format($booking['price'], 2) ?>" style="cursor:pointer;">
                                    <td>
                                        <?php
                                        $date = date('M d, Y', strtotime($booking['reservation_date']));
                                        $time = isset($booking['start_time']) ? htmlspecialchars($booking['start_time']) : '';
                                        echo $date . ($time ? " <br><small class='text-muted'>$time</small>" : '');
                                        ?>
                                    </td>
                                    <td>
                                        <a
                                            href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/customers/profile.php?user_id=<?= htmlspecialchars($booking['user_id']) ?>">
                                            <?= htmlspecialchars($userEmail) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($booking['event_type']) ?></td>
                                    <td><?= htmlspecialchars($booking['duration']) ?> Hours</td>
                                    <td>
                                        <form action="update_booking_status.php" method="POST" class="mb-0">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <select name="status" class="form-select form-select-sm"
                                                onchange="this.form.submit()">
                                                <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="approved" <?= $booking['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                                <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($booking['payment_status']) ?>
                                        <?php if (!empty($booking['payment_method'])): ?>
                                            <br><small
                                                class="text-muted"><?= htmlspecialchars($booking['payment_method']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('M d, Y h:i A', strtotime($booking['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Approved Bookings Tab -->
            <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                <div class="table-responsive mt-4">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Reference ID</th>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvedBookings as $booking): ?>
                                <?php
                                $stmtUser = $pdo->prepare("SELECT email, CONCAT(first_name, ' ', last_name) AS customer_name, contact_number AS customer_contact FROM tbl_users WHERE id = ?");
                                $stmtUser->execute([$booking['user_id']]);
                                $user = $stmtUser->fetch();
                                $userEmail = $user['email'];
                                $customerName = $user['customer_name'];
                                $customerContact = $user['customer_contact'];
                                ?>
                                <tr>
                                    <td>
                                        <a
                                            href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/customers/profile.php?user_id=<?php echo htmlspecialchars($booking['user_id']); ?>">
                                            <?php echo htmlspecialchars($userEmail); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['reference_id']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['event_type']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['reservation_date']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['status']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['payment_status']); ?></td>
                                    <td>
                                        <form action="approve_booking.php" method="POST" class="d-inline">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" class="btn btn-success btn-sm mx-1"
                                                title="Mark as Fully Paid">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        </form>
                                        <a href="edit_booking.php?booking_id=<?php echo $booking['id']; ?>"
                                            class="btn btn-primary btn-sm mx-1" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button class="btn btn-secondary btn-sm mx-1" data-bs-toggle="collapse"
                                            data-bs-target="#approved-details-<?php echo $booking['id']; ?>"
                                            title="Expand/Collapse">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="collapse" id="approved-details-<?php echo $booking['id']; ?>">
                                    <td colspan="7">
                                        <div class="p-3 bg-light border rounded">
                                            <strong>Event Details:</strong><br>
                                            <span class="ms-3">Duration:</span>
                                            <?php echo htmlspecialchars($booking['duration']); ?> hours<br>
                                            <span class="ms-3">Time Slot:</span>
                                            <?php echo htmlspecialchars($booking['time_slot']); ?><br>
                                            <br><strong>Location:</strong><br>
                                            <span
                                                class="ms-3"><?php echo htmlspecialchars($booking['street_address'] . ', ' . $booking['barangay'] . ', ' . $booking['city'] . ', ' . $booking['province']); ?></span><br>
                                            <br><strong>Payment:</strong><br>
                                            <span class="ms-3">Method:</span>
                                            <?php echo htmlspecialchars($booking['payment_method']); ?><br>
                                            <span class="ms-3">Type:</span>
                                            <?php echo htmlspecialchars($booking['payment_type']); ?><br>
                                            <span class="ms-3">Status:</span>
                                            <?php echo htmlspecialchars($booking['payment_status']); ?><br>
                                            <span class="ms-3">Reference #:</span>
                                            <?php echo htmlspecialchars($booking['reference_number']); ?><br>
                                            <span class="ms-3">Screenshot:</span>
                                            <?php if (!empty($booking['payment_screenshot'])): ?>
                                                <a href="/NEW-PM-JI-RESERVIFY/pages/customer/uploads/<?php echo htmlspecialchars($booking['payment_screenshot']); ?>"
                                                    download class="btn btn-link">Download</a>
                                            <?php else: ?>
                                                <span class="text-danger">No screenshot uploaded.</span>
                                            <?php endif; ?><br>
                                            <br><strong>Created At:</strong>
                                            <?php echo htmlspecialchars($booking['created_at']); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Booking History Tab -->
            <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                <div class="table-responsive mt-4">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Reference ID</th>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historyBookings as $booking): ?>
                                <?php
                                $stmtUser = $pdo->prepare("SELECT email, CONCAT(first_name, ' ', last_name) AS customer_name, contact_number AS customer_contact FROM tbl_users WHERE id = ?");
                                $stmtUser->execute([$booking['user_id']]);
                                $user = $stmtUser->fetch();
                                $userEmail = $user['email'];
                                $customerName = $user['customer_name'];
                                $customerContact = $user['customer_contact'];
                                ?>
                                <tr>
                                    <td>
                                        <a
                                            href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/customers/profile.php?user_id=<?php echo htmlspecialchars($booking['user_id']); ?>">
                                            <?php echo htmlspecialchars($userEmail); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['reference_id']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['event_type']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['reservation_date']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['status']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['payment_status']); ?></td>
                                    <td>
                                        <a href="edit_booking.php?booking_id=<?php echo $booking['id']; ?>"
                                            class="btn btn-primary btn-sm mx-1" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button class="btn btn-secondary btn-sm mx-1" data-bs-toggle="collapse"
                                            data-bs-target="#history-details-<?php echo $booking['id']; ?>"
                                            title="Expand/Collapse">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="collapse" id="history-details-<?php echo $booking['id']; ?>">
                                    <td colspan="7">
                                        <div class="p-3 bg-light border rounded">
                                            <strong>Event Details:</strong><br>
                                            <span class="ms-3">Duration:</span>
                                            <?php echo htmlspecialchars($booking['duration']); ?> hours<br>
                                            <span class="ms-3">Time Slot:</span>
                                            <?php echo htmlspecialchars($booking['time_slot']); ?><br>
                                            <br><strong>Location:</strong><br>
                                            <span
                                                class="ms-3"><?php echo htmlspecialchars($booking['street_address'] . ', ' . $booking['barangay'] . ', ' . $booking['city'] . ', ' . $booking['province']); ?></span><br>
                                            <br><strong>Payment:</strong><br>
                                            <span class="ms-3">Method:</span>
                                            <?php echo htmlspecialchars($booking['payment_method']); ?><br>
                                            <span class="ms-3">Type:</span>
                                            <?php echo htmlspecialchars($booking['payment_type']); ?><br>
                                            <span class="ms-3">Status:</span>
                                            <?php echo htmlspecialchars($booking['payment_status']); ?><br>
                                            <span class="ms-3">Reference #:</span>
                                            <?php echo htmlspecialchars($booking['reference_number']); ?><br>
                                            <span class="ms-3">Screenshot:</span>
                                            <?php if (!empty($booking['payment_screenshot'])): ?>
                                                <a href="/NEW-PM-JI-RESERVIFY/pages/customer/uploads/<?php echo htmlspecialchars($booking['payment_screenshot']); ?>"
                                                    download class="btn btn-link">Download</a>
                                            <?php else: ?>
                                                <span class="text-danger">No screenshot uploaded.</span>
                                            <?php endif; ?><br>
                                            <br><strong>Created At:</strong>
                                            <?php echo htmlspecialchars($booking['created_at']); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- End Tabs Content -->
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
</body>

</html>