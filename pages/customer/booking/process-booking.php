<?php
session_start();

// add console log helper function (stores logs in session to display later)
function console_log($data, $label = '')
{
    if (!isset($_SESSION['debug_logs'])) {
        $_SESSION['debug_logs'] = [];
    }
    $output = $label ? "[$label] " : '';
    $output .= is_array($data) || is_object($data) ? json_encode($data) : $data;
    $_SESSION['debug_logs'][] = $output;
}

console_log("=== BOOKING PROCESS STARTED ===");
console_log($_SESSION, "SESSION DATA");
console_log($_POST, "POST DATA");
console_log($_FILES, "FILES DATA");

if (!isset($_SESSION['user_email'])) {
    console_log("No user email in session - redirecting to login");
    header("Location: index.php");
    exit();
}

// database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
// include the ImageUploadHandler class
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/classes/ImageUploadHandler.php';

use Config\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// fetch the shared PDO instance
$pdo = Database::getConnection();
console_log("Database connection established");

// get the logged-in user's email from session
$user_email = $_SESSION['user_email'];
console_log($user_email, "USER EMAIL");

// get the corresponding user id from tbl_users
$stmt = $pdo->prepare("SELECT id FROM tbl_users WHERE email = :email");
$stmt->execute(['email' => $user_email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    console_log("User not found in database");
    die("user not found.");
}
$user_id = $user['id'];
console_log($user_id, "USER ID");

// validate and get booking details from POST request
$required_fields = [
    'event_type',
    'duration',
    'reservation_date',
    'start_time',
    'end_time',
    'street_address',
    'barangay_name',
    'city_name',
    'full_address',
    'reference_number',
    'payment_method',
    'payment_type'
];

$missing_fields = [];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    console_log($missing_fields, "MISSING REQUIRED FIELDS");
    $_SESSION['booking_error'] = "Missing required fields: " . implode(', ', $missing_fields);
    exit();
}

// get booking details from POST request (with validation)
$event_type = $_POST['event_type'] ?? '';
$duration = $_POST['duration'] ?? '';
$reservation_date = $_POST['reservation_date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';
$street_address = $_POST['street_address'] ?? '';
$barangay = $_POST['barangay_name'] ?? '';
$city = $_POST['city_name'] ?? '';
$full_address = $_POST['full_address'] ?? '';
$reference_number = $_POST['reference_number'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';
$payment_type = $_POST['payment_type'] ?? '';
$reference_id = strtoupper(uniqid("REF-"));



console_log([
    'event_type' => $event_type,
    'duration' => $duration,
    'reservation_date' => $reservation_date,
    'start_time' => $start_time,
    'end_time' => $end_time,
    'street_address' => $street_address,
    'barangay' => $barangay,
    'city' => $city,
    'full_address' => $full_address,
    'reference_number' => $reference_number,
    'payment_method' => $payment_method,
    'payment_type' => $payment_type,
    'reference_id' => $reference_id,
    'price' => $price
], "BOOKING DETAILS");

// begin transaction for data consistency
console_log("starting database transaction");
$pdo->beginTransaction();

try {
    console_log("=== INSERTING BOOKING DATA ===");

    // insert booking query
    $bookingSql = "INSERT INTO tbl_bookings
    (reference_id, user_id, event_type, duration, reservation_date, start_time, end_time,
     street_address, barangay, city, full_address, reference_number, status)
    VALUES
    (:reference_id, :user_id, :event_type, :duration, :reservation_date, :start_time, :end_time,
     :street_address, :barangay, :city, :full_address, :reference_number, 'pending')";

    console_log($bookingSql, "BOOKING SQL QUERY");

    $stmt = $pdo->prepare($bookingSql);
    $bookingParams = [
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
    ];

    console_log($bookingParams, "BOOKING PARAMETERS");

    $stmt->execute($bookingParams);
    $booking_id = $pdo->lastInsertId();

    console_log($booking_id, "BOOKING ID CREATED");

    console_log("=== HANDLING IMAGE UPLOAD ===");

    // handle payment screenshot upload using ImageUploadHandler
    $imageHandler = new ImageUploadHandler();
    console_log("ImageUploadHandler instantiated");

    $uploadResult = $imageHandler->handlePaymentScreenshotUpload($_FILES, $booking_id);
    console_log($uploadResult, "UPLOAD RESULT");

    if (!$uploadResult['success']) {
        console_log("Image upload failed: " . $uploadResult['error']);
        throw new Exception('Failed to upload payment screenshot: ' . $uploadResult['error']);
    }

    console_log("=== CALCULATING PAYMENT BALANCES ===");

    // calculate payment balances
    $full_price = isset($_POST['full_price']) ? floatval($_POST['full_price']) : $price;
    $amount_paid = $price;

    console_log([
        'full_price' => $full_price,
        'amount_paid' => $amount_paid,
        'payment_type' => $payment_type
    ], "PAYMENT CALCULATION INPUT");

    $basePrices = [
        'Baptism' => 4500,
        'Birthday' => 4000,
        'Corporate Event' => 7000,
        'Reunion' => 5000,
        'Wedding' => 5000
    ];

    $durationOverrides = [
        4 => [
            'Baptism' => 4600,
            'Birthday' => 4500,
            'Corporate Event' => 8000,
            'Reunion' => 6500,
            'Wedding' => 11000
        ]
    ];

    // Calculate full price using the same logic as frontend
    $full_price = 0;
    $duration_int = intval($duration);

    if (isset($durationOverrides[$duration_int]) && isset($durationOverrides[$duration_int][$event_type])) {
        $full_price = $durationOverrides[$duration_int][$event_type];
    } elseif (isset($basePrices[$event_type])) {
        $full_price = $basePrices[$event_type];
    }

    // Calculate amount to be paid based on payment type
    if (strtolower($payment_type) === 'down payment') {
        $amount_paid = $full_price / 2;
        $balance = $full_price - $amount_paid;
        $status = 'Partial';
    } else {
        $amount_paid = $full_price;
        $balance = 0;
        $status = 'Paid';
    }

    console_log([
        'event_type' => $event_type,
        'duration' => $duration,
        'duration_int' => $duration_int,
        'full_price_calculated' => $full_price,
        'payment_type' => $payment_type,
        'amount_paid' => $amount_paid,
        'balance' => $balance,
        'status' => $status
    ], "FIXED PAYMENT CALCULATION");

    $payment_date = date('Y-m-d');

    console_log([
        'balance' => $balance,
        'status' => $status,
        'payment_date' => $payment_date
    ], "PAYMENT CALCULATION RESULT");

    console_log("=== INSERTING PAYMENT DATA ===");

    // insert payment query with file path instead of blob
    $paymentSql = "INSERT INTO tbl_payments
    (booking_id, amount_paid, balance, payment_method, payment_type, payment_screenshot_path, payment_screenshot_thumbnail, status, payment_date)
    VALUES
    (:booking_id, :amount_paid, :balance, :payment_method, :payment_type, :payment_screenshot_path, :payment_screenshot_thumbnail, :status, :payment_date)";

    console_log($paymentSql, "PAYMENT SQL QUERY");

    $stmt = $pdo->prepare($paymentSql);

    $paymentParams = [
        'booking_id' => $booking_id,
        'amount_paid' => $amount_paid,
        'balance' => $balance,
        'payment_method' => $payment_method,
        'payment_type' => $payment_type,
        'payment_screenshot_path' => $uploadResult['filename'],
        'payment_screenshot_thumbnail' => $uploadResult['thumbnail'],
        'status' => $status,
        'payment_date' => $payment_date
    ];

    console_log($paymentParams, "PAYMENT PARAMETERS");

    $stmt->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->bindValue(':amount_paid', $amount_paid);
    $stmt->bindValue(':balance', $balance);
    $stmt->bindValue(':payment_method', $payment_method);
    $stmt->bindValue(':payment_type', $payment_type);
    $stmt->bindValue(':payment_screenshot_path', $uploadResult['filename']);
    $stmt->bindValue(':payment_screenshot_thumbnail', $uploadResult['thumbnail']);
    $stmt->bindValue(':status', $status);
    $stmt->bindValue(':payment_date', $payment_date);
    $stmt->execute();

    console_log("payment data inserted successfully");

    // commit the transaction
    $pdo->commit();
    console_log("database transaction committed successfully");

    console_log("=== SENDING EMAIL ===");

    // PHPMailer Vendor
    require $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/vendor/autoload.php';

    $_SESSION['booking_reference_id'] = $reference_id;
    console_log($reference_id, "REFERENCE ID SET IN SESSION");

    // send confirmation email
    $mail = new PHPMailer(true);

    try {
        console_log("Configuring PHPMailer settings");

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

        console_log($user_email, "EMAIL RECIPIENT");

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

        console_log("email content prepared");

        // send the email
        $mail->send();
        console_log("email sent successfully");

    } catch (PHPMailerException $e) {
        console_log("email sending failed: " . $mail->ErrorInfo);
        error_log("email could not be sent. Error: {$mail->ErrorInfo}");
    }

    console_log("=== BOOKING PROCESS COMPLETED SUCCESSFULLY ===");
    console_log("redirecting to success page");

    // store debug logs in session for display on success page
    $_SESSION['show_debug_logs'] = true;

    // redirect to the success page
    header("Location: /NEW-PM-JI-RESERVIFY/pages/customer/booking/components/successful-booking/index.php");
    exit();

} catch (Exception $e) {
    console_log("=== ERROR OCCURRED ===");
    console_log("Error message: " . $e->getMessage());
    console_log("Error trace: " . $e->getTraceAsString());

    // rollback the transaction on error
    $pdo->rollback();
    console_log("database transaction rolled back");

    // if booking was created but payment failed, clean up uploaded files
    if (isset($uploadResult) && $uploadResult['success']) {
        console_log("cleaning up uploaded files");
        $imageHandler->deleteImageFiles($uploadResult['filename']);
    }

    // log the error
    error_log("booking processing failed: " . $e->getMessage());

    // redirect to an error page or show an error message
    $_SESSION['booking_error'] = "failed to process booking: " . $e->getMessage();
    console_log("error message set in session, redirecting to error page");
    exit();
}
?>