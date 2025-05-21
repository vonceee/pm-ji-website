<!-- Step 3: Enter Location -->
<div class="form-step" data-step="3">
    <!-- Note -->
    <p class="important-note mt-3">
        <small><i class="fas fa-exclamation-circle"></i> <b>Note:</b> Bookings are currently available only for
            locations within the National Capital Region (NCR).</small>
    </p>

    <div class="form-group">
        <label for="streetAddress">Street Address</label>
        <input type="text" class="form-control" name="street_address" id="streetAddress" placeholder="e.g., 123 Main St"
            required>
    </div>

    <script>
        document.getElementById('streetAddress').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '');
        });
    </script>

    <!-- City Dropdown -->
    <div class="form-group">
        <label for="citySelect">City</label>
        <select id="citySelect" name="city" class="form-control" required>
            <option value="">Loading…</option>
        </select>
        <input type="hidden" name="city_name" id="cityName">
    </div>

    <!-- Barangay Dropdown -->
    <div class="form-group">
        <label for="barangaySelect">Barangay</label>
        <select id="barangaySelect" name="barangay" class="form-control" required>
            <option value="">select a city first</option>
        </select>
        <input type="hidden" name="barangay_name" id="barangayName">
    </div>

    <!-- Full Address Field -->
    <div class="form-group">
        <label for="fullAddress">Full Address</label>
        <input type="text" class="form-control" name="full_address" id="fullAddress"
            placeholder="e.g., 123 Main St, Barangay, City, NCR" required readonly>
    </div>

    <div id="step3-error" class="error-note text-danger" style="display:none;"></div>

    <div class="form-navigation">
        <button type="button" class="prev-btn btn btn-secondary">Previous</button>
        <button type="button" class="next-btn btn btn-primary">Next</button>
    </div>

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

    document.getElementById('citySelect').addEventListener('change', function() {
        document.getElementById('cityName').value = this.options[this.selectedIndex].text;
    });
    document.getElementById('barangaySelect').addEventListener('change', function() {
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
            errorDiv.textContent = 'enter your street address.';
            errorDiv.style.display = 'block';
            document.getElementById('streetAddress').focus();
            return false;
        }
        if (!city) {
            errorDiv.textContent = 'select a city.';
            errorDiv.style.display = 'block';
            document.getElementById('citySelect').focus();
            return false;
        }
        if (!barangay) {
            errorDiv.textContent = 'select a barangay.';
            errorDiv.style.display = 'block';
            document.getElementById('barangaySelect').focus();
            return false;
        }
        if (!fullAddress) {
            errorDiv.textContent = 'full address is incomplete.';
            errorDiv.style.display = 'block';
            return false;
        }
        return true;
    };
</script>

<script src="/NEW-PM-JI-RESERVIFY/pages/customer/API/location-select.js"></script>