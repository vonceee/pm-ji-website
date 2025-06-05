<?php
/**
 * cancel_booking.php
 * 
 * API endpoint to handle booking cancellations
 * Path: /NEW-PM-JI-RESERVIFY/pages/customer/actions/cancel_booking.php
 */

header('Content-Type: application/json');

// start session and check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_email'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['booking_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
use Config\Database;

try {
    $pdo = Database::getConnection();
    $userEmail = $_SESSION['user_email'];

    // get user ID
    $stmtUser = $pdo->prepare('SELECT id FROM tbl_users WHERE email = :email');
    $stmtUser->execute([':email' => $userEmail]);
    $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$userRow) {
        throw new Exception('User not found');
    }

    $userId = (int) $userRow['id'];
    $bookingId = (int) $input['booking_id'];
    $reason = isset($input['reason']) ? trim($input['reason']) : '';

    // start transaction
    $pdo->beginTransaction();

    // verify booking belongs to user and can be cancelled
    $stmtBooking = $pdo->prepare('
        SELECT id, status, reservation_date, event_type, reference_number 
        FROM tbl_bookings 
        WHERE id = :booking_id AND user_id = :user_id
    ');

    $stmtBooking->execute([
        ':booking_id' => $bookingId,
        ':user_id' => $userId
    ]);

    $booking = $stmtBooking->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception('Booking not found or access denied');
    }

    // check if booking can be cancelled
    $cancelableStatuses = ['approved', 'confirmed', 'pending'];
    if (!in_array(strtolower($booking['status']), $cancelableStatuses)) {
        throw new Exception('This booking cannot be cancelled');
    }

    // check if booking is at least 24 hours in the future
    $bookingDateTime = strtotime($booking['reservation_date']);
    $currentTime = time();
    $timeDifference = $bookingDateTime - $currentTime;

    if ($timeDifference < 86400) { // 24 hours in seconds
        throw new Exception('Bookings can only be cancelled at least 24 hours in advance');
    }

    // update booking status to cancelled
    $stmtUpdateBooking = $pdo->prepare('
        UPDATE tbl_bookings 
        SET status = "cancelled_by_user", 
            updated_at = NOW() 
        WHERE id = :booking_id
    ');

    $stmtUpdateBooking->execute([':booking_id' => $bookingId]);

    // insert cancellation record
    $stmtCancellation = $pdo->prepare('
        INSERT INTO tbl_cancellations (
            booking_id, 
            user_id, 
            reason, 
            refund_status,
            refund_amount,
            cancelled_at
        ) VALUES (
            :booking_id, 
            :user_id, 
            :reason, 
            "pending",
            NULL,
            NOW()
        )
    ');

    $stmtCancellation->execute([
        ':booking_id' => $bookingId,
        ':user_id' => $userId,
        ':reason' => $reason
    ]);

    // get payment info for potential refund calculation
    $stmtPayment = $pdo->prepare('
        SELECT amount_paid, payment_method, payment_type 
        FROM tbl_payments 
        WHERE booking_id = :booking_id
    ');

    $stmtPayment->execute([':booking_id' => $bookingId]);
    $payment = $stmtPayment->fetch(PDO::FETCH_ASSOC);

    // if there's a payment, update the cancellation with potential refund amount
    if ($payment && $payment['amount_paid'] > 0) {
        // calculate refund amount (you can implement your own logic here)
        // 100% refund if cancelled more than 48 hours in advance
        // 80% refund if cancelled less than 48 hours in advance
        $refundPercentage = ($timeDifference >= 172800) ? 1.0 : 0.8; // 48 hours = 172800 seconds
        $refundAmount = $payment['amount_paid'] * $refundPercentage;

        $stmtUpdateRefund = $pdo->prepare('
            UPDATE tbl_cancellations 
            SET refund_amount = :refund_amount 
            WHERE booking_id = :booking_id
        ');

        $stmtUpdateRefund->execute([
            ':refund_amount' => $refundAmount,
            ':booking_id' => $bookingId
        ]);
    }

    // commit transaction
    $pdo->commit();

    // send success response
    echo json_encode([
        'success' => true,
        'message' => 'Booking cancelled successfully',
        'data' => [
            'booking_id' => $bookingId,
            'reference_number' => $booking['reference_number'],
            'refund_amount' => isset($refundAmount) ? $refundAmount : 0,
            'refund_status' => 'pending'
        ]
    ]);

} catch (Exception $e) {
    // rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

} catch (PDOException $e) {
    // rollback transaction on database error
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }

    error_log('Database error in cancel_booking.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'A database error occurred. Please try again later.'
    ]);
}
?>