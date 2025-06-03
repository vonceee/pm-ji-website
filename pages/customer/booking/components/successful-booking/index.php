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

    <div class="container-content">
        <div class="card">
            <div class="card-header">
                <h2>Booking Request Sent!</h2>
            </div>
            <div class="card-body">
                <!-- success icon instead -->
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                
                <p class="lead">Thank you for booking with PM&JI Reservify!</p>
                
                <?php if ($referenceId): ?>
                    <h5>Your Reference ID</h5>
                    <div class="reference-id">
                        <?= htmlspecialchars($referenceId) ?>
                    </div>
                <?php endif; ?>

                <p>an email has been sent with all the booking details.</p>
            </div>
            <div class="card-footer">
                <p class="mb-2">
                    <strong>What's Next?</strong><br>
                    booking is being processed, processing might take 3-4 hours.
                </p>
                <a href="/NEW-PM-JI-RESERVIFY/pages/customer/views/dashboard.php">
                    View Booking Status →
                </a>
            </div>
        </div>
    </div>

    <script>
        // entrance animation
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>

</html>