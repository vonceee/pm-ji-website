document.querySelectorAll('.info-btn').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        // hide all tooltips first
        document.querySelectorAll('.service-tooltip').forEach(function (tip) {
            tip.style.display = 'none';
        });
        // show the relevant tooltip
        const service = btn.getAttribute('data-service');
        const tooltip = document.getElementById('tooltip-' + service);
        if (tooltip) {
            tooltip.style.display = 'block';
        }
    });
});

// hide tooltip when clicking outside
document.addEventListener('click', function () {
    document.querySelectorAll('.service-tooltip').forEach(function (tip) {
        tip.style.display = 'none';
    });
});