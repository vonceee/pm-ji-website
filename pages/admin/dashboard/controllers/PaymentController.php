<?php

namespace Controllers;

use Config\Database;
use Models\PaymentOutstandingsModel;
use Models\PaymentRefundsModel;

class PaymentController
{
    private $pdo;
    private $paymentModel;
    private $refundModel;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->paymentModel = new PaymentOutstandingsModel($this->pdo);
        $this->refundModel = new PaymentRefundsModel($this->pdo);
    }

    /**
     * Display the main payment management page
     */
    public function index()
    {
        try {
            $filters = $this->getFiltersFromRequest();
            $data = $this->preparePageData($filters);

            $this->loadView('payments/index', $data);
        } catch (\Exception $e) {
            $this->handleError($e->getMessage());
        }
    }

    /**
     * Handle AJAX requests
     */
    public function handleAjaxRequest()
    {
        header('Content-Type: application/json');

        try {
            $action = $_POST['action'] ?? $_GET['action'] ?? null;

            if (!$action) {
                throw new \Exception('No action specified');
            }

            $response = $this->routeAction($action);
            echo json_encode($response);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Route AJAX actions to appropriate methods
     */
    private function routeAction($action)
    {
        switch ($action) {
            case 'mark_paid':
                return $this->markPaymentAsPaid();
            case 'get_payment_details':
                return $this->getPaymentDetails();
            case 'get_payment_history':
                return $this->getPaymentHistory();
            case 'process_refund':
                return $this->processRefund();
            case 'reject_refund':
                return $this->rejectRefund();
            case 'get_refund_details':
                return $this->getRefundDetails();
            default:
                throw new \Exception('Invalid action: ' . $action);
        }
    }

    /**
     * Mark payment as paid
     */
    private function markPaymentAsPaid()
    {
        $paymentId = (int) ($_POST['payment_id'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'cash';
        $notes = $_POST['notes'] ?? '';

        if (!$paymentId) {
            throw new \Exception('Payment ID is required');
        }

        $result = $this->paymentModel->markAsPaid($paymentId);

        if (!$result) {
            throw new \Exception('Failed to mark payment as paid');
        }

        return ['success' => true, 'message' => 'Payment marked as fully paid'];
    }

    /**
     * Get payment details
     */
    private function getPaymentDetails()
    {
        $paymentId = (int) ($_GET['payment_id'] ?? 0);

        if (!$paymentId) {
            throw new \Exception('Payment ID is required');
        }

        $payment = $this->paymentModel->getPaymentById($paymentId);

        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found'];
        }

        return ['success' => true, 'payment' => $payment];
    }

    /**
     * Get payment history with pagination
     */
    private function getPaymentHistory()
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
        $offset = ($page - 1) * $limit;

        $filters = $this->getFiltersFromRequest();

        $historyPayments = $this->paymentModel->getAllPaymentsHistory(
            $limit,
            $offset,
            $filters['dateFrom'],
            $filters['dateTo'],
            '',
            ''
        );

        $totalCount = $this->paymentModel->getTotalPaymentsCount(
            $filters['dateFrom'],
            $filters['dateTo'],
            '',
            ''
        );

        return [
            'success' => true,
            'payments' => $historyPayments,
            'total' => $totalCount,
            'page' => $page,
            'hasMore' => ($offset + $limit) < $totalCount
        ];
    }

    /**
     * Process refund
     */
    private function processRefund()
    {
        $refundId = (int) ($_POST['refund_id'] ?? 0);
        $refundMethod = $_POST['refund_method'] ?? '';
        $adminNotes = $_POST['admin_notes'] ?? '';
        $refundReference = $_POST['refund_reference'] ?? '';

        if (!$refundId) {
            throw new \Exception('Refund ID is required');
        }

        if (empty($refundMethod)) {
            throw new \Exception('Refund method is required');
        }

        $result = $this->refundModel->processRefund($refundId, $refundMethod, $adminNotes, $refundReference);

        if (!$result) {
            throw new \Exception('Failed to process refund');
        }

        return [
            'success' => true,
            'message' => 'Refund has been processed successfully'
        ];
    }

    /**
     * Reject refund
     */
    private function rejectRefund()
    {
        $refundId = (int) ($_POST['refund_id'] ?? 0);
        $rejectionReason = $_POST['rejection_reason'] ?? '';
        $rejectionNotes = $_POST['rejection_notes'] ?? '';

        if (!$refundId) {
            throw new \Exception('Refund ID is required');
        }

        if (empty($rejectionReason)) {
            throw new \Exception('Rejection reason is required');
        }

        $result = $this->refundModel->rejectRefund($refundId, $rejectionReason, $rejectionNotes);

        if (!$result) {
            throw new \Exception('Failed to reject refund');
        }

        return [
            'success' => true,
            'message' => 'Refund has been rejected successfully'
        ];
    }

    /**
     * Get refund details
     */
    private function getRefundDetails()
    {
        $refundId = (int) ($_GET['refund_id'] ?? 0);

        if (!$refundId) {
            throw new \Exception('Refund ID is required');
        }

        $refund = $this->refundModel->getRefundById($refundId);

        if (!$refund) {
            return ['success' => false, 'message' => 'Refund not found'];
        }

        return ['success' => true, 'refund' => $refund];
    }

    /**
     * Get filters from request
     */
    private function getFiltersFromRequest()
    {
        return [
            'dateFrom' => $_GET['payment_date_from'] ?? '',
            'dateTo' => $_GET['payment_date_to'] ?? '',
        ];
    }

    /**
     * Check if filters are active
     */
    private function hasActiveFilters($filters)
    {
        return !empty($filters['dateFrom']) || !empty($filters['dateTo']);
    }

    /**
     * Prepare data for the view
     */
    private function preparePageData($filters)
    {
        $hasActiveFilters = $this->hasActiveFilters($filters);

        // Fetch data with filters
        $outstandingPayments = $this->paymentModel->getOutstandingPayments(
            $filters['dateFrom'],
            $filters['dateTo'],
            '',
            '',
            ''
        );

        $historyPayments = $this->paymentModel->getAllPaymentsHistory(
            20,
            0,
            $filters['dateFrom'],
            $filters['dateTo'],
            '',
            ''
        );

        $refundPayments = $this->refundModel->getAllRefundPayments(
            $filters['dateFrom'],
            $filters['dateTo']
        );

        return [
            'filters' => $filters,
            'hasActiveFilters' => $hasActiveFilters,
            'outstandingPayments' => $outstandingPayments,
            'historyPayments' => $historyPayments,
            'refundPayments' => $refundPayments,
            'pendingRefunds' => $refundPayments, // Alias for template compatibility
        ];
    }

    /**
     * Load view file
     */
    private function loadView($viewPath, $data = [])
    {
        // Extract data to make variables available in view
        extract($data);

        $viewFile = $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/views/' . $viewPath . '.php';

        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: " . $viewFile);
        }

        require_once $viewFile;
    }

    /**
     * Handle errors
     */
    private function handleError($message)
    {
        error_log("Payment Controller Error: " . $message);

        // In production, you might want to show a generic error page
        $data = ['error' => $message];
        $this->loadView('errors/500', $data);
    }
}