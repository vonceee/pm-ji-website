<?php

$preselectedEvent = isset($_GET['event']) ? htmlspecialchars($_GET['event']) : '';

?>

<div class="form-step active" data-step="1">
    <div class="form-row align-items-end">
        <div class="col-md-5 mb-2">
            <div class="booking-form-container p-3">
                <label for="eventType">Step 1: Select an Event</label>
                <select class="form-control" name="event_type" id="eventType" required>
                    <option value="" disabled <?= empty($preselectedEvent) ? 'selected' : '' ?>>select event type
                    </option>
                    <option value="Baptism" <?= $preselectedEvent === 'Baptism' ? 'selected' : '' ?>>Baptism</option>
                    <option value="Birthday" <?= $preselectedEvent === 'Birthday' ? 'selected' : '' ?>>Birthday</option>
                    <option value="Corporate Event" <?= $preselectedEvent === 'Corporate Event' ? 'selected' : '' ?>>
                        Corporate Event</option>
                    <option value="Reunion" <?= $preselectedEvent === 'Reunion' ? 'selected' : '' ?>>Reunion</option>
                    <option value="Wedding" <?= $preselectedEvent === 'Wedding' ? 'selected' : '' ?>>Wedding</option>
                </select>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="booking-form-container p-3">
                <label>Step 2: Select Duration</label>
                <div class="d-flex align-items-center" style="position: relative; top: 8px;">
                    <label class="mr-3 mb-0" for="duration2hr">
                        <input id="duration2hr" type="radio" name="duration" value="3" required checked>
                        <span style="font-weight: normal; position: relative; top: -1.6px;">3 hr/s</span>
                    </label>
                    <label class="mb-0" for="duration4hr">
                        <input id="duration4hr" type="radio" name="duration" value="4">
                        <span style="font-weight: normal; position: relative; top: -1.6px;">4 hr/s</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="booking-form-container p-3">
                <label>Price Preview</label>
                <span class="value" id="previewPrice" style="position: relative; top: 8px;">₱0.00</span>
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
        <button type="button" class="next-btn btn-primary">Next</button>
    </div>
</div>

<script>
    /**
     * handles price calculation based on selected event type and duration
     */
    const PriceCalculator = (() => {
        const basePrices = {
            'Baptism': 4500,
            'Birthday': 4000,
            'Corporate Event': 7000,
            'Reunion': 5000,
            'Wedding': 5000
        };

        const durationOverrides = {
            4: {
                'Baptism': 4600,
                'Birthday': 4500,
                'Corporate Event': 8000,
                'Reunion': 6500,
                'Wedding': 11000
            }
        };

        /**
         * gets the price based on event type and duration
         */
        function getPrice(eventType, duration) {
            const override = durationOverrides[duration];
            if (override && override[eventType] !== undefined) {
                return override[eventType];
            }
            return basePrices[eventType] || 0;
        }

        /**
         * formats a number into a currency string
         */
        function formatCurrency(amount) {
            return `₱${amount.toLocaleString()}.00`;
        }

        /**
         * updates the price preview in the DOM
         */
        function updatePricePreview() {
            const eventType = document.getElementById('eventType')?.value;
            const durationInput = document.querySelector('input[name="duration"]:checked');

            if (!eventType || !durationInput) return;

            const duration = parseInt(durationInput.value, 10);
            const price = getPrice(eventType, duration);
            const previewEl = document.getElementById('previewPrice');

            if (previewEl) {
                previewEl.textContent = formatCurrency(price);
            }
        }

        return {
            update: updatePricePreview
        };
    })();

    // initialize price update on relevant changes
    document.addEventListener('DOMContentLoaded', () => {
        // initial load
        PriceCalculator.update();

        // bind listeners
        document.getElementById('eventType')?.addEventListener('change', PriceCalculator.update);
        document.querySelectorAll('input[name="duration"]').forEach(input => {
            input.addEventListener('change', PriceCalculator.update);
        });
    });

    // step 1 validation function
    function validateStep1() {
        const errorDiv = document.getElementById('step1-error');
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';

        // validate event type
        const eventType = document.getElementById('eventType');
        if (!eventType.value) {
            errorDiv.textContent = 'select an event type.';
            errorDiv.style.display = 'block';
            eventType.focus();
            return false;
        }
        // validate duration
        const duration = document.querySelector('input[name="duration"]:checked');
        if (!duration) {
            errorDiv.textContent = 'select a duration.';
            errorDiv.style.display = 'block';
            return false;
        }
        // validate package
        const packageSelected = document.querySelector('input[name="package"]:checked');
        if (!packageSelected) {
            errorDiv.textContent = 'select a package.';
            errorDiv.style.display = 'block';
            return false;
        }
        return true;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const nextBtn = document.querySelector('.form-step[data-step="1"] .next-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', function (e) {
                if (!validateStep1()) {
                    e.preventDefault();
                    return false;
                }
                // navigation to next step is handled globally in index.php
            });
        }
    });
</script>