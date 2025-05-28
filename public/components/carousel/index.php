<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Carousel Component</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/public/components/carousel/index.css">
</head>
<body>
    <div class="carousel-container">
        <!-- Enhanced Carousel -->
        <div class="carousel" role="region" aria-label="Image carousel" aria-live="polite">
            <div class="carousel-track">
                <div class="carousel-slide active">
                    <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1920&h=1080&fit=crop" alt="Photo booth setup with colorful backdrop" loading="eager">
                </div>
                <div class="carousel-slide">
                    <img src="https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=1920&h=1080&fit=crop" alt="Wedding celebration with photo booth" loading="lazy">
                </div>
                <div class="carousel-slide">
                    <img src="https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=1920&h=1080&fit=crop" alt="Birthday party photo booth fun" loading="lazy">
                </div>
            </div>

            <!-- Modern Control Buttons -->
            <button class="carousel-controls prev" aria-label="Previous image" type="button">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="carousel-controls next" aria-label="Next image" type="button">
                <i class="fas fa-chevron-right"></i>
            </button>

            <!-- Pagination Dots -->
            <div class="carousel-pagination" role="tablist" aria-label="Carousel pagination">
                <button class="pagination-dot active" aria-label="Go to slide 1" data-slide="0" role="tab"></button>
                <button class="pagination-dot" aria-label="Go to slide 2" data-slide="1" role="tab"></button>
                <button class="pagination-dot" aria-label="Go to slide 3" data-slide="2" role="tab"></button>
            </div>

            <!-- Progress Bar -->
            <div class="carousel-progress"></div>
        </div>

        <!-- Hero Content Overlay -->
        <div class="hero-content">
            <img src="/NEW-PM-JI-RESERVIFY/assets/logo/PM&JI-logo.png" alt="PM&JI Reservify Logo" class="hero-logo">
            <h1 class="hero-title">PM&JI Reservify</h1>
            <p class="hero-tagline">
                Capture memories in style! 📸 Our Photo Booth Rental offers professional prints for Christenings,
                Birthdays & more. Let's make your occasion unforgettable!
            </p>
            <a href="#" class="hero-button" data-toggle="modal" data-target="#loginModal">Book now!</a>
        </div>

        <!-- Scroll Down Indicator -->
        <div class="scroll-down-indicator" role="button" tabindex="0" aria-label="Scroll down to see more">
            <span class="scroll-down-text">scroll down to see more</span>
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>

    <script>
        class EnhancedCarousel {
            constructor(container) {
                this.container = container;
                this.track = container.querySelector('.carousel-track');
                this.slides = container.querySelectorAll('.carousel-slide');
                this.prevBtn = container.querySelector('.carousel-controls.prev');
                this.nextBtn = container.querySelector('.carousel-controls.next');
                this.dots = container.querySelectorAll('.pagination-dot');
                this.progressBar = container.querySelector('.carousel-progress');
                
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
                
                // Preload next image
                this.preloadImages();
                
                // Initialize ARIA attributes
                this.updateARIA();
            }

            setupEventListeners() {
                // Navigation buttons
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

                // Pagination dots
                this.dots.forEach((dot, index) => {
                    dot.addEventListener('click', () => {
                        this.pauseAutoplay();
                        this.goToSlide(index);
                        this.startAutoplay();
                    });
                });

                // Keyboard navigation
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

                // Touch/swipe support
                this.track.addEventListener('touchstart', (e) => {
                    this.touchStartX = e.changedTouches[0].screenX;
                }, { passive: true });

                this.track.addEventListener('touchend', (e) => {
                    this.touchEndX = e.changedTouches[0].screenX;
                    this.handleSwipe();
                }, { passive: true });

                // Pause on hover
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

                // Visibility API for performance
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        this.pauseAutoplay();
                    } else {
                        this.startAutoplay();
                    }
                });

                // Scroll indicator
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

                // Remove active classes
                this.slides[this.currentIndex]?.classList.remove('active');
                this.dots[this.currentIndex]?.classList.remove('active');

                // Update current index
                this.currentIndex = index;

                // Add active classes
                this.slides[this.currentIndex]?.classList.add('active');
                this.dots[this.currentIndex]?.classList.add('active');

                // Move track
                const translateX = -this.currentIndex * 100;
                this.track.style.transform = `translateX(${translateX}%)`;

                // Update ARIA
                this.updateARIA();
                
                // Reset and restart progress
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
                // Update slide ARIA attributes
                this.slides.forEach((slide, index) => {
                    const isActive = index === this.currentIndex;
                    slide.setAttribute('aria-hidden', !isActive);
                    if (isActive) {
                        slide.setAttribute('aria-live', 'polite');
                    } else {
                        slide.removeAttribute('aria-live');
                    }
                });

                // Update dot ARIA attributes
                this.dots.forEach((dot, index) => {
                    dot.setAttribute('aria-selected', index === this.currentIndex);
                });
            }

            preloadImages() {
                this.slides.forEach((slide, index) => {
                    if (index !== this.currentIndex) {
                        const img = slide.querySelector('img');
                        if (img && img.loading === 'lazy') {
                            // Force load the next image
                            if (index === (this.currentIndex + 1) % this.slideCount) {
                                img.loading = 'eager';
                            }
                        }
                    }
                });
            }
        }

        // Initialize carousel when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            const carouselContainer = document.querySelector('.carousel-container');
            if (carouselContainer) {
                new EnhancedCarousel(carouselContainer);
            }
        });

        // Smooth scroll polyfill for older browsers
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
    </script>
</body>
</html>