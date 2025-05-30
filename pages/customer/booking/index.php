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
    <link rel="stylesheet"
        href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step1/booking-step1.css">
    <!-- End Custom CSS -->

    <!-- jQuery and jQuery UI -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />
    <!-- End jQuery and jQuery UI -->

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/navigation-bar/index.php'; ?>

    <!-- Form Container -->
    <div class="reservation-container">
        <!-- Form Header -->
        <div>
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
            const submitButton = document.querySelector(".submit-btn");
            let currentStep = 0;

            function updateStepIndicator(currentIndex) {
                const targetStep = currentIndex + 1;
                const steps = document.querySelectorAll('.progress-step');
                const totalSteps = steps.length;

                steps.forEach(step => {
                    const stepNum = Number(step.getAttribute('data-step'));
                    step.classList.remove('active', 'completed');

                    if (stepNum < targetStep) {
                        step.classList.add('completed');
                    } else if (stepNum === targetStep) {
                        step.classList.add('active');
                    }
                });

                // Update navigation buttons visibility
                updateNavigationButtons(currentIndex, totalSteps);

                // update progress line with animation
                const completedSteps = Math.max(0, targetStep - 1);
                const progressPercentage = (completedSteps / (totalSteps - 1)) * 100;

                // create or update dynamic style for progress line
                const styleElement = document.getElementById('progress-style') || document.createElement('style');
                styleElement.id = 'progress-style';
                styleElement.textContent = `.step-list::after { width: ${progressPercentage}% !important; }`;

                if (!document.getElementById('progress-style')) {
                    document.head.appendChild(styleElement);
                }
            }

            function updateNavigationButtons(currentIndex, totalSteps) {
                const prevBtn = document.querySelector('.form-navigation-top .prev-btn');
                const nextBtn = document.querySelector('.form-navigation-top .next-btn');
                const submitBtn = document.querySelector('.form-navigation-top .submit-btn');

                // Show/hide Previous button
                if (prevBtn) {
                    prevBtn.style.display = currentIndex > 0 ? 'inline-block' : 'none';
                }

                // Show/hide Next and Submit buttons
                if (currentIndex === totalSteps - 1) {
                    if (nextBtn) nextBtn.style.display = 'none';
                    if (submitBtn) submitBtn.style.display = 'inline-block';
                } else {
                    if (nextBtn) nextBtn.style.display = 'inline-block';
                    if (submitBtn) submitBtn.style.display = 'none';
                }
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
                const eventTypeSelect = document.getElementById('eventType');
                if (eventTypeSelect && eventTypeSelect.selectedOptions && eventTypeSelect.selectedOptions[0]) {
                    document.getElementById('previewEventType').textContent = eventTypeSelect.selectedOptions[0].text;
                }

                const durationInput = document.querySelector('input[name="duration"]:checked');
                if (durationInput) {
                    document.getElementById('previewDuration').textContent = durationInput.parentElement.textContent.trim();
                }

                // Step 2
                const reservationDate = document.getElementById('reservationDate');
                if (reservationDate) {
                    document.getElementById('previewDate').textContent = reservationDate.value;
                }

                const startTime = document.getElementById('startTime');
                if (startTime) {
                    document.getElementById('previewStartTime').textContent = startTime.value;
                }

                const endTime = document.getElementById('endTime');
                if (endTime) {
                    document.getElementById('previewEndTime').textContent = endTime.value;
                }

                // Step 3
                const streetAddress = document.getElementById('streetAddress');
                if (streetAddress) {
                    document.getElementById('previewStreetAddress').textContent = streetAddress.value;
                }

                const citySelect = document.getElementById('citySelect');
                if (citySelect && citySelect.selectedOptions && citySelect.selectedOptions[0]) {
                    document.getElementById('previewCity').textContent = citySelect.selectedOptions[0].text;
                }

                const barangaySelect = document.getElementById('barangaySelect');
                if (barangaySelect && barangaySelect.selectedOptions && barangaySelect.selectedOptions[0]) {
                    document.getElementById('previewBarangay').textContent = barangaySelect.selectedOptions[0].text;
                }

                const fullAddress = document.getElementById('fullAddress');
                if (fullAddress) {
                    document.getElementById('previewFullAddress').textContent = fullAddress.value;
                }

                // Step 1 - Package
                const packageInput = document.querySelector('input[name="package"]:checked');
                if (packageInput) {
                    const cardTitle = packageInput.parentElement.querySelector('.card-title');
                    if (cardTitle) {
                        document.getElementById('previewPackages').textContent = cardTitle.textContent;
                    }
                }

                // Step 1 - Price
                const previewPrice = document.getElementById('previewPrice');
                if (previewPrice) {
                    document.getElementById('previewPriceReview').textContent = previewPrice.textContent;
                }
            }

            // Event listeners for navigation buttons
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
            });

            prevButtons.forEach(btn => {
                btn.addEventListener("click", e => {
                    e.preventDefault();
                    if (currentStep > 0) {
                        currentStep--;
                        showStep(currentStep);
                    }
                });
            });

            // Submit button event listener
            if (submitButton) {
                submitButton.addEventListener("click", e => {
                    e.preventDefault();
                    if (validateStep(currentStep)) {
                        // Submit the form
                        document.getElementById('reservationForm').submit();
                    }
                });
            }

            // Initialize
            showStep(currentStep);
        });
    </script>

</body>