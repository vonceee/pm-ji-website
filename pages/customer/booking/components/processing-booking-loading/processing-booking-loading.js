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
                // Mark previous step as completed
                if (this.currentStep > 0) {
                    this.steps[this.currentStep - 1].classList.remove('active');
                    this.steps[this.currentStep - 1].classList.add('completed');
                }

                // Activate current step
                this.steps[this.currentStep].classList.add('active');
                this.currentStep++;
            } else {
                this.stopStepAnimation();
            }
        }, 1500); // Change step every 1.5 seconds
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

// Create global instance
const bookingLoader = new BookingLoader();

// Demo function
function showLoadingDemo() {
    bookingLoader.show();

    // Hide after 8 seconds for demo
    setTimeout(() => {
        bookingLoader.hide();
    }, 8000);
}

// Function to integrate with booking form
function showBookingLoader() {
    bookingLoader.show();
}

function hideBookingLoader() {
    bookingLoader.hide();
}

// Auto-hide loading on page unload (in case of redirect)
window.addEventListener('beforeunload', () => {
    bookingLoader.hide();
});
