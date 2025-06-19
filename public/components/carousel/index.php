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
        <!-- Carousel -->
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

            <!-- Control Buttons -->
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

    <script src="/NEW-PM-JI-RESERVIFY/public/components/carousel/index.js"></script>
</body>
</html>