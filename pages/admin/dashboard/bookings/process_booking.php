<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in as admin
if (!isset($_SESSION['admin_username'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get PDO connection
$pdo = Database::getConnection();

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'update_status':
            handleStatusUpdate($pdo);
            break;

        case 'process_refund':
            handleRefund($pdo);
            break;

        case 'add_note':
            handleAddNote($pdo);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }
} catch (Exception $e) {
    error_log("Booking processing error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}

/**
 * Handle booking status updates
 */
function handleStatusUpdate($pdo)
{
    $bookingId = $_POST['booking_id'] ?? 0;
    $status = $_POST['status'] ?? '';

    // Validate inputs
    if (!$bookingId || !in_array($status, ['pending', 'approved', 'cancelled', 'completed'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID or status']);
        return;
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Check if booking exists
        $checkStmt = $pdo->prepare("SELECT id, status, user_id FROM tbl_bookings WHERE id = ?");
        $checkStmt->execute([$bookingId]);
        $booking = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Update booking status
        $updateStmt = $pdo->prepare("
            UPDATE tbl_bookings 
            SET status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $updateStmt->execute([$status, $bookingId]);

        // Log the status change
        $logStmt = $pdo->prepare("
            INSERT INTO tbl_booking_logs (booking_id, old_status, new_status, changed_by, changed_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $logStmt->execute([$bookingId, $booking['status'], $status, $_SESSION['admin_username']]);

        // Send notification email to customer (optional)
        if ($status === 'approved') {
            sendBookingApprovalEmail($pdo, $bookingId);
        } elseif ($status === 'cancelled') {
            sendBookingCancellationEmail($pdo, $bookingId);
        }

        $pdo->commit();

        $statusMessages = [
            'approved' => 'Booking approved successfully',
            'cancelled' => 'Booking cancelled successfully',
            'completed' => 'Booking marked as completed',
            'pending' => 'Booking moved to pending status'
        ];

        echo json_encode([
            'success' => true,
            'message' => $statusMessages[$status] ?? 'Status updated successfully'
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Handle refund processing
 */
function handleRefund($pdo)
{
    $bookingId = $_POST['booking_id'] ?? 0;
    $refundAmount = $_POST['refund_amount'] ?? 0;

    // Validate inputs
    if (!$bookingId || !$refundAmount || $refundAmount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID or refund amount']);
        return;
    }

    $pdo->beginTransaction();

    try {
        // Check if booking exists and is completed
        $checkStmt = $pdo->prepare("
            SELECT b.*, p.amount_paid, p.refund_amount 
            FROM tbl_bookings b
            LEFT JOIN tbl_payments p ON b.id = p.booking_id
            WHERE b.id = ? AND b.status = 'completed'
        ");
        $checkStmt->execute([$bookingId]);
        $booking = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            throw new Exception('Booking not found or not eligible for refund');
        }

        // Check if refund amount is valid
        $totalPaid = $booking['amount_paid'] ?? 0;
        $previousRefund = $booking['refund_amount'] ?? 0;
        $availableForRefund = $totalPaid - $previousRefund;

        if ($refundAmount > $availableForRefund) {
            throw new Exception('Refund amount exceeds available balance');
        }

        // Update payment record with refund
        $updatePaymentStmt = $pdo->prepare("
            UPDATE tbl_payments 
            SET refund_amount = COALESCE(refund_amount, 0) + ?, 
                refund_date = NOW(),
                updated_at = NOW()
            WHERE booking_id = ?
        ");
        $updatePaymentStmt->execute([$refundAmount, $bookingId]);

        // Log the refund
        $logStmt = $pdo->prepare("
            INSERT INTO tbl_booking_logs (booking_id, action, amount, notes, changed_by, changed_at)
            VALUES (?, 'refund', ?, 'Refund processed', ?, NOW())
        ");
        $logStmt->execute([$bookingId, $refundAmount, $_SESSION['admin_username']]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Refund of ₱' . number_format($refundAmount, 2) . ' processed successfully'
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Handle adding notes to bookings
 */
function handleAddNote($pdo)
{
    $bookingId = $_POST['booking_id'] ?? 0;
    $note = trim($_POST['note'] ?? '');

    // Validate inputs
    if (!$bookingId || empty($note)) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID or note']);
        return;
    }

    try {
        // Check if booking exists
        $checkStmt = $pdo->prepare("SELECT id FROM tbl_bookings WHERE id = ?");
        $checkStmt->execute([$bookingId]);

        if (!$checkStmt->fetch()) {
            throw new Exception('Booking not found');
        }

        // Add note to booking_notes table (create this table if it doesn't exist)
        $noteStmt = $pdo->prepare("
            INSERT INTO tbl_booking_notes (booking_id, note, created_by, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $noteStmt->execute([$bookingId, $note, $_SESSION['admin_username']]);

        echo json_encode(['success' => true, 'message' => 'Note added successfully']);

    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Send booking approval email to customer
 */
function sendBookingApprovalEmail($pdo, $bookingId)
{
    // Get booking and customer details
    $stmt = $pdo->prepare("
        SELECT b.*, u.email, u.first_name, u.last_name
        FROM tbl_bookings b
        JOIN tbl_users u ON b.user_id = u.id
        WHERE b.id = ?
    ");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking && $booking['email']) {
        $to = $booking['email'];
        $subject = "Booking Approved - Reference #" . $booking['reference_id'];
        $message = "
            Dear {$booking['first_name']},
            
            Great news! Your booking has been approved.
            
            Booking Details:
            - Reference ID: #{$booking['reference_id']}
            - Event Type: {$booking['event_type']}
            - Date: " . date('F d, Y', strtotime($booking['reservation_date'])) . "
            - Time: {$booking['start_time']} - {$booking['end_time']}
            
            We look forward to serving you!
            
            Best regards,
            Reservify Team
        ";

        $headers = "From: noreply@reservify.com\r\n";
        $headers .= "Reply-To: support@reservify.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // Note: You might want to use a proper email library like PHPMailer for production
        @mail($to, $subject, $message, $headers);
    }
}

/**
 * Send booking cancellation email to customer
 */
function sendBookingCancellationEmail($pdo, $bookingId)
{
    // Get booking and customer details
    $stmt = $pdo->prepare("
        SELECT b.*, u.email, u.first_name, u.last_name
        FROM tbl_bookings b
        JOIN tbl_users u ON b.user_id = u.id
        WHERE b.id = ?
    ");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking && $booking['email']) {
        $to = $booking['email'];
        $subject = "Booking Cancelled - Reference #" . $booking['reference_id'];
        $message = "
            Dear {$booking['first_name']},
            
            We regret to inform you that your booking has been cancelled.
            
            Booking Details:
            - Reference ID: #{$booking['reference_id']}
            - Event Type: {$booking['event_type']}
            - Date: " . date('F d, Y', strtotime($booking['reservation_date'])) . "
            - Time: {$booking['start_time']} - {$booking['end_time']}
            
            If you have any questions, please contact our support team.
            
            Best regards,
            Reservify Team
        ";

        $headers = "From: noreply@reservify.com\r\n";
        $headers .= "Reply-To: support@reservify.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        @mail($to, $subject, $message, $headers);
    }
}
?>