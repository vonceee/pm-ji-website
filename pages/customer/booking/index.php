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
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- End Bootstrap CSS -->

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/booking.css">
    <!-- <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/progress-indicator.css"> -->
    <!-- <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-form.css"> -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step1/booking-step1.css">
    <!-- End Custom CSS -->

    <!-- jQuery and jQuery UI -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />
    <!-- End jQuery and jQuery UI -->

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/top-header.php'; ?>

    <!-- Form Container -->
    <div class="reservation-container">
        <!-- Form Header -->
        <div class="header-container">
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/progress-indicator.html'; ?>
        </div>
        <!-- End Form Header -->

        <!-- Booking Form -->
        <form action="process_booking.php" method="post" class="reservation-form" id="reservationForm"
            enctype="multipart/form-data">

            <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step1/index.php'; ?>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/step2.php'; ?>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/step3.php'; ?>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/step4.php'; ?>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/step5.php'; ?>

        </form>
        <!-- End Booking Form -->
    </div>
    <!-- End Form Container -->


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const steps = document.querySelectorAll(".form-step");
            const nextButtons = document.querySelectorAll(".next-btn");
            const prevButtons = document.querySelectorAll(".prev-btn");
            let currentStep = 0;

            function updateStepIndicator(currentIndex) {
                // currentIndex is zero-based; data-step is one-based
                const targetStep = currentIndex + 1;
                document
                    .querySelectorAll('.progress-step')
                    .forEach(li => {
                        const stepNum = Number(li.getAttribute('data-step'));
                        li.classList.toggle('active', stepNum === targetStep);
                        li.classList.toggle('completed', stepNum < targetStep);
                    });
            }

            function showStep(index) {
                steps.forEach((step, i) => {
                    step.classList.toggle("active", i === index);
                });
                updateStepIndicator(index);
            }

            function validateStep(idx) {
                // dispatch to your existing functions
                switch (idx) {
                    case 0: return typeof validateStep1 === "function" && validateStep1();
                    case 1: return typeof validateStep2 === "function" && validateStep2();
                    case 2: return typeof validateStep3 === "function" && validateStep3();
                    case 3: return typeof validateStep4 === "function" && validateStep4();
                    case 4: return typeof validateStep5 === "function" && validateStep5();
                    default: return true;
                }
            }

            function updateStep4Preview() {
                // Step 1
                document.getElementById('previewEventType').textContent =
                    document.getElementById('eventType').selectedOptions
                        ? document.getElementById('eventType').selectedOptions[0].text
                        : document.getElementById('eventType').value;

                const durationInput = document.querySelector('input[name="duration"]:checked');
                document.getElementById('previewDuration').textContent =
                    durationInput ? durationInput.parentElement.textContent.trim() : '';

                // Step 2
                document.getElementById('previewDate').textContent =
                    document.getElementById('reservationDate').value;
                document.getElementById('previewStartTime').textContent =
                    document.getElementById('startTime').value;
                document.getElementById('previewEndTime').textContent =
                    document.getElementById('endTime').value;

                // Step 3
                document.getElementById('previewStreetAddress').textContent =
                    document.getElementById('streetAddress').value;
                document.getElementById('previewCity').textContent =
                    document.getElementById('citySelect').selectedOptions[0].text;
                document.getElementById('previewBarangay').textContent =
                    document.getElementById('barangaySelect').selectedOptions[0].text;
                document.getElementById('previewFullAddress').textContent =
                    document.getElementById('fullAddress').value;

                // Step 1 - Package
                const packageInput = document.querySelector('input[name="package"]:checked');
                document.getElementById('previewPackages').textContent =
                    packageInput
                        ? packageInput.parentElement.querySelector('.card-title').textContent
                        : '';

                // Step 1 - Price
                document.getElementById('previewPriceReview').textContent =
                    document.getElementById('previewPrice').textContent;
            }

            nextButtons.forEach(btn => {
                btn.addEventListener("click", e => {
                    e.preventDefault();
                    if (!validateStep(currentStep)) return;
                    if (currentStep === 2) updateStep4Preview();
                    if (currentStep < steps.length - 1) {
                        currentStep++;
                        showStep(currentStep);
                    }
                });
            }); prevButtons.forEach(btn => {
                btn.addEventListener("click", e => {
                    e.preventDefault();
                    if (currentStep > 0) {
                        currentStep--;
                        showStep(currentStep);
                    }
                });
            });

            showStep(currentStep);
        });
    </script>

</body>