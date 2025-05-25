<?php
// process_refund.php
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
if (!isset($_POST['booking_id']) || !isset($_POST['refund_amount']) || !isset($_POST['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$booking_id = intval($_POST['booking_id']);
$refund_amount = floatval($_POST['refund_amount']);
$action = trim($_POST['action']);
$admin_username = $_SESSION['admin_username'];
$refund_reason = isset($_POST['refund_reason']) ? trim($_POST['refund_reason']) : 'Admin processed refund';

// Validate action
if ($action !== 'process_refund') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action provided']);
    exit;
}

// Validate refund amount
if ($refund_amount <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid refund amount']);
    exit;
}

try {
    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    // Get booking and payment information
    $stmt = $pdo->prepare("
        SELECT 
            b.id as booking_id,
            b.reference_id,
            b.status as booking_status,
            b.user_id,
            p.id as payment_id,
            p.amount_paid,
            p.balance,
            p.payment_method,
            p.status as payment_status,
            COALESCE(p.refund_amount, 0) as existing_refund
        FROM tbl_bookings b
        LEFT JOIN tbl_payments p ON b.id = p.booking_id
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }

    // Validate booking status (should be completed or cancelled for refunds)
    if (!in_array($booking['booking_status'], ['completed', 'cancelled'])) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Refunds can only be processed for completed or cancelled bookings']);
        exit;
    }

    // Check if payment exists
    if (!$booking['payment_id']) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'No payment record found for this booking']);
        exit;
    }

    // Calculate maximum refundable amount
    $total_paid = $booking['amount_paid'];
    $existing_refund = $booking['existing_refund'];
    $max_refundable = $total_paid - $existing_refund;

    if ($refund_amount > $max_refundable) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => "Refund amount (₱" . number_format($refund_amount, 2) . ") exceeds maximum refundable amount (₱" . number_format($max_refundable, 2) . ")"
        ]);
        exit;
    }

    // Check if already fully refunded
    if ($max_refundable <= 0) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'This booking has already been fully refunded']);
        exit;
    }

    // Update payment record with refund information
    $new_refund_total = $existing_refund + $refund_amount;
    $new_payment_status = ($new_refund_total >= $total_paid) ? 'refunded' : 'partial_refund';

    $updatePaymentStmt = $pdo->prepare("
        UPDATE tbl_payments 
        SET 
            refund_amount = ?,
            refund_date = NOW(),
            refund_reason = ?,
            status = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $updateResult = $updatePaymentStmt->execute([
        $new_refund_total,
        $refund_reason,
        $new_payment_status,
        $booking['payment_id']
    ]);

    if (!$updateResult) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to update payment record']);
        exit;
    }

    // Insert refund transaction record
    $refundStmt = $pdo->prepare("
        INSERT INTO tbl_refunds (
            booking_id, 
            payment_id, 
            refund_amount, 
            refund_reason, 
            refund_method, 
            processed_by, 
            status,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, 'processed', NOW())
    ");
    $refundStmt->execute([
        $booking_id,
        $booking['payment_id'],
        $refund_amount,
        $refund_reason,
        $booking['payment_method'], // Use same method as original payment
        $admin_username
    ]);

    // Log the refund action
    $logStmt = $pdo->prepare("
        INSERT INTO tbl_booking_logs (
            booking_id, 
            action, 
            description, 
            performed_by, 
            created_at
        ) VALUES (?, 'refund_processed', ?, ?, NOW())
    ");
    $logStmt->execute([
        $booking_id,
        "Refund of ₱" . number_format($refund_amount, 2) . " processed. Reason: " . $refund_reason,
        $admin_username
    ]);

    // Send refund notification to user
    sendRefundNotification($pdo, $booking_id, $booking['user_id'], $refund_amount, $booking['reference_id']);

    // If fully refunded, update booking status to refunded
    if ($new_payment_status === 'refunded') {
        $updateBookingStmt = $pdo->prepare("
            UPDATE tbl_bookings 
            SET status = 'refunded', updated_at = NOW(), updated_by = ?
            WHERE id = ?
        ");
        $updateBookingStmt->execute([$admin_username, $booking_id]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Refund of ₱' . number_format($refund_amount, 2) . ' processed successfully',
        'refund_amount' => $refund_amount,
        'total_refunded' => $new_refund_total,
        'remaining_refundable' => $max_refundable - $refund_amount
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error in process_refund.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("General error in process_refund.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred']);
}

// Helper function to send refund notification
function sendRefundNotification($pdo, $booking_id, $user_id, $refund_amount, $reference_id)
{
    try {
        // Get user information
        $userStmt = $pdo->prepare("SELECT email, first_name FROM tbl_users WHERE id = ?");
        $userStmt->execute([$user_id]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Insert notification record
            $notifStmt = $pdo->prepare("
                INSERT INTO tbl_notifications (
                    user_id, 
                    booking_id, 
                    type, 
                    title, 
                    message, 
                    created_at
                ) VALUES (?, ?, 'refund', ?, ?, NOW())
            ");

            $title = 'Refund Processed';
            $message = "A refund of ₱" . number_format($refund_amount, 2) . " has been processed for your booking #{$reference_id}. The refund will be credited to your original payment method within 3-5 business days.";

            $notifStmt->execute([$user_id, $booking_id, $title, $message]);

            // You can add email sending logic here
            // sendRefundEmail($user['email'], $user['first_name'], $refund_amount, $reference_id);
        }
    } catch (Exception $e) {
        error_log("Error sending refund notification: " . $e->getMessage());
    }
}

// Helper function to send refund email (implement as needed)
function sendRefundEmail($email, $first_name, $refund_amount, $reference_id)
{
    // Implement email sending logic here
    // This could use PHPMailer, mail(), or any other email service

    $subject = "Refund Processed - Booking #{$reference_id}";
    $message = "
        Dear {$first_name},
        
        Your refund request has been processed successfully.
        
        Booking Reference: #{$reference_id}
        Refund Amount: ₱" . number_format($refund_amount, 2) . "
        
        The refund will be credited to your original payment method within 3-5 business days.
        
        If you have any questions, please contact our support team.
        
        Thank you for choosing our services.
        
        Best regards,
        Reservify Team
    ";

    // Uncomment and configure based on your email setup
    // mail($email, $subject, $message);
}
?>