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

        // update navigation buttons visibility
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

    // fixed implementation for index.php - replace the updateNavigationButtons function

    function updateNavigationButtons(currentIndex, totalSteps) {
        const prevBtn = document.querySelector('.form-navigation-top .prev-btn');
        const nextBtn = document.querySelector('.form-navigation-top .next-btn');
        const submitBtn = document.querySelector('.form-navigation-top .submit-btn');

        // always show previous button but disable it on first step
        if (prevBtn) {
            prevBtn.style.display = 'inline-flex'; // changed from 'inline-block' to match CSS
            if (currentIndex === 0) {
                prevBtn.disabled = true;
                prevBtn.style.opacity = '0.4';
            } else {
                prevBtn.disabled = false;
                prevBtn.style.opacity = '1';
            }
        }

        // show/hide Next and Submit buttons
        if (currentIndex === totalSteps - 1) {
            if (nextBtn) nextBtn.style.display = 'none';
            if (submitBtn) submitBtn.style.display = 'inline-flex'; // changed from 'inline-block' to match CSS
        } else {
            if (nextBtn) nextBtn.style.display = 'inline-flex'; // changed from 'inline-block' to match CSS
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
            case 3: return typeof validateStep4 === "function" ? validateStep4() : true;
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
            const cardTitle = packageInput.parentElement.querySelector('.package-title');
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

    // event listeners for navigation buttons
    nextButtons.forEach(btn => {
        btn.addEventListener("click", e => {
            e.preventDefault();
            if (!validateStep(currentStep)) return;

            // special handling for Step 3 -> Step 4 transition
            if (currentStep === 2) {
                updateStep4Preview();
            }

            // special handling for Step 4 -> Step 5 transition
            if (currentStep === 3) {
                // initialize Step 5 with price data
                setTimeout(() => {
                    if (typeof window.initializeStep5 === 'function') {
                        window.initializeStep5();
                    }
                }, 100); // small delay to ensure DOM is ready
            }

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

    // submit button event listener
    if (submitButton) {
        submitButton.addEventListener("click", e => {
            e.preventDefault();
            if (validateStep(currentStep)) {
                // submit the form
                document.getElementById('reservationForm').submit();
            }
        });
    }

    // initialize
    showStep(currentStep);
});