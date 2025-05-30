document.addEventListener('DOMContentLoaded', () => {
    const qrContainer = document.getElementById('qrContainer');
    const qrImage = document.getElementById('qrImage');
    const qrDetails = document.getElementById('qrDetails');
    const qrLogo = document.getElementById('qrLogo');
    const pricePreview = document.getElementById('step5PricePreview');
    const bookingPriceInput = document.getElementById('bookingPrice');
    const fullPriceInput = document.getElementById('fullPriceInput');

    function parsePrice(str) {
        return parseFloat(str.replace(/[^\d.]/g, '')) || 0;
    }

    function formatCurrency(amount) {
        return '₱' + amount.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function updatePriceDisplay() {
        const paymentType = document.querySelector('input[name="payment_type"]:checked');
        const full = parseFloat(pricePreview.dataset.fullprice) || 0;

        let displayPrice = full;
        if (paymentType && paymentType.value === 'Down Payment') {
            displayPrice = full / 2;
        }

        pricePreview.textContent = formatCurrency(displayPrice);
        bookingPriceInput.value = displayPrice;
    }

    // Listen for changes to payment type
    document.querySelectorAll('input[name="payment_type"]').forEach(radio => {
        radio.addEventListener('change', updatePriceDisplay);
    });

    // Step 5 initialization function - called from index.php
    window.initializeStep5 = function () {
        const previewPriceReview = document.getElementById('previewPriceReview');
        const fullPrice = parsePrice(previewPriceReview?.textContent || '0');
        pricePreview.dataset.fullprice = fullPrice;
        if (fullPriceInput) fullPriceInput.value = fullPrice;
        updatePriceDisplay();
    };

    // QR Logic
    const qrPaths = {
        'GCash': '/NEW-PM-JI-RESERVIFY/assets/qr/gcash.png',
        'Paymaya': '/NEW-PM-JI-RESERVIFY/assets/qr/paymaya.png'
    };

    const logoPaths = {
        'GCash': '/NEW-PM-JI-RESERVIFY/assets/qr/gcash-logo.png',
        'Paymaya': '/NEW-PM-JI-RESERVIFY/assets/qr/paymaya-logo.png'
    };

    const paymentDetails = {
        'GCash': `
        <b>Account Name:</b> CL****L B.<br>
        <b>Mobile No.:</b> 091* ****138<br>
        <b>UserID:</b> ************WG22IK
    `,
        'Paymaya': `
        <b>Account Name:</b> CLEZIEL BERNIL<br>
        <b>Mobile No.:</b> +63 *** *** 2138<br>
        <b>UserID:</b> @cibernil
    `
    };

    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', () => {
            if (!radio.checked) return;
            const method = radio.value;
            const path = qrPaths[method] || '';
            const logo = logoPaths[method] ? `<img src="${logoPaths[method]}" alt="${method} Logo" style="height:32px;vertical-align:middle;margin-right:8px;">` : '';
            qrImage.src = path;
            qrImage.alt = path ? `${method} QR Code` : 'QR Code';
            qrDetails.innerHTML = paymentDetails[method] || '';
            qrLogo.innerHTML = logo;
            qrContainer.style.display = path ? 'block' : 'none';
        });
    });

    const gcashRadio = document.getElementById('paymentGCash');
    if (gcashRadio) {
        gcashRadio.checked = true;
        gcashRadio.dispatchEvent(new Event('change'));
    }
});