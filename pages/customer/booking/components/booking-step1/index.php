<div class="form-step active" data-step="1">
    <div class="form-row align-items-end mb-0">
        <div class="col-md-4 mb-2">
            <div class="booking-form-container p-3">
                <label class="mb-3" for="eventType">Step 1: Select an Event</label>
                <select class="form-control" name="event_type" id="eventType" required>
                    <option value="" disabled <?= empty($preselectedEvent) ? 'selected' : '' ?>>select event type
                    </option>
                    <option value="Baptism">Baptism</option>
                    <option value="Birthday">Birthday</option>
                    <option value="Corporate Event">Corporate Event</option>
                    <option value="Reunion">Reunion</option>
                    <option value="Wedding">Wedding</option>
                </select>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="booking-form-container p-3">
                <label>Step 2: Select Duration</label>
                <div class="d-flex align-items-center" style="position: relative; top: 8px;">
                    <label class="mr-3" for="duration2hr">
                        <input id="duration2hr" type="radio" name="duration" value="3" required checked>
                        <span>3 hr/s</span>
                    </label>
                    <label class="mb-1" for="duration4hr">
                        <input id="duration4hr" type="radio" name="duration" value="4">
                        <span style="font-weight: normal; position: relative; top: -1.6px;">4 hr/s</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="booking-form-container p-3">
                <span class="value" id="previewPrice" style="width: 100%; height: 100%; font-size: 2.5rem;">₱0.00</span>
            </div>
        </div>
    </div>

    <p class="important-note mt-3">
        <small><i class="fas fa-exclamation-circle"></i> <b>Note:</b> Extension of Hours during Event cost ₱1,800 per
            hour (Fixed at any Event).</small>
    </p>

    <div class="form-group mb-1 packages-selection">
        <label>Step 3: Select a Package:</label>
        <div class="packages-row">
            <!-- Package 1 -->
            <label class="package-card-custom">
                <input type="radio" name="package" value="PhotoStandeeFrame" id="package_1" required>
                <div class="card-body-custom">
                    <div class="text-content">
                        <h5 class="card-title">Photo Standee</h5>
                        <p class="card-text">
                            - Customized Layout<br>
                            - 4 Plastic Standee Frame<br>
                            - 3 Ref Magnets (Single Shot)
                        </p>
                    </div>
                    <img src="/NEW-PM-JI-RESERVIFY/assets/packages/photo_standee_frame.jpg" alt="Photo Standee Frame">
                </div>
            </label>

            <!-- Package 2 -->
            <label class="package-card-custom">
                <input type="radio" name="package" value="PolaroidFrame" id="package_2" required>
                <div class="card-body-custom">
                    <div class="text-content">
                        <h5 class="card-title">Polaroid Frame</h5>
                        <p class="card-text">
                            - Customized Layout<br>
                            - 4 Polaroid Frame<br>
                            - 3 Ref Magnets (Single Shot)
                        </p>
                    </div>
                    <img src="/NEW-PM-JI-RESERVIFY/assets/packages/polaroid_frame.png" alt="Polaroid Frame">
                </div>
            </label>

            <!-- Package 3 -->
            <label class="package-card-custom">
                <input type="radio" name="package" value="PhotoStripFrame" id="package_3" required>
                <div class="card-body-custom">
                    <div class="text-content">
                        <h5 class="card-title">2x6 Photo Strip</h5>
                        <p class="card-text">
                            - Customized Layout<br>
                            - 4 2x6 Photo Strip Frame<br>
                            - 3 Ref Magnets (Single Shot)
                        </p>
                    </div>
                    <img src="/NEW-PM-JI-RESERVIFY/assets/packages/photo_strip_frame.png" alt="2x6 Photo Strip Frame">
                </div>
            </label>
        </div>
    </div>

    <!-- Error Message -->
    <div id="step1-error" class="error-note text-danger" style="display:none;"></div>

    <div class="form-navigation">
        <a href="/NEW-PM-JI-RESERVIFY/pages/customer/home.php" class="btn btn-danger">Cancel</a>
        <button type="button" class="next-btn btn btn-primary">Next</button>
    </div>
</div>

<script src="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step1/booking-step1.js"></script>