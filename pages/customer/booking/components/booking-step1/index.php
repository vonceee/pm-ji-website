<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Booking Step 1</title>
    <style>
        
    </style>
</head>
<body>
    <div class="form-step active" data-step="1">
        <!-- Error Message -->
        <div id="step1-error" class="error-note">
            <span id="error-text"></span>
        </div>

        <!-- Step Header -->
        <div class="step-header">
            <h1 class="step-title">Reserve Your Service</h1>
            <p class="step-subtitle">Choose your event details and package preferences</p>
        </div>

        <!-- Form Grid -->
        <div class="form-grid">
            <!-- Event Type Selection -->
            <div class="form-section">
                <div class="section-label">
                    <span class="section-number">1</span>
                    Select Event Type
                </div>
                <div class="select-wrapper">
                    <select id="eventType" name="event_type" required>
                        <option value="" disabled selected>Choose your event</option>
                        <option value="Baptism">Baptism</option>
                        <option value="Birthday">Birthday</option>
                        <option value="Corporate Event">Corporate Event</option>
                        <option value="Reunion">Reunion</option>
                        <option value="Wedding">Wedding</option>
                    </select>
                </div>
            </div>

            <!-- Duration Selection -->
            <div class="form-section">
                <div class="section-label">
                    <span class="section-number">2</span>
                    Select Duration
                </div>
                <div class="duration-options">
                    <div class="duration-option">
                        <input type="radio" id="duration3hr" name="duration" value="3" required checked>
                        <label for="duration3hr" class="duration-label">3 Hours</label>
                    </div>
                    <div class="duration-option">
                        <input type="radio" id="duration4hr" name="duration" value="4">
                        <label for="duration4hr" class="duration-label">4 Hours</label>
                    </div>
                </div>
            </div>

            <!-- Price Preview -->
            <div class="form-section">
                <div class="section-label">
                    <span class="section-number">💰</span>
                    Total Price
                </div>
                <div class="price-display">
                    <div class="price-label">Starting from</div>
                    <div class="price-value" id="previewPrice">₱0.00</div>
                </div>
            </div>
        </div>

        <!-- Important Note -->
        <div class="important-note">
            <div class="note-text">
                <strong>Note:</strong> Extension of hours during event costs ₱1,800 per hour (fixed rate for any event type).
            </div>
        </div>

        <!-- Package Selection -->
        <div class="packages-section">
            <h2 class="packages-title">Choose Your Package</h2>
            <div class="packages-grid">
                <!-- Package 1: Photo Standee -->
                <label class="package-card">
                    <input type="radio" name="package" value="PhotoStandeeFrame" id="package_1" class="package-input" required>
                    <div class="package-content">
                        <img src="https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?w=400&h=300&fit=crop" alt="Photo Standee Frame" class="package-image">
                        <div class="package-info">
                            <h3 class="package-title">Photo Standee</h3>
                            <div class="package-features">
                                <ul>
                                    <li>Customized Layout</li>
                                    <li>4 Plastic Standee Frames</li>
                                    <li>3 Ref Magnets (Single Shot)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </label>

                <!-- Package 2: Polaroid Frame -->
                <label class="package-card">
                    <input type="radio" name="package" value="PolaroidFrame" id="package_2" class="package-input" required>
                    <div class="package-content">
                        <img src="https://images.unsplash.com/photo-1522204523234-8729aa6e3d5f?w=400&h=300&fit=crop" alt="Polaroid Frame" class="package-image">
                        <div class="package-info">
                            <h3 class="package-title">Polaroid Frame</h3>
                            <div class="package-features">
                                <ul>
                                    <li>Customized Layout</li>
                                    <li>4 Polaroid Frames</li>
                                    <li>3 Ref Magnets (Single Shot)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </label>

                <!-- Package 3: Photo Strip -->
                <label class="package-card">
                    <input type="radio" name="package" value="PhotoStripFrame" id="package_3" class="package-input" required>
                    <div class="package-content">
                        <img src="https://images.unsplash.com/photo-1516975080664-ed2fc6a32937?w=400&h=300&fit=crop" alt="2x6 Photo Strip Frame" class="package-image">
                        <div class="package-info">
                            <h3 class="package-title">2×6 Photo Strip</h3>
                            <div class="package-features">
                                <ul>
                                    <li>Customized Layout</li>
                                    <li>4 2×6 Photo Strip Frames</li>
                                    <li>3 Ref Magnets (Single Shot)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <script src="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step1/booking-step1.js"></script>
</body>
</html> 