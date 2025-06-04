<?php

// database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

// fetch the shared PDO instance
$pdo = Database::getConnection();

// payment model
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/PaymentModel.php';

$paymentModel = new \Models\PaymentModel($pdo);

// handle AJAX requests
if (
    ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) ||
    ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']))
) {
    header('Content-Type: application/json');

    try {
        $action = $_POST['action'] ?? $_GET['action'];

        switch ($action) {
            case 'mark_paid':
                $paymentId = (int) $_POST['payment_id'];
                $paymentMethod = $_POST['payment_method'] ?? 'cash';
                $notes = $_POST['notes'] ?? '';

                $result = $paymentModel->markAsPaid($paymentId, $paymentMethod, $notes);
                echo json_encode(['success' => true, 'message' => 'payment marked as fully paid']);
                break;

            case 'partial_payment':
                $paymentId = (int) $_POST['payment_id'];
                $amount = (float) $_POST['amount'];
                $paymentMethod = $_POST['payment_method'] ?? 'cash';
                $notes = $_POST['notes'] ?? '';

                $result = $paymentModel->updatePartialPayment($paymentId, $amount, $paymentMethod, $notes);
                echo json_encode(['success' => true, 'message' => 'partial payment recorded successfully']);
                break;

            case 'get_payment_details':
                $paymentId = (int) $_GET['payment_id'];
                $payment = $paymentModel->getPaymentById($paymentId);

                if ($payment) {
                    echo json_encode(['success' => true, 'payment' => $payment]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'payment not found']);
                }
                break;

            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// get outstanding payments and stats
$outstandingPayments = $paymentModel->getOutstandingPayments();
$paymentStats = $paymentModel->getPaymentStats();
$recentActivities = $paymentModel->getRecentPaymentActivities(5);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Payment Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payments-tab.css">
    
    <!-- SweetAlert2 for better alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
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
        <div class="payment-header">
            <h4>Payment Management</h4>
            <div class="btn-group">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print Report
                </button>
                <button class="btn btn-primary" onclick="refreshData()">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Outstanding Payments Section -->
        <section class="outstanding-payments-section">
            <div class="section-header">
                <h5>Outstanding Payments</h5>
                <div class="btn-group">
                    <button class="btn btn-outline-info btn-sm" onclick="exportPayments()">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                </div>
            </div>

            <?php if (empty($outstandingPayments)): ?>
                <div class="empty-state">
                    <i class="fas fa-money-check-alt"></i>
                    <h4>No Outstanding Payments</h4>
                    <p>Great! All payments are up to date.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Reference ID</th>
                                <th>Client</th>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Paid Amount</th>
                                <th>Outstanding Balance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($outstandingPayments as $payment): ?>
                                <tr data-payment-id="<?= $payment['payment_id'] ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($payment['reference_id']) ?></strong>
                                    </td>
                                    <td>
                                        <div class="client-info">
                                            <div class="client-name"><?= htmlspecialchars($payment['client_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($payment['phone_number']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="event-info">
                                            <div><?= htmlspecialchars($payment['event_type']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($payment['city']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <div><?= date('M d, Y', strtotime($payment['reservation_date'])) ?></div>
                                            <small class="text-muted">
                                                <?= date('h:i A', strtotime($payment['start_time'])) ?> -
                                                <?= date('h:i A', strtotime($payment['end_time'])) ?>
                                            </small>
                                            <?php if (strtotime($payment['reservation_date']) < time()): ?>
                                                <span class="badge badge-danger badge-sm">Overdue</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="amount-paid">₱<?= number_format($payment['amount_paid'], 2) ?></span>
                                    </td>
                                    <td>
                                        <span class="balance-amount">
                                            ₱<?= number_format($payment['balance'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="payment-status status-<?= $payment['payment_status'] ?>">
                                            <?= ucfirst($payment['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-success btn-sm"
                                                onclick="markAsPaid(<?= $payment['payment_id'] ?>, <?= $payment['balance'] ?>)"
                                                title="Mark as Fully Paid">
                                                <i class="fas fa-check"></i> Mark Paid
                                            </button>
                                            <button class="btn btn-warning btn-sm"
                                                onclick="recordPartialPayment(<?= $payment['payment_id'] ?>, <?= $payment['balance'] ?>)"
                                                title="Record Partial Payment">
                                                <i class="fas fa-coins"></i> Partial
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- Tabs Container -->
        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-button active" onclick="switchTab('activities')">
                    <i class="fas fa-clock"></i> Recent Activities
                </button>
            </div>

            <!-- Recent Activities Tab -->
            <div id="activities-tab" class="tab-content active">
                <?php if (!empty($recentActivities)): ?>
                    <div class="activities-list">
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-text">
                                        Payment updated for <strong><?= htmlspecialchars($activity['reference_id']) ?></strong>
                                        (<?= htmlspecialchars($activity['client_name']) ?>)
                                    </div>
                                    <div class="activity-meta">
                                        <span class="text-muted"><?= date('M d, Y h:i A', strtotime($activity['updated_at'])) ?></span>
                                        <span class="payment-status status-<?= $activity['status'] ?>"><?= ucfirst($activity['status']) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <h4>No Recent Activities</h4>
                        <p>No recent payment activities to display.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments-tab/payment-management.js"></script>
    
</body>

</html>