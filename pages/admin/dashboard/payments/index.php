<?php
session_start();
// Check if admin is logged in; if not, redirect to the login page.
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}
$admin_username = $_SESSION['admin_username'];

// Database connection configuration.
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

// Handle status update to 'fully_paid'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_fully_paid'])) {
    $paymentId = intval($_POST['payment_id']);
    $stmt = $pdo->prepare("UPDATE tbl_bookings SET payment_status = 'fully_paid' WHERE id = ?");
    $stmt->execute([$paymentId]);
}

// Fetch ongoing payments (only down_payment)
$stmtOngoing = $pdo->prepare("SELECT * FROM tbl_bookings WHERE payment_status = 'down_payment'");
$stmtOngoing->execute();
$ongoingPayments = $stmtOngoing->fetchAll();

// Fetch fully paid payments
$stmtFullyPaid = $pdo->prepare("SELECT * FROM tbl_bookings WHERE payment_status = 'fully_paid'");
$stmtFullyPaid->execute();
$fullyPaidPayments = $stmtFullyPaid->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Payments</title>
    <!-- Bootstrap 5 CSS for tab layout and collapse functionality -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for action icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <!-- Custom admin dashboard styles -->
    <link rel="stylesheet" href="../index.css">
</head>

<body>
    <!-- Include common header -->
    <?php include '../components/admin_header.php'; ?>

    <!-- Dashboard Container: Sidebar + Main Content -->
    <div class="dashboard-container">
        <!-- Include the sidebar -->
        <?php include '../components/admin_sidebar.php'; ?>

        <!-- Main Content Section -->
        <main class="main-content">
            <div class="container my-4">
                <h2 class="mb-4">Manage Payments</h2>

                <!-- Bootstrap Tabs Navigation -->
                <ul class="nav nav-tabs" id="paymentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="ongoing-payments-tab" data-bs-toggle="tab"
                            data-bs-target="#ongoing-payments" type="button" role="tab" aria-controls="ongoing-payments"
                            aria-selected="true">
                            Ongoing
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="fully-paid-tab" data-bs-toggle="tab"
                            data-bs-target="#fully-paid" type="button" role="tab"
                            aria-controls="fully-paid" aria-selected="false">
                            Fully Paid
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="paymentTabsContent">
                    <!-- Ongoing Payments Tab -->
                    <div class="tab-pane fade show active" id="ongoing-payments" role="tabpanel"
                        aria-labelledby="ongoing-payments-tab">
                        <div class="table-responsive mt-4">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>User ID</th>
                                        <th>Payment Method</th>
                                        <th>Payment Type</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($ongoingPayments) > 0): ?>
                                        <?php foreach ($ongoingPayments as $payment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($payment['id']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['user_id']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['payment_type']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['created_at']); ?></td>
                                                <td>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                        <button type="submit" name="mark_fully_paid" class="btn btn-success btn-sm" title="Mark as Fully Paid">
                                                            Mark as Fully Paid
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <tr class="collapse" id="payment-details-<?php echo $payment['id']; ?>">
                                                <td colspan="6">
                                                    <div class="p-3 bg-light">
                                                        <strong>Reference Number:</strong>
                                                        <?php echo htmlspecialchars($payment['reference_number']); ?><br>
                                                        <strong>Payment Screenshot:</strong>
                                                        <?php if (!empty($payment['payment_screenshot'])): ?>
                                                            <img src="../uploads/<?php echo htmlspecialchars($payment['payment_screenshot']); ?>"
                                                                alt="Payment Screenshot" class="img-thumbnail"
                                                                style="max-width: 200px;">
                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No ongoing payments found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Fully Paid Payments Tab -->
                    <div class="tab-pane fade" id="fully-paid" role="tabpanel"
                        aria-labelledby="fully-paid-tab">
                        <div class="table-responsive mt-4">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>User ID</th>
                                        <th>Payment Method</th>
                                        <th>Payment Type</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($fullyPaidPayments) > 0): ?>
                                        <?php foreach ($fullyPaidPayments as $payment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($payment['id']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['user_id']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['payment_type']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['created_at']); ?></td>
                                                <td>
                                                    <span class="badge bg-success">Fully Paid</span>
                                                </td>
                                            </tr>
                                            <tr class="collapse" id="payment-details-<?php echo $payment['id']; ?>">
                                                <td colspan="6">
                                                    <div class="p-3 bg-light">
                                                        <strong>Reference Number:</strong>
                                                        <?php echo htmlspecialchars($payment['reference_number']); ?><br>
                                                        <strong>Payment Screenshot:</strong>
                                                        <?php if (!empty($payment['payment_screenshot'])): ?>
                                                            <img src="../uploads/<?php echo htmlspecialchars($payment['payment_screenshot']); ?>"
                                                                alt="Payment Screenshot" class="img-thumbnail"
                                                                style="max-width: 200px;">
                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No fully paid payments found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div><!-- End Tabs Content -->
            </div><!-- End Container -->
        </main>
    </div><!-- End Dashboard Container -->

    <!-- Bootstrap 5 JS Bundle (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>