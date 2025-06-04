class BookingLoader {
    constructor() {
        this.overlay = document.getElementById('loadingOverlay');
        this.steps = document.querySelectorAll('.loading-step');
        this.currentStep = 0;
        this.stepInterval = null;
    }

    show() {
        this.overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
        this.startStepAnimation();
    }

    hide() {
        this.overlay.classList.remove('show');
        document.body.style.overflow = '';
        this.stopStepAnimation();
        this.resetSteps();
    }

    startStepAnimation() {
        this.currentStep = 0;
        this.stepInterval = setInterval(() => {
            if (this.currentStep < this.steps.length) {
                // mark previous step as completed
                if (this.currentStep > 0) {
                    this.steps[this.currentStep - 1].classList.remove('active');
                    this.steps[this.currentStep - 1].classList.add('completed');
                }

                // activate current step
                this.steps[this.currentStep].classList.add('active');
                this.currentStep++;
            } else {
                this.stopStepAnimation();
            }
        }, 1500); // change step every 1.5 seconds
    }

    stopStepAnimation() {
        if (this.stepInterval) {
            clearInterval(this.stepInterval);
            this.stepInterval = null;
        }
    }

    resetSteps() {
        this.steps.forEach(step => {
            step.classList.remove('active', 'completed');
        });
        this.currentStep = 0;
    }
}

// create global instance
const bookingLoader = new BookingLoader();

// function to integrate with booking form
function showBookingLoader() {
    bookingLoader.show();
}

function hideBookingLoader() {
    bookingLoader.hide();
}

// auto-hide loading on page unload (in case of redirect)
window.addEventListener('beforeunload', () => {
    bookingLoader.hide();
});