<?php
session_start();

// check if the user is logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: /NEW-PM-JI-RESERVIFY/index.php");
    exit();
}

// retrieve booking details from the session or database (if applicable)
$bookingDetails = isset($_SESSION['booking_details']) ? $_SESSION['booking_details'] : null;
$referenceId = isset($_SESSION['booking_reference_id']) ? $_SESSION['booking_reference_id'] : null;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Success - PM&JI Reservify</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/booking.css">
    <link rel="stylesheet"
        href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/successful-booking/successful-booking.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/navigation-bar/index.php'; ?>

    <div class="animated-bg"></div>

    <div class="container-content mt-5">
        <div class="card text-center">
            <div class="card-header text-white">
                <h2>Booking Request Sent!</h2>
            </div>
            <div class="card-body">
                <img src="/NEW-PM-JI-RESERVIFY/assets/success.gif" alt="Success" class="mt"
                    style="width: 100px; height: auto;">
                <p class="card-text">Thank you for booking with PM&JI Reservify!</p>
                <?php if ($referenceId): ?>
                    <h5 class="mt-2">Reference ID:</h5>
                    <p class="text-primary font-weight-bold"><?= htmlspecialchars($referenceId) ?></p>
                <?php endif; ?>

                <p class="card-text" style="font-size: 1rem;">A confirmation email has been sent to your registered
                    email address.</p>
            </div>
            <div class="card-footer text-muted">
                Booking is being processed. Expect to hear from us within 3-4 hours. <a
                    href="/NEW-PM-JI-RESERVIFY/pages/customer/views/dashboard.php"
                    style="color: var(--primary-color);">Click here to View Status!</a>
            </div>
        </div>
    </div>
</body>

</html>