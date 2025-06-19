class EnhancedCarousel {
    constructor(container) {
        this.container = container;
        this.track = container.querySelector('.carousel-track');
        this.slides = container.querySelectorAll('.carousel-slide');
        this.prevBtn = container.querySelector('.carousel-controls.prev');
        this.nextBtn = container.querySelector('.carousel-controls.next');
        this.dots = container.querySelectorAll('.pagination-dot');

        this.currentIndex = 0;
        this.slideCount = this.slides.length;
        this.autoplayInterval = null;
        this.progressInterval = null;
        this.autoplayDelay = 6000;
        this.isPlaying = true;
        this.touchStartX = 0;
        this.touchEndX = 0;

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.startAutoplay();
        this.updateProgress();

        // preload next image
        this.preloadImages();

        // initialize ARIA attributes
        this.updateARIA();
    }

    setupEventListeners() {
        // navigation buttons
        this.prevBtn?.addEventListener('click', () => {
            this.pauseAutoplay();
            this.previousSlide();
            this.startAutoplay();
        });

        this.nextBtn?.addEventListener('click', () => {
            this.pauseAutoplay();
            this.nextSlide();
            this.startAutoplay();
        });

        // pagination dots
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                this.pauseAutoplay();
                this.goToSlide(index);
                this.startAutoplay();
            });
        });

        // keyboard navigation
        this.container.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                this.pauseAutoplay();
                this.previousSlide();
                this.startAutoplay();
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                this.pauseAutoplay();
                this.nextSlide();
                this.startAutoplay();
            }
        });

        // touch/swipe support
        this.track.addEventListener('touchstart', (e) => {
            this.touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        this.track.addEventListener('touchend', (e) => {
            this.touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe();
        }, { passive: true });

        // pause on hover
        this.container.addEventListener('mouseenter', () => {
            if (this.isPlaying) {
                this.pauseAutoplay();
            }
        });

        this.container.addEventListener('mouseleave', () => {
            if (!this.isPlaying) {
                this.startAutoplay();
            }
        });

        // visibility API for performance
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseAutoplay();
            } else {
                this.startAutoplay();
            }
        });

        // scroll indicator
        const scrollIndicator = this.container.querySelector('.scroll-down-indicator');
        scrollIndicator?.addEventListener('click', () => {
            window.scrollTo({
                top: window.innerHeight,
                behavior: 'smooth'
            });
        });
    }

    handleSwipe() {
        const swipeThreshold = 50;
        const diff = this.touchStartX - this.touchEndX;

        if (Math.abs(diff) > swipeThreshold) {
            this.pauseAutoplay();
            if (diff > 0) {
                this.nextSlide();
            } else {
                this.previousSlide();
            }
            this.startAutoplay();
        }
    }

    goToSlide(index) {
        if (index === this.currentIndex) return;

        // remove active classes
        this.slides[this.currentIndex]?.classList.remove('active');
        this.dots[this.currentIndex]?.classList.remove('active');

        // update current index
        this.currentIndex = index;

        // add active classes
        this.slides[this.currentIndex]?.classList.add('active');
        this.dots[this.currentIndex]?.classList.add('active');

        // move track
        const translateX = -this.currentIndex * 100;
        this.track.style.transform = `translateX(${translateX}%)`;

        // update ARIA
        this.updateARIA();

        // reset and restart progress
        this.updateProgress();
    }

    nextSlide() {
        const nextIndex = (this.currentIndex + 1) % this.slideCount;
        this.goToSlide(nextIndex);
    }

    previousSlide() {
        const prevIndex = (this.currentIndex - 1 + this.slideCount) % this.slideCount;
        this.goToSlide(prevIndex);
    }

    startAutoplay() {
        if (!this.isPlaying) return;

        this.pauseAutoplay();
        this.autoplayInterval = setInterval(() => {
            this.nextSlide();
        }, this.autoplayDelay);

        this.updateProgress();
    }

    pauseAutoplay() {
        if (this.autoplayInterval) {
            clearInterval(this.autoplayInterval);
            this.autoplayInterval = null;
        }
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }
    }

    updateProgress() {
        if (!this.progressBar) return;

        this.progressBar.style.transform = 'scaleX(0)';

        if (this.progressInterval) {
            clearInterval(this.progressInterval);
        }

        let progress = 0;
        const increment = 100 / (this.autoplayDelay / 50);

        this.progressInterval = setInterval(() => {
            progress += increment;
            this.progressBar.style.transform = `scaleX(${Math.min(progress, 100) / 100})`;

            if (progress >= 100) {
                clearInterval(this.progressInterval);
            }
        }, 50);
    }

    updateARIA() {
        // update slide ARIA attributes
        this.slides.forEach((slide, index) => {
            const isActive = index === this.currentIndex;
            slide.setAttribute('aria-hidden', !isActive);
            if (isActive) {
                slide.setAttribute('aria-live', 'polite');
            } else {
                slide.removeAttribute('aria-live');
            }
        });

        // update dot ARIA attributes
        this.dots.forEach((dot, index) => {
            dot.setAttribute('aria-selected', index === this.currentIndex);
        });
    }

    preloadImages() {
        this.slides.forEach((slide, index) => {
            if (index !== this.currentIndex) {
                const img = slide.querySelector('img');
                if (img && img.loading === 'lazy') {
                    // force load the next image
                    if (index === (this.currentIndex + 1) % this.slideCount) {
                        img.loading = 'eager';
                    }
                }
            }
        });
    }
}

// initialize carousel when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const carouselContainer = document.querySelector('.carousel-container');
    if (carouselContainer) {
        new EnhancedCarousel(carouselContainer);
    }
});

// smooth scroll polyfill for older browsers
if (!('scrollBehavior' in document.documentElement.style)) {
    const scrollIndicator = document.querySelector('.scroll-down-indicator');
    scrollIndicator?.addEventListener('click', () => {
        const start = window.pageYOffset;
        const target = window.innerHeight;
        const distance = target - start;
        const duration = 1000;
        let startTime = null;

        function animation(currentTime) {
            if (startTime === null) startTime = currentTime;
            const timeElapsed = currentTime - startTime;
            const run = ease(timeElapsed, start, distance, duration);
            window.scrollTo(0, run);
            if (timeElapsed < duration) requestAnimationFrame(animation);
        }

        function ease(t, b, c, d) {
            t /= d / 2;
            if (t < 1) return c / 2 * t * t + b;
            t--;
            return -c / 2 * (t * (t - 2) - 1) + b;
        }

        requestAnimationFrame(animation);
    });
}