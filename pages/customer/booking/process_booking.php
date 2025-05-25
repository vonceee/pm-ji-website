<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

// database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

// fetch the shared PDO instance
$pdo = Database::getConnection();

// get the logged-in user's email from session
$user_email = $_SESSION['user_email'];

// get the corresponding user id from tbl_users
$stmt = $pdo->prepare("SELECT id FROM tbl_users WHERE email = :email");
$stmt->execute(['email' => $user_email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("user not found.");
}
$user_id = $user['id'];

// get booking details from POST request
$event_type = $_POST['event_type'];
$duration = $_POST['duration'];
$reservation_date = $_POST['reservation_date'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$street_address = $_POST['street_address'];
$barangay = $_POST['barangay_name'];
$city = $_POST['city_name'];
$full_address = $_POST['full_address'];
$reference_number = $_POST['reference_number'];
$payment_method = $_POST['payment_method'];
$payment_type = $_POST['payment_type'];
$reference_id = strtoupper(uniqid("REF-"));
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
$tmpPath = $_FILES['payment_screenshot']['tmp_name'];
$blobData = file_get_contents($tmpPath);

// insert booking query
$bookingSql = "INSERT INTO tbl_bookings
(reference_id, user_id, event_type, duration, reservation_date, start_time, end_time,
 street_address, barangay, city, full_address, reference_number, status)
VALUES
(:reference_id, :user_id, :event_type, :duration, :reservation_date, :start_time, :end_time,
 :street_address, :barangay, :city, :full_address, :reference_number, 'pending')";
$stmt = $pdo->prepare($bookingSql);
$stmt->execute([
    'reference_id' => $reference_id,
    'user_id' => $user_id,
    'event_type' => $event_type,
    'duration' => $duration,
    'reservation_date' => $reservation_date,
    'start_time' => $start_time,
    'end_time' => $end_time,
    'street_address' => $street_address,
    'barangay' => $barangay,
    'city' => $city,
    'full_address' => $full_address,
    'reference_number' => $reference_number
]);
$booking_id = $pdo->lastInsertId();

// calculate payment balances
$full_price = isset($_POST['full_price']) ? floatval($_POST['full_price']) : $price;
$amount_paid = $price;
if (strtolower($payment_type) === 'down payment') {
    $balance = $full_price - $amount_paid;
    $status = ($balance > 0) ? 'partial' : 'paid';
} else {
    $balance = 0;
    $status = 'paid';
}
$payment_date = date('Y-m-d');

// insert payment query
$paymentSql = "INSERT INTO tbl_payments
(booking_id, amount_paid, balance, payment_method, payment_type, payment_screenshot, status, payment_date)
VALUES
(:booking_id, :amount_paid, :balance, :payment_method, :payment_type, :payment_screenshot, :status, :payment_date)";
$stmt = $pdo->prepare($paymentSql);
$stmt->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);
$stmt->bindValue(':amount_paid', $amount_paid);
$stmt->bindValue(':balance', $balance);
$stmt->bindValue(':payment_method', $payment_method);
$stmt->bindValue(':payment_type', $payment_type);
$stmt->bindValue(':payment_screenshot', $blobData, PDO::PARAM_LOB);
$stmt->bindValue(':status', $status);
$stmt->bindValue(':payment_date', $payment_date);
$stmt->execute();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer Vendor
require $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/vendor/autoload.php';

$_SESSION['booking_reference_id'] = $reference_id;

// send confirmation email
$mail = new PHPMailer(true);

try {
    // server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'skypemain01@gmail.com';
    $mail->Password = 'nxkt whiw tlft udhl';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // recipients
    $mail->setFrom('skypemain01@gmail.com', 'PM&JI Reservify');
    $mail->addAddress($user_email);

    // content
    $mail->isHTML(true);
    $mail->Subject = 'Booking Confirmation - PM&JI Reservify';
    $mail->Body = "
        <h2>Booking Sent!</h2>
        <p>Thank you for booking with PM&JI Reservify. Your booking is being processed.</p>
        <p>Expect to hear from us within 3-4 Hours</p>
        <h4>Booking Details:</h4>
        <ul>
            <li><strong>Reference ID:</strong> $reference_id</li>
            <li><strong>Event Type:</strong> $event_type</li>
            <li><strong>Date:</strong> $reservation_date</li>
            <li><strong>Time Slot:</strong> $start_time - $end_time</li>
            <li><strong>Location:</strong> $street_address, $barangay, $city</li>
            <li><strong>Full Address:</strong> $full_address</li>
            <li><strong>Payment Type:</strong> $payment_type</li>
            <li><strong>Price:</strong> $price</li>
        </ul>
        <p>If you have any concerns, please contact us and provide your Reference ID.</p>
    ";

    // send the email
    $mail->send();
} catch (Exception $e) {
    error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
}

// redirect to the success page
header("Location: /NEW-PM-JI-RESERVIFY/pages/customer/booking_success.php");
exit();
