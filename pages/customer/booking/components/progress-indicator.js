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

    // show/hide Next and Submit buttons
    if (currentIndex === totalSteps - 1) {
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'inline-block';
    } else {
        if (nextBtn) nextBtn.style.display = 'inline-block';
        if (submitBtn) submitBtn.style.display = 'none';
    }
}

// initialize button visibility on page load
document.addEventListener('DOMContentLoaded', function () {
    updateNavigationButtons(0, 5); // assuming starting at step 0, total 5 steps
});