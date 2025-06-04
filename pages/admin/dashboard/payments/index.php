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

<head>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/dashboard/index.css" />

    <!-- SweetAlert2 for better alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Custom Tabs CSS -->
    <style>
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .tabs-container {
            margin-top: 2rem;
        }

        .tabs-nav {
            display: flex;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 1.5rem;
        }

        .tab-button {
            background: none;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: #6c757d;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            position: relative;
        }

        .tab-button:hover {
            color: #495057;
            background-color: #f8f9fa;
        }

        .tab-button.active {
            color: #007bff;
            border-bottom-color: #007bff;
            background-color: #f8f9fa;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .logs-container {
            max-height: 600px;
            overflow-y: auto;
        }

        .log-item {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }

        .log-action {
            font-weight: 600;
            color: #495057;
        }

        .log-timestamp {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .log-details {
            font-size: 0.875rem;
            color: #6c757d;
            line-height: 1.4;
        }

        .log-amount {
            font-weight: 600;
            color: #28a745;
        }

        .log-reference {
            font-weight: 600;
            color: #007bff;
        }

        .load-more-logs {
            text-align: center;
            margin-top: 1rem;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 1rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            background: #fff;
        }

        .activity-icon {
            margin-right: 1rem;
            margin-top: 1rem;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }

        .activity-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <!-- Payment Statistics Header -->
    <header>
        <div class="dashboard-header mb-2">
            <h4>Payment Management</h4>
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
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No recent payment activities to display.
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments/payments.js"></script>
    
</body>