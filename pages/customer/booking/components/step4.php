<!-- Step 4: Review Booking -->
<div class="form-step" data-step="4">
    <div class="review-card">
        <h5 class="review-title">Booking Summary</h5>
        <div class="review-columns">
            <!-- Left column -->
            <div class="review-group">
                <h6>Event Info</h6>
                <p><strong>Event:</strong> <span id="previewEventType"></span></p>
                <p><strong>Duration:</strong> <span id="previewDuration"></span></p>
                <p><strong>Date:</strong> <span id="previewDate"></span></p>
                <p><strong>Start:</strong> <span id="previewStartTime"></span></p>
                <p><strong>End:</strong> <span id="previewEndTime"></span></p>
            </div>

            <!-- Right column -->
            <div class="review-group">
                <h6>Location & Package</h6>
                <p><strong>Street:</strong> <span id="previewStreetAddress"></span></p>
                <p><strong>City:</strong> <span id="previewCity"></span></p>
                <p><strong>Barangay:</strong> <span id="previewBarangay"></span></p>
                <p><strong>Full Address:</strong> <span id="previewFullAddress"></span></p>
                <p><strong>Package:</strong> <span id="previewPackages"></span></p>
                <p><strong>Price:</strong> <span id="previewPriceReview"></span></p>
            </div>
        </div>

    </div>

    <div class="form-navigation mt-4">
        <button type="button" class="prev-btn btn btn-secondary">Previous</button>
        <button type="button" class="next-btn btn btn-primary">Next</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // find the step 4 next button
        const step4NextBtn = document.querySelector('.form-step[data-step="4"] .next-btn');
        if (step4NextBtn) {
            step4NextBtn.addEventListener('click', function (e) {
                e.preventDefault();


                // hide step 4, show step 5
                const step4 = document.querySelector('.form-step[data-step="4"]');
                const step5 = document.querySelector('.form-step[data-step="5"]');
                if (step4 && step5) {
                    step4.classList.remove('active');
                    step5.classList.add('active');
                }

                const price = document.getElementById('previewPriceReview')?.textContent || '₱0.00';
                const step5Price = document.getElementById('step5PricePreview');
                if (step5Price) step5Price.textContent = price;
                const bookingPriceInput = document.getElementById('bookingPrice');
                if (bookingPriceInput) bookingPriceInput.value = price.replace(/[^\d.]/g, '');
            });
        }
    });
</script>