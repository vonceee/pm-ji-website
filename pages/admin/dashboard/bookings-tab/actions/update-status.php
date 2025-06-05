<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

// set content type to JSON
header('Content-Type: application/json');

// check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized Access']);
    exit;
}

// check if request is POST and has JSON content
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'method not allowed']);
    exit;
}

// get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// validate required fields
if (!isset($input['booking_id'], $input['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'missing required fields']);
    exit;
}

$bookingId = (int) $input['booking_id'];
$newStatus = trim($input['status']);
$adminNotes = isset($input['admin_notes']) ? trim($input['admin_notes']) : '';
$cancellationReason = isset($input['cancellation_reason']) ? trim($input['cancellation_reason']) : '';

// validate status
$validStatuses = ['pending', 'approved', 'completed', 'cancelled'];
if (!in_array($newStatus, $validStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// validate cancellation reason if status is cancelled
if ($newStatus === 'cancelled' && empty($cancellationReason)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cancellation reason is required']);
    exit;
}

try {
    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    // get current booking details
    $stmt = $pdo->prepare("
        SELECT b.*, u.first_name, u.last_name, u.email, p.payment_method, p.amount_paid, p.balance
        FROM tbl_bookings b
        LEFT JOIN tbl_users u ON b.user_id = u.id
        LEFT JOIN tbl_payments p ON b.id = p.booking_id
        WHERE b.id = ?
    ");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception('Booking not found');
    }

    // check if status change is valid
    $currentStatus = $booking['status'];
    $validTransitions = [
        'pending' => ['approved', 'cancelled'],
        'approved' => ['completed', 'cancelled', 'pending'],
        'completed' => [],
        'cancelled' => ['pending']
    ];

    if (!empty($validTransitions[$currentStatus]) && !in_array($newStatus, $validTransitions[$currentStatus])) {
        throw new Exception("cannot change status from {$currentStatus} to {$newStatus}");
    }

    // update booking status
    $updateStmt = $pdo->prepare("
        UPDATE tbl_bookings 
        SET status = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    $updateStmt->execute([$newStatus, $bookingId]);

    // add admin note if provided
    if (!empty($adminNotes)) {
        $noteStmt = $pdo->prepare("
            INSERT INTO tbl_booking_notes (booking_id, note_type, note_text, created_by, created_at)
            VALUES (?, 'admin', ?, ?, CURRENT_TIMESTAMP)
        ");
        $noteStmt->execute([$bookingId, $adminNotes, $_SESSION['admin_username']]);
    }

    // handle payment status updates based on booking status
    if ($newStatus === 'approved') {
        // when booking is approved, ensure payment status is updated if needed
        $paymentUpdateStmt = $pdo->prepare("
            UPDATE tbl_payments 
            SET status = CASE 
                WHEN amount_paid >= (amount_paid + balance) THEN 'paid'
                WHEN amount_paid > 0 THEN 'partial'
                ELSE 'pending'
            END
            WHERE booking_id = ? AND status = 'pending'
        ");
        $paymentUpdateStmt->execute([$bookingId]);
    } elseif ($newStatus === 'cancelled') {
        // handle cancellation logic
        $amountPaid = (float) ($booking['amount_paid'] ?? 0);
        
        // insert into tbl_cancellations - FIXED: Correct parameter order
        $cancellationStmt = $pdo->prepare("
            INSERT INTO tbl_cancellations (
                booking_id, 
                user_id, 
                reason, 
                cancelled_at, 
                refund_status, 
                refund_amount, 
                admin_notes
            ) VALUES (?, ?, ?, CURRENT_TIMESTAMP, ?, ?, ?)
        ");
        
        // determine refund status and amount
        $refundStatus = $amountPaid > 0 ? 'pending' : 'refunded';
        $refundAmount = $amountPaid > 0 ? $amountPaid : null;
        
        // FIXED: Correct parameter order matching the SQL statement
        $cancellationStmt->execute([
            $bookingId,                                    // booking_id
            $booking['user_id'],                          // user_id  
            $cancellationReason,                          // reason
            $refundStatus,                                // refund_status
            $refundAmount,                                // refund_amount
            $adminNotes ?: "Booking cancelled by admin"   // admin_notes
        ]);
        
        // update payment status if there was a payment
        if ($amountPaid > 0) {
            $paymentStmt = $pdo->prepare("
                UPDATE tbl_payments 
                SET status = 'paid', 
                    refund_date = CURRENT_TIMESTAMP, 
                    refund_amount = amount_paid
                WHERE booking_id = ?
            ");
            $paymentStmt->execute([$bookingId]);
        }
    }

    // log the status change
    $logStmt = $pdo->prepare("
        INSERT INTO tbl_booking_logs (booking_id, old_status, new_status, changed_by, change_reason, created_at)
        VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
    ");
    
    $changeReason = $newStatus === 'cancelled' && !empty($cancellationReason) 
        ? "Cancelled: " . $cancellationReason 
        : ($adminNotes ?: "Status changed from {$currentStatus} to {$newStatus}");
        
    $logStmt->execute([
        $bookingId,
        $currentStatus,
        $newStatus,
        $_SESSION['admin_username'],
        $changeReason
    ]);

    $pdo->commit();

    // send email notification to customer
    sendStatusUpdateEmail($booking, $newStatus, $cancellationReason);

    // set success message for session
    $statusMessages = [
        'approved' => 'Booking has been Approved Successfully!',
        'cancelled' => 'Booking has been Cancelled and refund has been processed.',
        'completed' => 'Booking has been marked as Completed.',
        'pending' => 'Booking has been moved back to Pending.'
    ];

    $_SESSION['success_message'] = $statusMessages[$newStatus];

    echo json_encode([
        'success' => true,
        'message' => $statusMessages[$newStatus],
        'booking_id' => $bookingId,
        'new_status' => $newStatus
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Booking status update error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * send email notification to customer about status change
 */
function sendStatusUpdateEmail($booking, $newStatus, $cancellationReason = '')
{
    if (empty($booking['email'])) {
        return;
    }

    $customerName = $booking['first_name'] . ' ' . $booking['last_name'];
    $referenceId = $booking['reference_id'];
    $eventType = $booking['event_type'];
    $reservationDate = date('F j, Y', strtotime($booking['reservation_date']));
    $startTime = $booking['start_time'];

    // email templates based on status
    $emailTemplates = [
        'approved' => [
            'subject' => "Booking Approved - #{$referenceId}",
            'message' => "
                <h2>Booking Approved!</h2>
                <p>Dear {$customerName},</p>
                <p>Great news! Your booking has been approved.</p>
                <p><strong>Booking Details:</strong></p>
                <ul>
                    <li>Reference ID: #{$referenceId}</li>
                    <li>Event Type: {$eventType}</li>
                    <li>Date: {$reservationDate}</li>
                    <li>Time: {$startTime}</li>
                </ul>
                <p>We will contact you soon with further details.</p>
                <p>Thank you for choosing our services!</p>
            "
        ],
        'cancelled' => [
            'subject' => "Booking Cancelled - #{$referenceId}",
            'message' => "
                <h2>Booking Cancelled</h2>
                <p>Dear {$customerName},</p>
                <p>We regret to inform you that your booking has been cancelled.</p>
                <p><strong>Booking Details:</strong></p>
                <ul>
                    <li>Reference ID: #{$referenceId}</li>
                    <li>Event Type: {$eventType}</li>
                    <li>Date: {$reservationDate}</li>
                </ul>
                " . (!empty($cancellationReason) ? "<p><strong>Reason:</strong> {$cancellationReason}</p>" : "") . "
                <p>If you made a payment, a full refund will be processed within 3-5 business days.</p>
                <p>If you have any questions, please contact us.</p>
            "
        ],
        'completed' => [
            'subject' => "Event Completed - #{$referenceId}",
            'message' => "
                <h2>Event Completed Successfully!</h2>
                <p>Dear {$customerName},</p>
                <p>Your event has been completed successfully. We hope you had a wonderful experience!</p>
                <p><strong>Event Details:</strong></p>
                <ul>
                    <li>Reference ID: #{$referenceId}</li>
                    <li>Event Type: {$eventType}</li>
                    <li>Date: {$reservationDate}</li>
                </ul>
                <p>Thank you for choosing our services. We'd love to hear your feedback!</p>
            "
        ]
    ];

    if (!isset($emailTemplates[$newStatus])) {
        return;
    }

    $template = $emailTemplates[$newStatus];

    // set email headers
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Reservify <noreply@reservify.com>',
        'Reply-To: support@reservify.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    // send email
    $success = mail(
        $booking['email'],
        $template['subject'],
        $template['message'],
        implode("\r\n", $headers)
    );

    if (!$success) {
        error_log("failed to send email to {$booking['email']} for booking {$referenceId}");
    }
}
?>