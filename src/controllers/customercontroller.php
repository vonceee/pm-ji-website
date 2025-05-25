<?php
// src/controllers/customercontroller.php

require_once __DIR__ . '/../services/userservice.php';
require_once __DIR__ . '/../services/bookingservice.php';
require_once __DIR__ . '/../services/paymentservice.php';

class CustomerController
{
    private $userService;
    private $bookingService;
    private $paymentService;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->bookingService = new BookingService();
        $this->paymentService = new PaymentService();
    }

    public function dashboard()
    {
        // Session validation
        session_start();
        if (!isset($_SESSION['user_email'])) {
            header("Location: /NEW-PM-JI-RESERVIFY/index.php");
            exit;
        }

        // Handle POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequests();
        }

        // Get user data
        $user = $this->userService->getUserByEmail($_SESSION['user_email']);
        if (!$user) {
            die('User not found.');
        }

        // Get bookings with pagination and filters
        $filters = $this->getFilters();
        $bookingsData = $this->bookingService->getUserBookings($user['id'], $filters);

        // Load the dashboard view
        $this->loadDashboardView($user, $bookingsData, $filters);
    }

    private function handlePostRequests()
    {
        if (isset($_POST['cancel_reference_id'])) {
            $this->cancelBooking($_POST['cancel_reference_id']);
        }
    }

    private function getFilters()
    {
        return [
            'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
            'filter_date' => isset($_GET['filter_date']) ? trim($_GET['filter_date']) : '',
            'page' => isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1,
            'limit' => 5
        ];
    }

    private function cancelBooking($referenceId)
    {
        $result = $this->bookingService->cancelBooking($referenceId);
        if ($result['success']) {
            $_SESSION['message'] = 'Booking cancelled successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to cancel booking: ' . $result['error'];
            $_SESSION['message_type'] = 'error';
        }

        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    private function loadDashboardView($user, $bookingsData, $filters)
    {
        // Extract variables for the view
        extract($user);
        extract($bookingsData);
        extract($filters);

        // Calculate pagination variables
        $totalPages = ceil($totalBookings / $limit);
        $offset = ($page - 1) * $limit;

        // Load the view
        include __DIR__ . '/../../pages/customer/views/dashboard.php';
    }

    public function updateProfile()
    {
        session_start();
        if (!isset($_SESSION['user_email'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->userService->updateProfile($_SESSION['user_email'], $_POST);
            echo json_encode($result);
        }
    }
}