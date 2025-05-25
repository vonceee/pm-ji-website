<?php
// submit_cancellation.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_email'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['reference_id']) || empty($data['reason'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
use Config\Database;
$pdo = Database::getConnection();

// Get user and booking IDs
$userEmail = $_SESSION['user_email'];
$stmt = $pdo->prepare("SELECT u.id AS user_id, b.id AS booking_id, b.reference_number
    FROM tbl_users u
    JOIN tbl_bookings b ON b.user_id = u.id
    WHERE u.email = :email 
      AND b.reference_id = :ref
");
$stmt->execute([':email' => $userEmail, ':ref' => $data['reference_id']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Booking not found']);
    exit;
}

// Insert cancellation request
$ins = $pdo->prepare("INSERT INTO tbl_cancellation_requests
    (booking_id,user_id,reason) VALUES (:bid,:uid,:reason)");
$ins->execute([
    ':bid' => $row['booking_id'],
    ':uid' => $row['user_id'],
    ':reason' => $data['reason']
]);

// Send email to user
$to = $userEmail;
$subject = "Cancellation Request Received for {$row['reference_number']}";
$message = "Hi,\n\nWe’ve received your cancellation request for booking #{$row['reference_number']}.\nReason: {$data['reason']}\n\nOur admin team will review and let you know once it’s approved and refunded.\n\nThank you.";
$headers = "From: no-reply@yourdomain.com\r\n";
mail($to, $subject, $message, $headers);

echo json_encode(['status' => 'ok']);
