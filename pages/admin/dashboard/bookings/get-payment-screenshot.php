<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

// start session to check if user is admin
session_start();

// check if user is admin
if (!isset($_SESSION['admin_username'])) {
    http_response_code(403);
    exit('unauthorized');
}

// get parameters
$booking_id = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;
$thumbnail = isset($_GET['thumbnail']) && $_GET['thumbnail'] == '1';
$download = isset($_GET['download']) && $_GET['download'] == '1';

if ($booking_id <= 0) {
    http_response_code(400);
    exit('invalid booking ID');
}

try {
    // get PDO connection
    $pdo = Database::getConnection();

    // fetch payment screenshot path for the given booking
    $stmt = $pdo->prepare("
        SELECT payment_screenshot_path, payment_screenshot_thumbnail, screenshot_file_size
        FROM tbl_payments 
        WHERE booking_id = ? 
        AND payment_screenshot_path IS NOT NULL 
        AND payment_screenshot_path != ''
    ");

    $stmt->execute([$booking_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(404);
        exit('no payment screenshot found');
    }

    // determine which file to serve
    $filename = $thumbnail && $result['payment_screenshot_thumbnail']
        ? $result['payment_screenshot_thumbnail']
        : $result['payment_screenshot_path'];

    // construct full file path
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/uploads/payment-screenshots/';
    $filePath = $thumbnail
        ? $uploadDir . 'thumbnails/' . $filename
        : $uploadDir . $filename;

    // check if file exists
    if (!file_exists($filePath)) {
        http_response_code(404);
        exit('Screenshot file not found on server');
    }

    // get file info
    $fileInfo = getimagesize($filePath);
    if ($fileInfo === false) {
        http_response_code(400);
        exit('Invalid image file');
    }

    $mimeType = $fileInfo['mime'];
    $fileSize = filesize($filePath);

    // set headers
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . $fileSize);

    if ($download) {
        // force download
        header('Content-Disposition: attachment; filename="payment-screenshot-' . $booking_id . '.jpg"');
        header('Cache-Control: no-cache, must-revalidate');
    } else {
        // display inline
        header('Content-Disposition: inline; filename="payment-screenshot-' . $booking_id . '.jpg"');
        header('Cache-Control: public, max-age=86400'); // Cache for 24 hours

        // add etag for better caching
        $etag = md5_file($filePath);
        header('ETag: "' . $etag . '"');

        // check if client has cached version
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
            http_response_code(304);
            exit();
        }
    }

    // output the file
    readfile($filePath);

} catch (PDOException $e) {
    error_log("Database error in get-payment-screenshot.php: " . $e->getMessage());
    http_response_code(500);
    exit('database error occurred');
} catch (Exception $e) {
    error_log("error in get-payment-screenshot.php: " . $e->getMessage());
    http_response_code(500);
    exit('an error occurred while retrieving the screenshot');
}
?>