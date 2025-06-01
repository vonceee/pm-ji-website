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
     * updates the price preview in the DOM AND stores the full price for later use
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
            // Store the full price as a data attribute for Step 5 to access
            previewEl.dataset.fullPrice = price;
            
            // Also update hidden inputs if they exist (in case we're on a later step)
            const fullPriceInput = document.getElementById('fullPriceInput');
            if (fullPriceInput) {
                fullPriceInput.value = price;
            }
            
            // Update Step 5 price display if we're already there
            const step5PricePreview = document.getElementById('step5PricePreview');
            if (step5PricePreview) {
                step5PricePreview.dataset.fullprice = price;
                // Trigger Step 5 price update if the function exists
                if (typeof window.updateStep5PriceDisplay === 'function') {
                    window.updateStep5PriceDisplay();
                }
            }
        }
    }

    return {
        update: updatePricePreview,
        getPrice: getPrice // Expose this for external use
    };
})();

// Make PriceCalculator globally available
window.PriceCalculator = PriceCalculator;

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
        errorDiv.textContent = 'Select an Event.';
        errorDiv.style.display = 'block';
        eventType.focus();
        return false;
    }
    // validate duration
    const duration = document.querySelector('input[name="duration"]:checked');
    if (!duration) {
        errorDiv.textContent = 'Select a Duration.';
        errorDiv.style.display = 'block';
        return false;
    }
    // validate package
    const packageSelected = document.querySelector('input[name="package"]:checked');
    if (!packageSelected) {
        errorDiv.textContent = 'Select a Package.';
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