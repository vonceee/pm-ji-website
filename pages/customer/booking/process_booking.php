<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "db_pmji";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// get the logged-in user's email from session
$user_email = $_SESSION['user_email'];

// get the corresponding user id from tbl_users
$userIdQuery = "SELECT id FROM tbl_users WHERE email = ?";
$stmt = $conn->prepare($userIdQuery);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    die("User not found.");
}
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

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

// process file upload for the payment screenshot
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] == 0) {
    // sanitize file name and generate a unique name to avoid overwrites.
    $fileName = basename($_FILES["payment_screenshot"]["name"]);
    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid("payment_", true) . "." . $fileExt;
    $uploadFilePath = $uploadDir . $newFileName;

    // check allowed file types (optional)
    $allowed = array("jpg", "jpeg", "png");
    if (!in_array(strtolower($fileExt), $allowed)) {
        die("Error: Only JPG, JPEG, PNG");
    }

    if (!move_uploaded_file($_FILES["payment_screenshot"]["tmp_name"], $uploadFilePath)) {
        die("Error uploading file.");
    }
} else {
    die("Error: Payment screenshot file is required.");
}

// Prepare the insert query for tbl_bookings
$query = "INSERT INTO tbl_bookings 
            (user_id, reference_id, event_type, duration, reservation_date, start_time, end_time, street_address, barangay, city, full_address, reference_number, payment_method, payment_type, payment_screenshot, price)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param(
    "issssssssssssssd",
    $user_id,
    $reference_id,
    $event_type,
    $duration,
    $reservation_date,
    $start_time,
    $end_time,
    $street_address,
    $barangay,
    $city,
    $full_address,
    $reference_number,
    $payment_method,
    $payment_type,
    $newFileName,
    $price
);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer
require $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/vendor/autoload.php';

if ($stmt->execute()) {
    $_SESSION['booking_reference_id'] = $reference_id;

    // Send confirmation email
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'skypemain01@gmail.com';
        $mail->Password = 'nxkt whiw tlft udhl';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('skypemain01@gmail.com', 'PM&JI Reservify');
        $mail->addAddress($user_email);

        // Content
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

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        // Log the error or display a message
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
    }

    // Redirect to the success page
    header("Location: /NEW-PM-JI-RESERVIFY/pages/customer/booking_success.php");
    exit();
} else {
    echo "Error: " . $stmt->error;
}