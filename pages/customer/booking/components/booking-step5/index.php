<!-- Step 5: Payment -->
<div class="form-step" data-step="5">
    <div class="step-header">
        <h2 class="step-title">Payment Details</h2>
        <p class="step-subtitle">choose your payment type and method to complete your booking</p>
    </div>

    <div id="step5-error" class="error-message"></div>

    <!-- Payment Options Grid -->
    <div class="payment-grid">
        <!-- Payment Type Card -->
        <div class="payment-card">
            <label class="payment-card-label">Payment Type <span style="color: red">*</span></label>
            <div class="radio-inputs-19">
                <label for="downPayment">
                    <input id="downPayment" form="reservationForm" type="radio" name="payment_type" value="Down Payment" required>
                    <span class="name">Down Payment</span>
                </label>
                <label for="fullPayment">
                    <input id="fullPayment" form="reservationForm" type="radio" name="payment_type" value="Full Payment" required>
                    <span class="name">Full Payment</span>
                </label>
            </div>
        </div>

        <!-- Payment Method Card -->
        <div class="payment-card">
            <label class="payment-card-label">Payment Method <span style="color: red">*</span></label>
            <div class="radio-inputs-19">
                <label for="paymentGCash">
                    <input id="paymentGCash" form="reservationForm" type="radio" name="payment_method" value="GCash" required checked>
                    <span class="name">GCash</span>
                </label>
                <label for="paymentPaymaya">
                    <input id="paymentPaymaya" form="reservationForm" type="radio" name="payment_method" value="Paymaya" required>
                    <span class="name">Paymaya</span>
                </label>
            </div>
        </div>

        <!-- Price Preview Card -->
        <div class="payment-card price-preview-container">
            <label class="payment-card-label">Amount to Pay</label>
            <div class="price-preview" id="step5PricePreview">₱0.00</div>
        </div>
    </div>

    <!-- QR Code Section -->
    <div class="qr-section">
        <div class="qr-container" id="qrContainer" style="display:none;">
            <div class="qr-header">
                <i class="fas fa-qrcode"></i>
                Scan QR Code to Pay
            </div>
            <div class="qr-content">
                <div class="qr-details">
                    <div class="qr-logo" id="qrLogo">
                        <!-- logo will be set dynamically -->
                    </div>
                    <div class="qr-payment-details" id="qrDetails">
                        <!-- payment details will be set dynamically -->
                    </div>
                </div>
                <div class="qr-code-wrapper">
                    <img src="" alt="QR Code" id="qrImage" class="qr-code-image">
                </div>
            </div>
        </div>
    </div>

    <!-- Input Fields Grid -->
    <div class="input-grid">
        <!-- Reference Number -->
        <div class="input-card">
            <label class="input-label" for="referenceNumber">Reference Number <span style="color: red">*</span></label>
            <input type="text" class="form-input" name="reference_number" id="referenceNumber" form="reservationForm"
                placeholder="Enter Reference Number" required>
        </div>

        <!-- Payment Screenshot -->
        <div class="input-card">
            <label class="input-label" for="paymentScreenshot">Upload Payment Screenshot <span style="color: red">*</span></label>
            <input type="file" class="file-input" name="payment_screenshot" id="paymentScreenshot" form="reservationForm"
                accept=".png,.jpg,.jpeg,image/png,image/jpeg" required>
            <small style="color: #64748b; font-size: 12px; margin-top: 4px; display: block;">
                accepted formats: PNG, JPG, JPEG (Max 5MB)
            </small>
        </div>
    </div>

    <!-- Hidden Inputs -->
    <input type="hidden" name="price" id="bookingPrice" value="0">
    <input type="hidden" name="full_price" id="fullPriceInput" value="0">
</div>

<!-- Terms and Conditions Modal -->
<div id="termsOverlay" class="terms-overlay" style="display: none;"></div>
<div id="termsModal" class="terms-modal" style="display: none;">
    <div class="terms-modal-content">
        <div class="terms-modal-header">
            <h3>Terms and Conditions</h3>
            <button type="button" class="terms-close-btn" onclick="hideTermsModal()">&times;</button>
        </div>
        
        <div class="terms-modal-body">
            <div class="terms-content">         
                <div class="terms-link-section">
                    <a href="/NEW-PM-JI-RESERVIFY/assets/documents/PM&JIReservifyTerms&Conditions.pdf" 
                       target="_blank" 
                       class="terms-link">
                        <i class="fas fa-file-pdf"></i>
                        View Full Terms and Conditions (PDF)
                    </a>
                </div>
            </div>
            
            <div id="terms-error" class="error-message" style="display: none;"></div>
            
            <div class="terms-agreement">
                <label class="terms-checkbox-label">
                    <input type="checkbox" id="termsCheckbox" class="terms-checkbox">
                    <span class="checkmark"></span>
                    I have read, understood, and agree to the 
                    <a href="/NEW-PM-JI-RESERVIFY/assets/documents/terms-and-conditions.pdf" target="_blank">Terms and Conditions</a>
                </label>
            </div>
        </div>
        
        <div class="terms-modal-footer">
            <button type="button" class="btn-secondary" onclick="hideTermsModal()">Cancel</button>
            <button type="button" class="btn-primary" onclick="handleTermsAgreement()" id="agreeTermsBtn">
                Agree and Complete Booking
            </button>
        </div>
    </div>
</div>

<script src="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step5/booking-step5.js"></script>