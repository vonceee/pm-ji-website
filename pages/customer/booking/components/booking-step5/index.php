<head>
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step5/booking-step5.css">
</head>

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
                <label for="paymentScreenshot">Upload Payment Screenshot (png, jpg, jpeg)</label>
                <input type="file" class="form-control" name="payment_screenshot" id="paymentScreenshot"
                    accept=".png,.jpg,.jpeg,image/png,image/jpeg" required>
            </div>
        </div>
    </div>
    
    <input type="hidden" name="price" id="bookingPrice" value="0">
    <input type="hidden" name="full_price" id="fullPriceInput" value="0">
</div>

<script src="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step5/booking-step5.js"></script>