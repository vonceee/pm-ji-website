function setPaymentDateRange(range) {
    const today = new Date();
    let fromDate, toDate;

    switch (range) {
        case 'today':
            fromDate = toDate = today;
            break;
        case 'this_week':
            fromDate = new Date(today);
            fromDate.setDate(today.getDate() - today.getDay());
            toDate = new Date(today);
            toDate.setDate(fromDate.getDate() + 6);
            break;
        case 'this_month':
            fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
            toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case 'last_30_days':
            fromDate = new Date(today);
            fromDate.setDate(today.getDate() - 30);
            toDate = today;
            break;
        case 'last_3_months':
            fromDate = new Date(today);
            fromDate.setMonth(today.getMonth() - 3);
            toDate = today;
            break;
        case 'this_year':
            fromDate = new Date(today.getFullYear(), 0, 1);
            toDate = new Date(today.getFullYear(), 11, 31);
            break;
    }

    document.getElementById('payment_date_from').value = formatDate(fromDate);
    document.getElementById('payment_date_to').value = formatDate(toDate);
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

// Handle filter form submission
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('paymentFilterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const params = new URLSearchParams();

            // Add form data to URL parameters
            for (let [key, value] of formData.entries()) {
                if (value) {
                    params.append(key, value);
                }
            }

            // Stay on the current page but add filter parameters
            const currentUrl = window.location.pathname;
            const newUrl = currentUrl + (params.toString() ? '?' + params.toString() : '');

            window.location.href = newUrl;
        });
    }

    // Validate date range
    const dateFromInput = document.getElementById('payment_date_from');
    const dateToInput = document.getElementById('payment_date_to');

    if (dateFromInput) {
        dateFromInput.addEventListener('change', function () {
            const fromDate = this.value;

            if (fromDate && dateToInput.value && fromDate > dateToInput.value) {
                dateToInput.value = fromDate;
            }
        });
    }

    if (dateToInput) {
        dateToInput.addEventListener('change', function () {
            const toDate = this.value;

            if (toDate && dateFromInput.value && toDate < dateFromInput.value) {
                dateFromInput.value = toDate;
            }
        });
    }
});