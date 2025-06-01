document.addEventListener('DOMContentLoaded', () => {
    const qrContainer = document.getElementById('qrContainer');
    const qrImage = document.getElementById('qrImage');
    const qrDetails = document.getElementById('qrDetails');
    const qrLogo = document.getElementById('qrLogo');
    const pricePreview = document.getElementById('step5PricePreview');
    const bookingPriceInput = document.getElementById('bookingPrice');
    const fullPriceInput = document.getElementById('fullPriceInput');

    function parsePrice(str) {
        return parseFloat(str.replace(/[^\d.]/g, '')) || 0;
    }

    function formatCurrency(amount) {
        return '₱' + amount.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function updatePriceDisplay() {
        const paymentType = document.querySelector('input[name="payment_type"]:checked');
        const full = parseFloat(pricePreview.dataset.fullprice) || 0;

        let displayPrice = full;
        if (paymentType && paymentType.value === 'Down Payment') {
            displayPrice = full / 2;
        }

        pricePreview.textContent = formatCurrency(displayPrice);
        bookingPriceInput.value = displayPrice;
    }

    // make this function globally available so Step 1 can call it
    window.updateStep5PriceDisplay = updatePriceDisplay;

    // listen for changes to payment type
    document.querySelectorAll('input[name="payment_type"]').forEach(radio => {
        radio.addEventListener('change', updatePriceDisplay);
    });

    // step 5 initialization function - called from index.php
    window.initializeStep5 = function () {
        // get the full price from Step 1's price preview element
        const step1PricePreview = document.getElementById('previewPrice');
        let fullPrice = 0;

        if (step1PricePreview && step1PricePreview.dataset.fullPrice) {
            // use the stored data attribute from Step 1
            fullPrice = parseFloat(step1PricePreview.dataset.fullPrice);
        } else if (step1PricePreview) {
            // fallback: parse the displayed text
            fullPrice = parsePrice(step1PricePreview.textContent || '0');
        } else {
            // last resort: try to calculate from current form values
            const eventType = document.getElementById('eventType')?.value;
            const durationInput = document.querySelector('input[name="duration"]:checked');

            if (eventType && durationInput && window.PriceCalculator) {
                const duration = parseInt(durationInput.value, 10);
                fullPrice = window.PriceCalculator.getPrice(eventType, duration);
            }
        }

        console.log('Step 5 initialization - Full Price:', fullPrice);

        pricePreview.dataset.fullprice = fullPrice;
        if (fullPriceInput) fullPriceInput.value = fullPrice;
        updatePriceDisplay();
    };

    // step 5 validation function - can be called from the Submit button
    window.validateStep5 = function () {
        const errorDiv = document.getElementById('step5-error');
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';

        // check if payment type is selected
        const paymentType = document.querySelector('input[name="payment_type"]:checked');
        if (!paymentType) {
            errorDiv.textContent = 'Select Payment Type.';
            errorDiv.style.display = 'block';
            return false;
        }

        // check if payment method is selected
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        if (!paymentMethod) {
            errorDiv.textContent = 'Select a Payment Method.';
            errorDiv.style.display = 'block';
            return false;
        }

        // check if reference number is entered
        const referenceNumber = document.getElementById('referenceNumber');
        if (!referenceNumber.value.trim()) {
            errorDiv.textContent = 'Enter the Reference Number.';
            errorDiv.style.display = 'block';
            referenceNumber.focus();
            return false;
        }

        // check if payment screenshot is uploaded
        const paymentScreenshot = document.getElementById('paymentScreenshot');
        if (!paymentScreenshot.files.length) {
            errorDiv.textContent = 'Upload a Payment Screenshot.';
            errorDiv.style.display = 'block';
            paymentScreenshot.focus();
            return false;
        }

        // validate file type
        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        const file = paymentScreenshot.files[0];
        if (!allowedTypes.includes(file.type)) {
            errorDiv.textContent = 'Please upload a valid image file (PNG, JPG, JPEG).';
            errorDiv.style.display = 'block';
            paymentScreenshot.focus();
            return false;
        }

        // validate file size (optional - max 5MB)
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            errorDiv.textContent = 'File size must be less than 5MB.';
            errorDiv.style.display = 'block';
            paymentScreenshot.focus();
            return false;
        }

        return true;
    };

    // QR Logic
    const qrPaths = {
        'GCash': '/NEW-PM-JI-RESERVIFY/assets/qr/gcash.png',
        'Paymaya': '/NEW-PM-JI-RESERVIFY/assets/qr/paymaya.png'
    };

    const logoPaths = {
        'GCash': '/NEW-PM-JI-RESERVIFY/assets/qr/gcash-logo.png',
        'Paymaya': '/NEW-PM-JI-RESERVIFY/assets/qr/paymaya-logo.png'
    };

    const paymentDetails = {
        'GCash': `
        <b>Account Name:</b> CL****L B.<br>
        <b>Mobile No.:</b> 091* ****138<br>
        <b>UserID:</b> ************WG22IK
    `,
        'Paymaya': `
        <b>Account Name:</b> CLEZIEL BERNIL<br>
        <b>Mobile No.:</b> +63 *** *** 2138<br>
        <b>UserID:</b> @cibernil
    `
    };

    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', () => {
            if (!radio.checked) return;
            const method = radio.value;
            const path = qrPaths[method] || '';
            const logo = logoPaths[method] ? `<img src="${logoPaths[method]}" alt="${method} Logo" style="height:32px;vertical-align:middle;margin-right:8px;">` : '';
            qrImage.src = path;
            qrImage.alt = path ? `${method} QR Code` : 'QR Code';
            qrDetails.innerHTML = paymentDetails[method] || '';
            qrLogo.innerHTML = logo;
            qrContainer.style.display = path ? 'block' : 'none';
        });
    });

    const gcashRadio = document.getElementById('paymentGCash');
    if (gcashRadio) {
        gcashRadio.checked = true;
        gcashRadio.dispatchEvent(new Event('change'));
    }
});
// Add this to the end of booking-step5.js to ensure proper initialization

// step 5 initialization function - called from index.php
window.initializeStep5 = function () {
    // get the full price from Step 1's price preview element
    const step1PricePreview = document.getElementById('previewPrice');
    let fullPrice = 0;

    if (step1PricePreview && step1PricePreview.dataset.fullPrice) {
        // use the stored data attribute from Step 1
        fullPrice = parseFloat(step1PricePreview.dataset.fullPrice);
    } else if (step1PricePreview) {
        // fallback: parse the displayed text
        fullPrice = parsePrice(step1PricePreview.textContent || '0');
    } else {
        // last resort: try to calculate from current form values
        const eventType = document.getElementById('eventType')?.value;
        const durationInput = document.querySelector('input[name="duration"]:checked');

        if (eventType && durationInput && window.PriceCalculator) {
            const duration = parseInt(durationInput.value, 10);
            fullPrice = window.PriceCalculator.getPrice(eventType, duration);
        }
    }

    console.log('Step 5 initialization - Full Price:', fullPrice);

    pricePreview.dataset.fullprice = fullPrice;
    if (fullPriceInput) fullPriceInput.value = fullPrice;

    // IMPORTANT: Initialize the bookingPrice input with the correct value
    // This ensures that even if user doesn't change payment type, the value is correct
    updatePriceDisplay();

    // If down payment is pre-selected, make sure the display is correct
    const downPaymentRadio = document.getElementById('downPayment');
    if (downPaymentRadio && downPaymentRadio.checked) {
        updatePriceDisplay();
    }
};

// Also add an event listener to ensure price is updated when step becomes active
document.addEventListener('DOMContentLoaded', function () {
    // Listen for when step 5 becomes active
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const step5 = document.querySelector('.form-step[data-step="5"]');
                if (step5 && step5.classList.contains('active')) {
                    // Step 5 just became active, initialize it
                    setTimeout(function () {
                        if (window.initializeStep5) {
                            window.initializeStep5();
                        }
                    }, 100);
                }
            }
        });
    });

    const step5 = document.querySelector('.form-step[data-step="5"]');
    if (step5) {
        observer.observe(step5, { attributes: true });
    }
});