<?php
// pages/customer/actions/reschedule-booking.php

header('Content-Type: application/json');

// Session and authentication checks
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_email'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Include dependencies
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/src/controllers/CustomerBookingController.php';

use Controllers\CustomerBookingController;

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid request data');
    }

    // Validate required fields
    $required_fields = ['booking_id', 'new_date', 'new_start_time', 'new_end_time', 'reason'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Sanitize input
    $booking_id = (int) $input['booking_id'];
    $new_date = trim($input['new_date']);
    $new_start_time = trim($input['new_start_time']);
    $new_end_time = trim($input['new_end_time']);
    $reason = trim($input['reason']);

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $new_date)) {
        throw new Exception('Invalid date format');
    }

    // Validate time format
    if (!preg_match('/^\d{2}:\d{2}$/', $new_start_time) || !preg_match('/^\d{2}:\d{2}$/', $new_end_time)) {
        throw new Exception('Invalid time format');
    }

    // Check if new date is in the future (at least 24 hours from now)
    $new_datetime = new DateTime($new_date . ' ' . $new_start_time);
    $now = new DateTime();
    $now->add(new DateInterval('PT24H')); // Add 24 hours

    if ($new_datetime < $now) {
        throw new Exception('New booking date must be at least 24 hours from now');
    }

    // Check if new date is within 6 months
    $max_date = new DateTime();
    $max_date->add(new DateInterval('P6M')); // Add 6 months

    if ($new_datetime > $max_date) {
        throw new Exception('New booking date must be within the next 6 months');
    }

    // Validate that end time is after start time
    $start_time_obj = new DateTime($new_date . ' ' . $new_start_time);
    $end_time_obj = new DateTime($new_date . ' ' . $new_end_time);

    if ($end_time_obj <= $start_time_obj) {
        throw new Exception('End time must be after start time');
    }

    // Initialize controller
    $controller = new CustomerBookingController();

    // Process reschedule request
    $result = $controller->rescheduleBooking(
        $booking_id,
        $_SESSION['user_email'],
        $new_date,
        $new_start_time,
        $new_end_time,
        $reason
    );

    if ($result['success']) {
        // Log the successful reschedule
        error_log("Booking rescheduled successfully: " . json_encode([
            'booking_id' => $booking_id,
            'user_email' => $_SESSION['user_email'],
            'new_date' => $new_date,
            'new_start_time' => $new_start_time,
            'new_end_time' => $new_end_time
        ]));

        echo json_encode($result);
    } else {
        throw new Exception($result['message'] ?? 'Failed to reschedule booking');
    }

} catch (Exception $e) {
    error_log("Reschedule booking error: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    error_log("Reschedule booking system error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again later.'
    ]);
}
?>