// track active tab for pagination
function setActiveTab(tab) {
    // update URL to include current tab
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    url.searchParams.delete('page'); // Reset page when switching tabs
    window.history.replaceState({}, '', url);
}

// handle tab switching with URL updates
document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function (e) {
            const tabId = e.target.getAttribute('aria-controls');
            setActiveTab(tabId);
        });
    });
});