
// Payment Tab Management JavaScript
// Following the same pattern as bookings-tab

// Set active payment tab and update URL
function setActivePaymentTab(tabName) {
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    url.searchParams.set('view', 'payments');
    url.searchParams.delete('page'); // Reset page when switching tabs
    
    // Preserve other parameters like filters
    const currentParams = new URLSearchParams(window.location.search);
    ['payment_date_from', 'payment_date_to', 'search'].forEach(param => {
        if (currentParams.has(param)) {
            url.searchParams.set(param, currentParams.get(param));
        }
    });
    
    window.location.href = url.toString();
}
