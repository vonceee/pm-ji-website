function setDateRange(range) {
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
    }

    document.getElementById('date_from').value = formatDate(fromDate);
    document.getElementById('date_to').value = formatDate(toDate);
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

// handle form submission to stay on bookings page
document.getElementById('filterForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const params = new URLSearchParams();

    // add form data to URL parameters
    for (let [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }

    // stay on the current page but add filter parameters
    const currentUrl = window.location.pathname;
    const newUrl = currentUrl + (params.toString() ? '?' + params.toString() : '');

    window.location.href = newUrl;
});

// validate date range
document.getElementById('date_from').addEventListener('change', function () {
    const fromDate = this.value;
    const toDateInput = document.getElementById('date_to');

    if (fromDate && toDateInput.value && fromDate > toDateInput.value) {
        toDateInput.value = fromDate;
    }
});

document.getElementById('date_to').addEventListener('change', function () {
    const toDate = this.value;
    const fromDateInput = document.getElementById('date_from');

    if (toDate && fromDateInput.value && toDate < fromDateInput.value) {
        fromDateInput.value = toDate;
    }
});