<!-- Step 5: Payment -->
<div class="form-step" data-step="5">
    <div class="row align-items-end">
        <!-- Payment Type -->
        <div class="col-md-4">
            <div class="booking-form-container p-3 mb-1" style="height: 80px;">
                <label style="font-weight:600;">Payment Type</label>
                <div class="radio-inputs-19 d-flex flex-row gap-2" style="gap: 16px;">
                    <label for="downPayment" class="mb-0" style="margin-right:10px;">
                        <input id="downPayment" type="radio" name="payment_type" value="Down Payment" required>
                        <span class="name">Down Payment</span>
                    </label>
                    <label for="fullPayment" class="mb-0">
                        <input id="fullPayment" type="radio" name="payment_type" value="Full Payment" required>
                        <span class="name">Full Payment</span>
                    </label>
                </div>
            </div>
        </div>
        <!-- Payment Method -->
        <div class="col-md-4">
            <div class="booking-form-container p-3 mb-1" style="height: 80px;">
                <label style="font-weight:600;">Payment Method</label>
                <div class="radio-inputs-19 d-flex flex-row gap-2" style="gap: 16px;">
                    <label for="paymentGCash" class="mb-0" style="margin-right:10px;">
                        <input id="paymentGCash" type="radio" name="payment_method" value="GCash" required checked>
                        <span class="name">GCash</span>
                    </label>
                    <label for="paymentPaymaya" class="mb-0">
                        <input id="paymentPaymaya" type="radio" name="payment_method" value="Paymaya" required>
                        <span class="name">Paymaya</span>
                    </label>
                </div>
            </div>
        </div>
        <!-- Price Preview -->
        <div class="col-md-4">
            <div class="booking-form-container p-3 mb-1" style="height: 80px;">
                <label for="step5PricePreview" style="font-weight:600;">Price</label>
                <div class="price-preview" id="step5PricePreview" style="font-size:1.2rem; font-weight:700;">₱0.00</div>
            </div>
        </div>
    </div>
    <!-- Container to display QR Code based on Payment Method -->
    <div>
        <div class="form-group" id="qrContainer" style="display:none;">
            <label>Scan QR Code:</label>
            <div id="qrBox" style="border:1.5px solid #ccc; border-radius:8px; padding:16px; background:#fafbfc;">
                <div class="row" style="align-items:center;">
                    <!-- Left column: logo and details -->
                    <div class="col-7" style="text-align:left;">
                        <div id="qrLogo" style="margin-bottom:10px;">
                            <!-- Logo will be set dynamically -->
                        </div>
                        <div id="qrDetails" style="font-size:15px; margin-top:8px;">
                            <!-- Payment details will be set dynamically -->
                        </div>
                    </div>
                    <!-- Right column: QR image -->
                    <div class="col-5" style="text-align:center;">
                        <div id="qrCode">
                            <!-- QR code image will be set dynamically -->
                            <img src="" alt="QR Code" id="qrImage" style="max-width: 120px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Reference Number -->
        <div class="col-md-6">
            <div class="booking-form-container p-3">
                <label for="referenceNumber">Reference Number</label>
                <input type="text" class="form-control" name="reference_number" id="referenceNumber"
                    placeholder="enter reference number" required>
            </div>
        </div>
        <!-- Payment Screenshot -->
        <div class="col-md-6">
            <div class="booking-form-container p-3">
                <label for="paymentScreenshot">Upload Payment Screenshot</label>
                <input type="file" class="form-control" name="payment_screenshot" id="paymentScreenshot"
                    accept="image/*" required>
            </div>
        </div>
    </div>
    <div class="form-navigation">
        <button type="button" class="prev-btn btn btn-secondary">Previous</button>
        <button type="submit" class="btn-reserve btn-primary">Confirm Booking</button>
    </div>
    <input type="hidden" name="price" id="bookingPrice" value="0">
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const qrContainer = document.getElementById('qrContainer');
        const qrImage = document.getElementById('qrImage');
        const qrDetails = document.getElementById('qrDetails');
        const qrLogo = document.getElementById('qrLogo');
        const pricePreview = document.getElementById('step5PricePreview');
        const bookingPriceInput = document.getElementById('bookingPrice');

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

        // listen for changes to payment type
        document.querySelectorAll('input[name="payment_type"]').forEach(radio => {
            radio.addEventListener('change', updatePriceDisplay);
        });

        // Step 4 to Step 5 transition logic
        const step4NextBtn = document.querySelector('.form-step[data-step="4"] .next-btn');
        if (step4NextBtn) {
            step4NextBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const step4 = document.querySelector('.form-step[data-step="4"]');
                const step5 = document.querySelector('.form-step[data-step="5"]');
                if (step4 && step5) {
                    step4.classList.remove('active');
                    step5.classList.add('active');
                }

                // capture full price from Step 4 and store raw number
                const previewPriceReview = document.getElementById('previewPriceReview');
                const fullPrice = parsePrice(previewPriceReview?.textContent || '0');
                pricePreview.dataset.fullprice = fullPrice;
                updatePriceDisplay(); // show appropriate price on load
            });
        }

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
</script>