// tooltip functionality
document.querySelectorAll('.info-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.stopPropagation();

        // hide all tooltips
        document.querySelectorAll('.service-tooltip').forEach(tooltip => {
            tooltip.classList.remove('show');
        });

        // show the relevant tooltip
        const service = this.getAttribute('data-service');
        const tooltip = document.getElementById('tooltip-' + service);
        if (tooltip) {
            tooltip.classList.add('show');
        }
    });
});

// hide tooltips when clicking outside
document.addEventListener('click', function (e) {
    if (!e.target.closest('.service-tooltip') && !e.target.closest('.info-btn')) {
        document.querySelectorAll('.service-tooltip').forEach(tooltip => {
            tooltip.classList.remove('show');
        });
    }
});

// smooth scroll reveal animation
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animationPlayState = 'running';
        }
    });
}, observerOptions);

document.querySelectorAll('.fade-in').forEach(el => {
    el.style.animationPlayState = 'paused';
    observer.observe(el);
});

// enhanced card interactions
document.querySelectorAll('.service-card').forEach(card => {
    card.addEventListener('mouseenter', function () {
        this.style.zIndex = '10';
    });

    card.addEventListener('mouseleave', function () {
        this.style.zIndex = '1';
    });
});

// parallax effect for background
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const bg = document.querySelector('.animated-bg::before');
    if (bg) {
        bg.style.transform = `translateY(${scrolled * 0.5}px)`;
    }
});