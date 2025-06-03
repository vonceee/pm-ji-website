<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: /NEW-PM-JI-RESERVIFY/index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reserve Your Service - PM&JI Reservify</title>

    <!-- JQeury -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/booking.css">
    <link rel="stylesheet"
        href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/progress-indicator/progress-indicator.css">
    <link rel="stylesheet"
        href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step1/booking-step1.css">
    <link rel="stylesheet"
        href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step2/booking-step2.css">
    <link rel="stylesheet"
        href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step3/booking-step3.css">
    <link rel="stylesheet"
        href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step4/booking-step4.css">
    <link rel="stylesheet"
        href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step5/booking-step5.css">
    <link rel="stylesheet"
        href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/processing-booking-loading/processing-booking-loading.css">
    <!-- End Custom CSS -->

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />

    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>

<div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/navigation-bar/index.php'; ?>

    <div class="animated-bg"></div>

    <!-- Form Container -->
    <div class="reservation-container">
        <!-- Form Header -->
        <div>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/progress-indicator/progress-indicator.html'; ?>
        </div>
        <!-- End Form Header -->

        <!-- Booking Form -->
        <form action="process-booking.php" method="post" class="reservation-form" id="reservationForm"
            enctype="multipart/form-data">

            <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step1/index.php'; ?>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step2/index.php'; ?>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step3/index.php'; ?>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step4/index.php'; ?>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step5/index.php'; ?>

        </form>
        <!-- End Booking Form -->
    </div>
    <!-- End Form Container -->

    <!-- Loading Overlay -->
    <div>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/processing-booking-loading/index.php'; ?>
    </div>
    <!-- End Loading Overlay -->

    <script src="/NEW-PM-JI-RESERVIFY/pages/customer/booking/booking.js"></script>

    </body>

</html>