<!-- Step 3: Enter Location -->
<div class="form-step" data-step="3">
    <!-- Important Note -->
    <div class="important-note">
        <i class="fas fa-exclamation-circle"></i>
        <div class="important-note-text">
            <b>Note:</b> Bookings are currently available only for
            locations within the National Capital Region (NCR).
        </div>
    </div>

    <div id="step3-error" class="error-message"></div>

    <div class="form-group">
        <label for="streetAddress">Street Address <span style="color: red">*</span></label>
        <input type="text" class="form-control" name="street_address" id="streetAddress" form="reservationForm"
            placeholder="e.g., 123 Main St" required>
    </div>

    <script>
        document.getElementById('streetAddress').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '');
        });
    </script>

    <!-- City Dropdown -->
    <div class="form-group">
        <label for="citySelect">City <span style="color: red">*</span></label>
        <select id="citySelect" name="city" class="form-control" required>
            <option value="">Loading…</option>
        </select>
        <input type="hidden" name="city_name" id="cityName" form="reservationForm">
    </div>

    <!-- Barangay Dropdown -->
    <div class="form-group">
        <label for="barangaySelect">Barangay <span style="color: red">*</span></label>
        <select id="barangaySelect" name="barangay" class="form-control" required>
            <option value="">Select City First</option>
        </select>
        <input type="hidden" name="barangay_name" id="barangayName" form="reservationForm">
    </div>

    <!-- Full Address Field -->
    <div class="form-group">
        <label for="fullAddress">Full Address</label>
        <input type="text" class="form-control" name="full_address" id="fullAddress" form="reservationForm"
            placeholder="e.g., 123 Main St, Barangay, City, NCR" required readonly>
    </div>

    <!--
    <script>
        document.querySelector('.form-step[data-step="3"] .next-btn').addEventListener('click', function () {
            if (validateStep3()) {
                // update preview values for step 4
                document.getElementById('previewStreetAddress').textContent = document.getElementById('streetAddress').value;
                document.getElementById('previewCity').textContent = document.getElementById('citySelect').options[document.getElementById('citySelect').selectedIndex].text;
                document.getElementById('previewBarangay').textContent = document.getElementById('barangaySelect').options[document.getElementById('barangaySelect').selectedIndex].text;
                document.getElementById('previewFullAddress').textContent = document.getElementById('fullAddress').value;
            }
        });
    </script>
    -->
</div>

<script>
    // Auto-fill Full Address
    function updateFullAddress() {
        const street = document.getElementById('streetAddress').value.trim();
        const citySelect = document.getElementById('citySelect');
        const barangaySelect = document.getElementById('barangaySelect');
        const city = citySelect.options[citySelect.selectedIndex]?.text || '';
        const barangay = barangaySelect.options[barangaySelect.selectedIndex]?.text || '';
        let parts = [];
        if (street) parts.push(street);
        if (barangay && barangaySelect.value) parts.push(barangay);
        if (city && citySelect.value) parts.push(city);
        parts.push('NCR');
        document.getElementById('fullAddress').value = parts.join(', ');
    }

    document.getElementById('streetAddress').addEventListener('input', updateFullAddress);
    document.getElementById('citySelect').addEventListener('change', updateFullAddress);
    document.getElementById('barangaySelect').addEventListener('change', updateFullAddress);

    document.getElementById('citySelect').addEventListener('change', function () {
        document.getElementById('cityName').value = this.options[this.selectedIndex].text;
    });
    document.getElementById('barangaySelect').addEventListener('change', function () {
        document.getElementById('barangayName').value = this.options[this.selectedIndex].text;
    });
</script>

<script>
    window.validateStep3 = function () {
        const errorDiv = document.getElementById('step3-error');
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';

        const street = document.getElementById('streetAddress').value.trim();
        const city = document.getElementById('citySelect').value;
        const barangay = document.getElementById('barangaySelect').value;
        const fullAddress = document.getElementById('fullAddress').value.trim();

        if (!street) {
            errorDiv.textContent = 'Fill Street Address.';
            errorDiv.style.display = 'block';
            document.getElementById('streetAddress').focus();
            return false;
        }
        if (!city) {
            errorDiv.textContent = 'Select City.';
            errorDiv.style.display = 'block';
            document.getElementById('citySelect').focus();
            return false;
        }
        if (!barangay) {
            errorDiv.textContent = 'Select Barangay.';
            errorDiv.style.display = 'block';
            document.getElementById('barangaySelect').focus();
            return false;
        }
        if (!fullAddress) {
            errorDiv.textContent = 'Full Address is Incomplete.';
            errorDiv.style.display = 'block';
            return false;
        }
        return true;
    };
</script>

<script src="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step3/location-select.js"></script>