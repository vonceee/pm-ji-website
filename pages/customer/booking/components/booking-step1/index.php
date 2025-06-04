<!-- Booking Form -->
<form class="reservation-form" id="reservationForm">
    <!-- Step 1: Event Details -->
    <div class="form-step active" data-step="1">
        <div class="step-header">
            <h2 class="step-title">Event Details</h2>
            <p class="step-subtitle">choose your event type, duration, and package</p>
        </div>

        <div id="step1-error" class="error-message"></div>

        <div class="form-grid">
            <div class="form-card">
                <label class="form-label" for="eventType">Event Type <span style="color: red">*</span></label>
                <select class="form-select" name="event_type" id="eventType" required>
                    <option value="" disabled selected>Select Event Type</option>
                    <option value="Baptism">Baptism</option>
                    <option value="Birthday">Birthday</option>
                    <option value="Corporate Event">Corporate Event</option>
                    <option value="Reunion">Reunion</option>
                    <option value="Wedding">Wedding</option>
                </select>
            </div>

            <div class="form-card">
                <label class="form-label">Duration <span style="color: red">*</span></label>
                <div class="duration-options">
                    <div class="duration-option">
                        <input id="duration3hr" type="radio" name="duration" value="3" checked>
                        <label class="duration-label" for="duration3hr">3 Hours</label>
                    </div>
                    <div class="duration-option">
                        <input id="duration4hr" type="radio" name="duration" value="4">
                        <label class="duration-label" for="duration4hr">4 Hours</label>
                    </div>
                </div>
            </div>

            <div class="form-card">
                <div class="price-display" id="previewPrice">₱0.00</div>
            </div>
        </div>

        <div class="important-note">
            <i class="fas fa-exclamation-circle"></i>
            <div class="important-note-text">
                <strong>Note:</strong> Extension of hours during event costs ₱1,800 per hour (Fixed for any event).
            </div>
        </div>

        <label style="font-size: 1.5rem;">Select Package <span style="color: red">*</span></label>

        <div class="packages-section">
            <div class="packages-grid">
                <label class="package-card">
                    <input type="radio" name="package" value="PhotoStandeeFrame" required>
                    <div class="package-content">
                        <img src="/NEW-PM-JI-RESERVIFY/assets/packages/photo_standee_frame.jpg"
                            alt="Photo Standee Frame" class="package-image">
                        <h3 class="package-title">Photo Standee</h3>
                        <div class="package-features">
                            • Customized Layout<br>
                            • 4 Plastic Standee Frame<br>
                            • 3 Ref Magnets (Single Shot)
                        </div>
                    </div>
                </label>

                <label class="package-card">
                    <input type="radio" name="package" value="PolaroidFrame" required>
                    <div class="package-content">
                        <img src="/NEW-PM-JI-RESERVIFY/assets/packages/polaroid_frame.png" alt="Polaroid Frame"
                            class="package-image">
                        <h3 class="package-title">Polaroid Frame</h3>
                        <div class="package-features">
                            • Customized Layout<br>
                            • 4 Polaroid Frame<br>
                            • 3 Ref Magnets (Single Shot)
                        </div>
                    </div>
                </label>

                <label class="package-card">
                    <input type="radio" name="package" value="PhotoStripFrame" required>
                    <div class="package-content">
                        <img src="/NEW-PM-JI-RESERVIFY/assets/packages/photo_strip_frame.png"
                            alt="2x6 Photo Strip Frame" class="package-image">
                        <h3 class="package-title">2x6 Photo Strip</h3>
                        <div class="package-features">
                            • Customized Layout<br>
                            • 4 2x6 Photo Strip Frame<br>
                            • 3 Ref Magnets (Single Shot)
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </div>
</form>

<script src="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step1/booking-step1.js"></script>