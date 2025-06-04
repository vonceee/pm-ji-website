document.addEventListener('DOMContentLoaded', () => {

    let isSubmitting = false;

    const qrContainer = document.getElementById('qrContainer');
    const qrImage = document.getElementById('qrImage');
    const qrDetails = document.getElementById('qrDetails');
    const qrLogo = document.getElementById('qrLogo');
    const pricePreview = document.getElementById('step5PricePreview');
    const bookingPriceInput = document.getElementById('bookingPrice');
    const fullPriceInput = document.getElementById('fullPriceInput');

    // reference number validation patterns
    const referencePatterns = {
        'GCash': {
            pattern: /^\d{13}$/,
            description: '13-digit number',
            example: '1234567890123'
        },
        'Paymaya': {
            pattern: /^[A-Z0-9]{10,15}$/,
            description: '10-15 characters (letters and numbers)',
            example: 'ABC1234567890'
        }
    };

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

    // reference number validation function
    function validateReferenceNumber(referenceNumber, paymentMethod) {
        if (!referenceNumber || !paymentMethod) {
            return { isValid: false, message: '' };
        }

        const pattern = referencePatterns[paymentMethod];
        if (!pattern) {
            return { isValid: true, message: '' };
        }

        const isValid = pattern.pattern.test(referenceNumber.trim());
        const message = isValid ? '' : `Please enter a valid ${paymentMethod} reference number (${pattern.description})`;

        return { isValid, message };
    }

    // update placeholder and validation message based on payment method
    function updateReferenceNumberField() {
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
        const referenceInput = document.getElementById('referenceNumber');
        const referenceLabel = document.querySelector('label[for="referenceNumber"]');

        if (!paymentMethod || !referenceInput) return;

        const pattern = referencePatterns[paymentMethod];
        if (pattern) {
            referenceInput.placeholder = `Enter ${paymentMethod} Reference Number (e.g., ${pattern.example})`;
            referenceInput.title = `${paymentMethod} reference number format: ${pattern.description}`;

            // update label with format hint
            const labelText = referenceLabel.textContent.replace(/\s*\(.*?\)\s*/, '').replace(' *', '');
            referenceLabel.innerHTML = `${labelText} <span style="color: red">*</span> <small style="color: #64748b; font-weight: normal;">(${pattern.description})</small>`;
        }
    }

    // setup real-time reference number validation
    function setupReferenceNumberValidation() {
        const referenceInput = document.getElementById('referenceNumber');
        if (!referenceInput) return;

        // create validation message element
        let validationMsg = document.getElementById('reference-validation-msg');
        if (!validationMsg) {
            validationMsg = document.createElement('small');
            validationMsg.id = 'reference-validation-msg';
            validationMsg.style.cssText = 'color: #ef4444; font-size: 12px; margin-top: 4px; display: none;';
            referenceInput.parentNode.appendChild(validationMsg);
        }

        referenceInput.addEventListener('input', function () {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
            const validation = validateReferenceNumber(this.value, paymentMethod);

            if (this.value.trim() === '') {
                // hide validation message when field is empty
                validationMsg.style.display = 'none';
                referenceInput.style.borderColor = '';
            } else if (!validation.isValid && validation.message) {
                // show validation error
                validationMsg.textContent = validation.message;
                validationMsg.style.display = 'block';
                referenceInput.style.borderColor = '#ef4444';
            } else {
                // hide validation message when valid
                validationMsg.style.display = 'none';
                referenceInput.style.borderColor = validation.isValid ? '#10b981' : '';
            }
        });

        // validate on blur (when user leaves the field)
        referenceInput.addEventListener('blur', function () {
            if (this.value.trim() !== '') {
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
                const validation = validateReferenceNumber(this.value, paymentMethod);

                if (!validation.isValid && validation.message) {
                    validationMsg.textContent = validation.message;
                    validationMsg.style.display = 'block';
                    referenceInput.style.borderColor = '#ef4444';
                }
            }
        });
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

        /*-- console.log('Step 5 initialization - Full Price:', fullPrice); --*/

        pricePreview.dataset.fullprice = fullPrice;
        if (fullPriceInput) fullPriceInput.value = fullPrice;
        updatePriceDisplay();
    };

    // Terms and Conditions Modal Functions
    function showTermsModal() {
        const modal = document.getElementById('termsModal');
        const overlay = document.getElementById('termsOverlay');
        if (modal && overlay) {
            modal.style.display = 'block';
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden'; // prevent background scrolling
        }
    }

    function hideTermsModal() {
        const modal = document.getElementById('termsModal');
        const overlay = document.getElementById('termsOverlay');
        if (modal && overlay) {
            modal.style.display = 'none';
            overlay.style.display = 'none';
            document.body.style.overflow = 'auto'; // restore scrolling
        }
    }

    // make modal functions globally available
    window.showTermsModal = showTermsModal;
    window.hideTermsModal = hideTermsModal;

    // Enhanced step 5 validation function - can be called from the Submit button
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

        // validate reference number format based on payment method
        const validation = validateReferenceNumber(referenceNumber.value, paymentMethod.value);
        if (!validation.isValid && validation.message) {
            errorDiv.textContent = validation.message;
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
            errorDiv.textContent = 'Please upload a Valid Image File (PNG, JPG, JPEG).';
            errorDiv.style.display = 'block';
            paymentScreenshot.focus();
            return false;
        }

        // show terms and conditions modal instead of proceeding directly
        showTermsModal();
        return false; // don't submit yet, wait for terms acceptance
    };

    // handle terms agreement and form submission
    window.handleTermsAgreement = function () {
        // prevent multiple submissions
        if (isSubmitting) {
            return false;
        }

        const termsCheckbox = document.getElementById('termsCheckbox');
        const agreeButton = document.getElementById('agreeTermsBtn');

        if (!termsCheckbox.checked) {
            const termsError = document.getElementById('terms-error');
            termsError.textContent = 'You must agree to the Terms and Conditions to proceed.';
            termsError.style.display = 'block';
            return false;
        }

        // set submitting flag and disable button
        isSubmitting = true;
        if (agreeButton) {
            agreeButton.disabled = true;
            agreeButton.textContent = 'Processing...';
        }

        // hide modal and submit the form
        hideTermsModal();

        // find and submit the reservation form
        const form = document.getElementById('reservationForm');
        if (form) {
            // add a hidden input to indicate terms were accepted
            const termsAcceptedInput = document.createElement('input');
            termsAcceptedInput.type = 'hidden';
            termsAcceptedInput.name = 'terms_accepted';
            termsAcceptedInput.value = '1';
            form.appendChild(termsAcceptedInput);

            form.submit();
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

            // update reference number field when payment method changes
            updateReferenceNumberField();
            // clear reference number and validation when payment method changes
            const referenceInput = document.getElementById('referenceNumber');
            const validationMsg = document.getElementById('reference-validation-msg');
            if (referenceInput) {
                referenceInput.value = '';
                referenceInput.style.borderColor = '';
            }
            if (validationMsg) {
                validationMsg.style.display = 'none';
            }
        });
    });

    const gcashRadio = document.getElementById('paymentGCash');
    if (gcashRadio) {
        gcashRadio.checked = true;
        gcashRadio.dispatchEvent(new Event('change'));
    }

    // initialize reference number validation
    setupReferenceNumberValidation();
    updateReferenceNumberField();

    // terms checkbox event listener
    document.addEventListener('change', function (e) {
        if (e.target.id === 'termsCheckbox') {
            const termsError = document.getElementById('terms-error');
            if (termsError) {
                termsError.style.display = 'none';
            }
        }
    });

    // close modal when clicking outside
    document.addEventListener('click', function (e) {
        if (e.target.id === 'termsOverlay') {
            hideTermsModal();
        }
    });

    // close modal with Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            hideTermsModal();
        }
    });
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

    const pricePreview = document.getElementById('step5PricePreview');
    const fullPriceInput = document.getElementById('fullPriceInput');

    if (pricePreview) pricePreview.dataset.fullprice = fullPrice;
    if (fullPriceInput) fullPriceInput.value = fullPrice;

    // this ensures that even if user doesn't change payment type, the value is correct
    if (window.updateStep5PriceDisplay) {
        window.updateStep5PriceDisplay();
    }

    // if down payment is pre-selected, make sure the display is correct
    const downPaymentRadio = document.getElementById('downPayment');
    const updatePriceDisplay = window.updateStep5PriceDisplay;
    if (downPaymentRadio && downPaymentRadio.checked && updatePriceDisplay) {
        updatePriceDisplay();
    }
};

// add an event listener to ensure price is updated when step becomes active
document.addEventListener('DOMContentLoaded', function () {
    // listen for when step 5 becomes active
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const step5 = document.querySelector('.form-step[data-step="5"]');
                if (step5 && step5.classList.contains('active')) {
                    // step 5 just became active, initialize it
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