<?php
// update_booking.php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

// Set content type to JSON
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate required fields
if (!isset($_POST['booking_id']) || !isset($_POST['status']) || !isset($_POST['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$booking_id = intval($_POST['booking_id']);
$new_status = trim($_POST['status']);
$action = trim($_POST['action']);
$admin_username = $_SESSION['admin_username'];

// Validate status
$allowed_statuses = ['pending', 'approved', 'cancelled', 'completed'];
if (!in_array($new_status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status provided']);
    exit;
}

// Validate action
if ($action !== 'update_status') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action provided']);
    exit;
}

try {
    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    // First, check if booking exists and get current status
    $checkStmt = $pdo->prepare("SELECT id, status, user_id FROM tbl_bookings WHERE id = ?");
    $checkStmt->execute([$booking_id]);
    $booking = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }

    $old_status = $booking['status'];
    $user_id = $booking['user_id'];

    // Validate status transition
    $valid_transitions = [
        'pending' => ['approved', 'cancelled'],
        'approved' => ['completed', 'pending', 'cancelled'],
        'completed' => ['cancelled'], // Only allow cancellation for refunds
        'cancelled' => ['pending'] // Allow reactivation
    ];

    if (!isset($valid_transitions[$old_status]) || !in_array($new_status, $valid_transitions[$old_status])) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => "Cannot change status from {$old_status} to {$new_status}"]);
        exit;
    }

    // Update booking status
    $updateStmt = $pdo->prepare("
        UPDATE tbl_bookings 
        SET status = ?, updated_at = NOW(), updated_by = ? 
        WHERE id = ?
    ");
    $updateResult = $updateStmt->execute([$new_status, $admin_username, $booking_id]);

    if (!$updateResult) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to update booking status']);
        exit;
    }

    // Log the status change
    $logStmt = $pdo->prepare("
        INSERT INTO tbl_booking_logs (booking_id, action, old_status, new_status, performed_by, created_at)
        VALUES (?, 'status_change', ?, ?, ?, NOW())
    ");
    $logStmt->execute([$booking_id, $old_status, $new_status, $admin_username]);

    // Handle specific status changes
    switch ($new_status) {
        case 'approved':
            // Send approval notification to user
            sendBookingNotification($pdo, $booking_id, $user_id, 'approved');
            break;

        case 'cancelled':
            // Handle cancellation logic
            handleBookingCancellation($pdo, $booking_id, $user_id);
            break;

        case 'completed':
            // Handle completion logic
            handleBookingCompletion($pdo, $booking_id, $user_id);
            break;
    }

    $pdo->commit();

    // Prepare success message
    $status_messages = [
        'approved' => 'Booking approved successfully',
        'cancelled' => 'Booking cancelled successfully',
        'completed' => 'Booking marked as completed',
        'pending' => 'Booking moved back to pending'
    ];

    echo json_encode([
        'success' => true,
        'message' => $status_messages[$new_status],
        'booking_id' => $booking_id,
        'old_status' => $old_status,
        'new_status' => $new_status
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error in update_booking.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("General error in update_booking.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred']);
}

// Helper function to send booking notifications
function sendBookingNotification($pdo, $booking_id, $user_id, $type)
{
    try {
        // Get user email
        $userStmt = $pdo->prepare("SELECT email, first_name FROM tbl_users WHERE id = ?");
        $userStmt->execute([$user_id]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Insert notification record
            $notifStmt = $pdo->prepare("
                INSERT INTO tbl_notifications (user_id, booking_id, type, title, message, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            $title = '';
            $message = '';

            switch ($type) {
                case 'approved':
                    $title = 'Booking Approved';
                    $message = 'Your booking has been approved. You will receive further details soon.';
                    break;
                case 'cancelled':
                    $title = 'Booking Cancelled';
                    $message = 'Your booking has been cancelled. Please contact us for more information.';
                    break;
                case 'completed':
                    $title = 'Event Completed';
                    $message = 'Your event has been completed. Thank you for choosing our services!';
                    break;
            }

            $notifStmt->execute([$user_id, $booking_id, $type, $title, $message]);

            // You can add email sending logic here
            // sendEmail($user['email'], $title, $message);
        }
    } catch (Exception $e) {
        error_log("Error sending notification: " . $e->getMessage());
    }
}

// Helper function to handle booking cancellation
function handleBookingCancellation($pdo, $booking_id, $user_id)
{
    try {
        // Check if there are any payments to refund
        $paymentStmt = $pdo->prepare("
            SELECT amount_paid, payment_method 
            FROM tbl_payments 
            WHERE booking_id = ? AND status IN ('paid', 'partial')
        ");
        $paymentStmt->execute([$booking_id]);
        $payment = $paymentStmt->fetch(PDO::FETCH_ASSOC);

        if ($payment && $payment['amount_paid'] > 0) {
            // Update payment status to cancelled
            $updatePaymentStmt = $pdo->prepare("
                UPDATE tbl_payments 
                SET status = 'cancelled', updated_at = NOW() 
                WHERE booking_id = ?
            ");
            $updatePaymentStmt->execute([$booking_id]);
        }

        sendBookingNotification($pdo, $booking_id, $user_id, 'cancelled');

    } catch (Exception $e) {
        error_log("Error handling booking cancellation: " . $e->getMessage());
    }
}

// Helper function to handle booking completion
function handleBookingCompletion($pdo, $booking_id, $user_id)
{
    try {
        // Update any pending payments to completed
        $updatePaymentStmt = $pdo->prepare("
            UPDATE tbl_payments 
            SET status = 'completed', updated_at = NOW() 
            WHERE booking_id = ? AND status = 'pending'
        ");
        $updatePaymentStmt->execute([$booking_id]);

        sendBookingNotification($pdo, $booking_id, $user_id, 'completed');

    } catch (Exception $e) {
        error_log("Error handling booking completion: " . $e->getMessage());
    }
}
?>