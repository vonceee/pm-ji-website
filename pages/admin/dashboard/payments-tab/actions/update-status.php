<?php
/**
 * Payment Status Update Handler
 * Handles AJAX requests for updating payment statuses
 */

// Prevent direct access
if (!defined('ALLOW_DIRECT_ACCESS')) {
    define('ALLOW_DIRECT_ACCESS', true);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

// Fetch the shared PDO instance
$pdo = Database::getConnection();

// Payment model
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/PaymentModel.php';

// Set content type for JSON response
header('Content-Type: application/json');

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get the action
    $action = $_POST['action'] ?? '';

    if (empty($action)) {
        throw new Exception('Action not specified');
    }

    // Initialize payment model
    $paymentModel = new \Models\PaymentModel($pdo);

    switch ($action) {
        case 'mark_paid':
            // Validate required fields
            if (!isset($_POST['payment_id']) || !is_numeric($_POST['payment_id'])) {
                throw new Exception('Invalid payment ID');
            }

            $paymentId = (int) $_POST['payment_id'];
            $paymentMethod = $_POST['payment_method'] ?? 'cash';
            $notes = $_POST['notes'] ?? '';

            // Validate payment method
            $validPaymentMethods = ['cash', 'bank_transfer', 'credit_card', 'gcash', 'paymaya', 'check'];
            if (!in_array($paymentMethod, $validPaymentMethods)) {
                throw new Exception('Invalid payment method');
            }

            // Get payment details first to verify it exists and get balance
            $payment = $paymentModel->getPaymentById($paymentId);
            if (!$payment) {
                throw new Exception('Payment not found');
            }

            // Check if payment is already fully paid
            if ($payment['payment_status'] === 'paid') {
                throw new Exception('Payment is already fully paid');
            }

            // Mark as paid
            $result = $paymentModel->markAsPaid($paymentId, $paymentMethod, $notes);

            if ($result) {
                // Log the activity
                $logData = [
                    'payment_id' => $paymentId,
                    'action' => 'mark_paid',
                    'amount' => $payment['balance'],
                    'payment_method' => $paymentMethod,
                    'notes' => $notes,
                    'admin_id' => $_SESSION['admin_id'] ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // You might want to create a payment log table for this
                // $paymentModel->logPaymentActivity($logData);

                echo json_encode([
                    'success' => true,
                    'message' => 'Payment marked as fully paid successfully',
                    'data' => [
                        'payment_id' => $paymentId,
                        'new_status' => 'paid'
                    ]
                ]);
            } else {
                throw new Exception('Failed to update payment status');
            }
            break;

        case 'partial_payment':
            // Validate required fields
            if (!isset($_POST['payment_id']) || !is_numeric($_POST['payment_id'])) {
                throw new Exception('Invalid payment ID');
            }

            if (!isset($_POST['amount']) || !is_numeric($_POST['amount']) || $_POST['amount'] <= 0) {
                throw new Exception('Invalid payment amount');
            }

            $paymentId = (int) $_POST['payment_id'];
            $amount = (float) $_POST['amount'];
            $paymentMethod = $_POST['payment_method'] ?? 'cash';
            $notes = $_POST['notes'] ?? '';

            // Validate payment method
            $validPaymentMethods = ['cash', 'bank_transfer', 'credit_card', 'gcash', 'paymaya', 'check'];
            if (!in_array($paymentMethod, $validPaymentMethods)) {
                throw new Exception('Invalid payment method');
            }

            // Get payment details first to verify it exists and validate amount
            $payment = $paymentModel->getPaymentById($paymentId);
            if (!$payment) {
                throw new Exception('Payment not found');
            }

            // Check if payment is already fully paid
            if ($payment['payment_status'] === 'paid') {
                throw new Exception('Payment is already fully paid');
            }

            // Validate amount doesn't exceed balance
            if ($amount > $payment['balance']) {
                throw new Exception('Payment amount cannot exceed outstanding balance');
            }

            // Record partial payment
            $result = $paymentModel->updatePartialPayment($paymentId, $amount, $paymentMethod, $notes);

            if ($result) {
                // Get updated payment info
                $updatedPayment = $paymentModel->getPaymentById($paymentId);

                // Log the activity
                $logData = [
                    'payment_id' => $paymentId,
                    'action' => 'partial_payment',
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'notes' => $notes,
                    'admin_id' => $_SESSION['admin_id'] ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // You might want to create a payment log table for this
                // $paymentModel->logPaymentActivity($logData);

                echo json_encode([
                    'success' => true,
                    'message' => 'Partial payment recorded successfully',
                    'data' => [
                        'payment_id' => $paymentId,
                        'amount_paid' => $amount,
                        'new_balance' => $updatedPayment['balance'],
                        'new_status' => $updatedPayment['payment_status']
                    ]
                ]);
            } else {
                throw new Exception('Failed to record partial payment');
            }
            break;

        case 'get_payment_details':
            // This case is handled in the main index.php file
            if (!isset($_GET['payment_id']) || !is_numeric($_GET['payment_id'])) {
                throw new Exception('Invalid payment ID');
            }

            $paymentId = (int) $_GET['payment_id'];
            $payment = $paymentModel->getPaymentById($paymentId);

            if ($payment) {
                echo json_encode([
                    'success' => true,
                    'payment' => $payment
                ]);
            } else {
                throw new Exception('Payment not found');
            }
            break;

        default:
            throw new Exception('Invalid action specified');
    }

} catch (Exception $e) {
    // Log error for debugging
    error_log("Payment Update Error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'PAYMENT_UPDATE_ERROR'
    ]);
}

// Ensure script terminates here for AJAX requests
exit;
?>