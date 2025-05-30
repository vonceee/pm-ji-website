function updateStepIndicator(currentIndex) {
    const targetStep = currentIndex + 1;
    const steps = document.querySelectorAll('.progress-step');
    const totalSteps = steps.length;

    steps.forEach(step => {
        const stepNum = Number(step.getAttribute('data-step'));
        step.classList.remove('active', 'completed');

        if (stepNum < targetStep) {
            step.classList.add('completed');
        } else if (stepNum === targetStep) {
            step.classList.add('active');
        }
    });

    // update navigation buttons visibility
    updateNavigationButtons(currentIndex, totalSteps);

    // update progress line with animation
    const completedSteps = Math.max(0, targetStep - 1);
    const progressPercentage = (completedSteps / (totalSteps - 1)) * 100;

    // create or update dynamic style for progress line
    const styleElement = document.getElementById('progress-style') || document.createElement('style');
    styleElement.id = 'progress-style';
    styleElement.textContent = `.step-list::after { width: ${progressPercentage}% !important; }`;

    if (!document.getElementById('progress-style')) {
        document.head.appendChild(styleElement);
    }
}

function updateNavigationButtons(currentIndex, totalSteps) {
    const prevBtn = document.querySelector('.form-navigation-top .prev-btn');
    const nextBtn = document.querySelector('.form-navigation-top .next-btn');
    const submitBtn = document.querySelector('.form-navigation-top .submit-btn');

    // Always show previous button but disable it on first step
    if (prevBtn) {
        prevBtn.style.display = 'inline-flex';
        if (currentIndex === 0) {
            prevBtn.disabled = true;
            prevBtn.style.opacity = '0.4';
        } else {
            prevBtn.disabled = false;
            prevBtn.style.opacity = '1';
        }
    }

    // show/hide Next and Submit buttons
    if (currentIndex === totalSteps - 1) {
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'inline-flex';
    } else {
        if (nextBtn) nextBtn.style.display = 'inline-flex';
        if (submitBtn) submitBtn.style.display = 'none';
    }
}

// initialize button visibility on page load
document.addEventListener('DOMContentLoaded', function () {
    // Start at step 0, total 5 steps
    updateNavigationButtons(0, 5);
    
    // Add smooth transitions to all navigation buttons
    const navButtons = document.querySelectorAll('.form-navigation-top .btn');
    navButtons.forEach(button => {
        button.style.transition = 'all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
    });
    
    // Initialize step indicator
    updateStepIndicator(0);
});