<?php

// Database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

// Fetch the shared PDO instance
$pdo = Database::getConnection();

// Payment model
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/PaymentModel.php';

$paymentModel = new \Models\PaymentModel($pdo);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    try {
        switch ($_POST['action']) {
            case 'mark_paid':
                $paymentId = (int) $_POST['payment_id'];
                $paymentMethod = $_POST['payment_method'] ?? 'cash';
                $notes = $_POST['notes'] ?? '';

                $result = $paymentModel->markAsPaid($paymentId, $paymentMethod, $notes);
                echo json_encode(['success' => true, 'message' => 'Payment marked as fully paid']);
                break;

            case 'partial_payment':
                $paymentId = (int) $_POST['payment_id'];
                $amount = (float) $_POST['amount'];
                $paymentMethod = $_POST['payment_method'] ?? 'cash';
                $notes = $_POST['notes'] ?? '';

                $result = $paymentModel->updatePartialPayment($paymentId, $amount, $paymentMethod, $notes);
                echo json_encode(['success' => true, 'message' => 'Partial payment recorded successfully']);
                break;

            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get outstanding payments and stats
$outstandingPayments = $paymentModel->getOutstandingPayments();
$paymentStats = $paymentModel->getPaymentStats();
$recentActivities = $paymentModel->getRecentPaymentActivities(5);

?>

<head>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments/index.css" />

    <!-- SweetAlert2 for better alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
    <!-- Payment Statistics Header -->
    <header>
        <div class="payment-header">
            <h4>Payment Management</h4>
            <div class="payment-stats">
                <div class="stat-card">
                    <div class="stat-number text-warning"><?= $paymentStats['count_outstanding'] ?></div>
                    <div class="stat-label">Outstanding Payments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number text-danger">₱<?= number_format($paymentStats['total_outstanding'], 2) ?>
                    </div>
                    <div class="stat-label">Total Outstanding</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number text-danger"><?= $paymentStats['overdue_count'] ?></div>
                    <div class="stat-label">Overdue Payments</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Outstanding Payments Table -->
    <section class="outstanding-payments-section">
        <div class="section-header">
            <h5>Outstanding Payments</h5>
            <button class="btn btn-primary btn-sm" onclick="refreshData()">
                <i class="fas fa-refresh"></i> Refresh
            </button>
        </div>

        <?php if (empty($outstandingPayments)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Great! No outstanding payments at the moment.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
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
                                    <span class="balance-amount text-warning">
                                        <strong>₱<?= number_format($payment['balance'], 2) ?></strong>
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
                                        <button class="btn btn-info btn-sm"
                                            onclick="viewPaymentDetails(<?= $payment['payment_id'] ?>)" title="View Details">
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
    </section>

    <!-- Recent Payment Activities -->
    <?php if (!empty($recentActivities)): ?>
        <section class="recent-activities-section">
            <div class="section-header">
                <h5>Recent Payment Activities</h5>
            </div>
            <div class="activities-list">
                <?php foreach ($recentActivities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-money-bill-wave text-success"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">
                                Payment updated for <strong><?= htmlspecialchars($activity['reference_id']) ?></strong>
                                (<?= htmlspecialchars($activity['client_name']) ?>)
                            </div>
                            <div class="activity-meta">
                                <span class="text-muted"><?= date('M d, Y h:i A', strtotime($activity['updated_at'])) ?></span>
                                <span class="status-<?= $activity['status'] ?>"><?= ucfirst($activity['status']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments/payments.js"></script>
</body>