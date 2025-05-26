<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.9">
    <title>PM&JI Reservify</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- End Bootstrap CSS -->


    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/index.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/components/top-header.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/components/footer.css">
    <!-- End Custom CSS -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />

    <!-- Loading CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />
    <!-- End Loading CSS -->


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Loading Animation JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
    <!-- End Loading Animation JS -->

</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/top-header.php'; ?>

    <section class="hero-section">
        <!-- Carousel Background -->
        <div class="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="/NEW-PM-JI-RESERVIFY/assets/carousel/sample7.jpg" alt="Image 1">
                </div>
                <div class="carousel-item">
                    <img src="/NEW-PM-JI-RESERVIFY/assets/carousel/sample8.jpg" alt="Image 2">
                </div>
                <div class="carousel-item">
                    <img src="/NEW-PM-JI-RESERVIFY/assets/carousel/sample9.jpg" alt="Image 3">
                </div>
            </div>
            <span class="carousel-control prev">&#10094;</span>
            <span class="carousel-control next">&#10095;</span>
        </div>

        <!-- Hero Content Overlay -->
        <div class="hero-content">
            <img src="assets/logo/PM&JI-logo.png" alt="PM&JI Reservify Logo" class="hero-logo">
            <h1 class="hero-title">PM&JI Reservify</h1>
            <p class="hero-tagline">
                Capture memories in style! 📸 Our Photo Booth Rental offers professional prints for Christenings,
                Birthdays & more. Let's make your occasion unforgettable!
            </p>
            <a href="#" class="hero-button" data-toggle="modal" data-target="#loginModal">Book now!</a>
        </div>
        <div class="scroll-down-indicator"
            onclick="document.getElementById('services-section').scrollIntoView({ behavior: 'smooth' });">
            <span class="scroll-down-text">scroll down to see more</span>
            <i class="fas fa-chevron-down"></i>
        </div>

    </section>

    <!-- Services Section -->
    <section id="services-section">

        <!-- Curly SVG Divider -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100">
            <g fill="var(--primary-bg)">
                <path d="M0 1v99c134.3 0 153.7-99 296-99H0Z" opacity=".5"></path>
                <path d="M1000 4v86C833.3 90 833.3 3.6 666.7 3.6S500 90 333.3 90 166.7 4 0 4h1000Z" opacity=".5"></path>
                <path d="M617 1v86C372 119 384 1 196 1h421Z" opacity=".5"></path>
                <path d="M1000 0H0v52C62.5 28 125 4 250 4c250 0 250 96 500 96 125 0 187.5-24 250-48V0Z"></path>
            </g>
        </svg>

        <div class="service-container">
            <!-- Modified Service Inclusions & Horizontal Testimonials Row -->
            <div class="container mb-4">
                <div class="row">
                    <!-- Service Inclusions Column (left side) -->
                    <div class="col-md-5">
                        <div class="service-inclusions">
                            <h5>All Packages Includes</h5>
                            <ul>
                                <li>📸 Unlimited Shots</li>
                                <li>🖼️ Personalized Photo Layout</li>
                                <li>💎 High-Quality Photo (4 Frames)</li>
                                <li>🎨 Custom Layouts (According to Event Theme)</li>
                                <li>☁️ Soft Copy of All Photos (via Google Drive)</li>
                            </ul>
                        </div>
                    </div>
                    <!-- Horizontal Testimonials Column (right side) -->
                    <div class="col-md-7">
                        <h5>What Our Clients Say!</h5>
                        <div class="testimonials-container">
                            <!-- Testimonial Card 1 -->
                            <div class="testimonial-card">
                                <div class="testimonial-header">
                                    <img src="/NEW-PM-JI-RESERVIFY/assets/profile/profile01.jpg" alt="Profile 1"
                                        class="profile-pic">
                                    <div class="user-info">
                                        <span class="username">Yun-ah (노윤아)</span>
                                        <div class="rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </div>
                                    </div>
                                </div>
                                <blockquote>
                                    "Excellent service and exceptional quality – highly recommend!"
                                </blockquote>
                            </div>
                            <!-- Testimonial Card 2 -->
                            <div class="testimonial-card">
                                <div class="testimonial-header">
                                    <img src="/NEW-PM-JI-RESERVIFY/assets/profile/profile02.jpg" alt="Profile 2"
                                        class="profile-pic">
                                    <div class="user-info">
                                        <span class="username">Minju (민주)</span>
                                        <div class="rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                                <blockquote>
                                    "Professional, timely, and creative. Our event was unforgettable!"
                                </blockquote>
                            </div>
                            <!-- Testimonial Card 3 -->
                            <div class="testimonial-card">
                                <div class="testimonial-header">
                                    <img src="/NEW-PM-JI-RESERVIFY/assets/profile/profile03.jpg" alt="Profile 3"
                                        class="profile-pic">
                                    <div class="user-info">
                                        <span class="username">Moka (모카)</span>
                                        <div class="rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                                <blockquote>
                                    "Friendly staff and beautiful photo layouts. Will book again!"
                                </blockquote>
                            </div>
                            <!-- Testimonial Card 4 -->
                            <div class="testimonial-card">
                                <div class="testimonial-header">
                                    <img src="/NEW-PM-JI-RESERVIFY/assets/profile/profile04.jpg" alt="Profile 4"
                                        class="profile-pic">
                                    <div class="user-info">
                                        <span class="username">Wonhee (원희)</span>
                                        <div class="rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </div>
                                    </div>
                                </div>
                                <blockquote>
                                    "Great value for money and super easy to work with."
                                </blockquote>
                            </div>
                            <!-- Testimonial Card 5 -->
                            <div class="testimonial-card">
                                <div class="testimonial-header">
                                    <img src="/NEW-PM-JI-RESERVIFY/assets/profile/profile05.jpg" alt="Profile 5"
                                        class="profile-pic">
                                    <div class="user-info">
                                        <span class="username">Iroha (이로하)</span>
                                        <div class="rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                                <blockquote>
                                    "Our guests loved the booth! The prints were amazing."
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container">
                <div class="row">

                    <!-- Baptism Service Card -->
                    <div class="col-md-6 col-lg-4 mb-4" id="baptism-card">
                        <div class="service-card card h-100 position-relative">
                            <div class="card-body">
                                <div class="service-icon">
                                    <i class="fas fa-church"></i>
                                </div>
                                <h5 class="card-title">Baptism</h5>
                                <p class="card-text">
                                    Capture moments with professional coverage.
                                </p>
                                <ul class="pricing-list">
                                    <li><strong>3 Hours:</strong> ₱4,500 <span>(50% down: ₱2,250)</span></li>
                                    <li><strong>4 Hours:</strong> ₱4,600 <span>(50% down: ₱2,300)</span></li>
                                </ul>
                                <button type="button" class="btn btn-info info-btn position-absolute"
                                    style="bottom: 16px; right: 16px;" data-service="baptism">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <div class="service-tooltip" id="tooltip-baptism" style="display:none;">
                                    <strong>Baptism Inclusions:</strong>
                                    <ul>
                                        <li>Unlimited photo sessions</li>
                                        <li>Customized baptism-themed photo layout (with baby’s name & date)</li>
                                        <li>Soft pastel or church-inspired backdrop options</li>
                                        <li>Clean & sanitized baby-friendly props (angel wings, halos, crosses, etc.)
                                        </li>
                                        <li>Free digital copy via Google Drive</li>
                                        <li>Printed photo strips 2x6, Polaroid style design or 1 Photo Standee (limited
                                            to 4 shots), 3 ref magnets (single shot)</li>
                                        <li>Extra printed copies for godparents (optional)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Birthday Service Card -->
                    <div class="col-md-6 col-lg-4 mb-4" id="birthday-card">
                        <div class="service-card card h-100 position-relative">
                            <div class="card-body">
                                <div class="service-icon">
                                    <i class="fas fa-birthday-cake"></i>
                                </div>
                                <h5 class="card-title">Birthday</h5>
                                <p class="card-text">
                                    Celebrate in style with lively and creative coverage.
                                </p>
                                <ul class="pricing-list">
                                    <li><strong>3 Hours:</strong> ₱4,000<span>(50% down: ₱2,000)</span></li>
                                    <li><strong>4 Hours:</strong> ₱4,500 <span>(50% down: ₱2,750)</span></li>
                                </ul>
                                <button type="button" class="btn btn-info info-btn position-absolute"
                                    style="bottom: 16px; right: 16px;" data-service="birthday">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <div class="service-tooltip" id="tooltip-birthday" style="display:none;">
                                    <strong>Birthday Inclusions:</strong>
                                    <ul>
                                        <li>Unlimited photo sessions</li>
                                        <li>Personalized birthday-themed layout</li>
                                        <li>Fun birthday props</li>
                                        <li>Printed photo strips 2x6, Polaroid style design or 1 Photo Standee (limited
                                            to 4 shots), 3 ref magnets (single shot)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Event Service Card -->
                    <div class="col-md-6 col-lg-4 mb-4" id="company-card">
                        <div class="service-card card h-100 position-relative">
                            <div class="card-body">
                                <div class="service-icon">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <h5 class="card-title">Company Event</h5>
                                <p class="card-text">
                                    Professional coverage for your corporate gatherings.
                                </p>
                                <ul class="pricing-list">
                                    <li><strong>3 Hours:</strong> ₱7,000 <span>(50% down: ₱3,500)</span></li>
                                    <li><strong>4 Hours:</strong> ₱8,000 <span>(50% down: ₱4,000)</span></li>
                                </ul>
                                <button type="button" class="btn btn-info info-btn position-absolute"
                                    style="bottom: 16px; right: 16px;" data-service="company">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <div class="service-tooltip" id="tooltip-company" style="display:none;">
                                    <strong>Company Event Inclusions:</strong>
                                    <ul>
                                        <li>Unlimited sessions with company-branded photo layout</li>
                                        <li>Professional photo lighting setup</li>
                                        <li>Corporate backdrop or company logo integration</li>
                                        <li>Free digital copies (Google Drive)</li>
                                        <li>Props suitable for corporate fun</li>
                                        <li>On-site assistant/operator</li>
                                        <li>Option to add branding or sponsor logos (optional)</li>
                                        <li>Bonus: Data collection feature (email/photo opt-in) if needed</li>
                                        <li>Printed photo strips 2x6, Polaroid style design or 1 Photo Standee (limited
                                            to 4 shots)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reunion Service Card -->
                    <div class="col-md-6 col-lg-4 mb-4" id="reunion-card">
                        <div class="service-card card h-100 position-relative">
                            <div class="card-body">
                                <div class="service-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h5 class="card-title">Reunion</h5>
                                <p class="card-text">
                                    Relive old memories with a full event coverage.
                                </p>
                                <ul class="pricing-list">
                                    <li><strong>3 Hours:</strong> ₱5,000 <span>(50% down: ₱2,500)</span></li>
                                    <li><strong>4 Hours:</strong> ₱6,500 <span>(50% down: ₱3,250)</span></li>
                                </ul>
                                <button type="button" class="btn btn-info info-btn position-absolute"
                                    style="bottom: 16px; right: 16px;" data-service="reunion">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <div class="service-tooltip" id="tooltip-reunion" style="display:none;">
                                    <strong>Reunion Inclusions:</strong>
                                    <ul>
                                        <li>Unlimited family/group photos</li>
                                        <li>Customized layout (batch/family name)</li>
                                        <li>Props for all ages</li>
                                        <li>Classic or themed backdrop</li>
                                        <li>Free digital copy via Google Drive</li>
                                        <li>Printed photo strips 2x6, Polaroid style design or 1 Photo Standee (limited
                                            to 4 shots), 3 ref magnets (single shot)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Wedding Service Card -->
                    <div class="col-md-6 col-lg-4 mb-4" id="wedding-card">
                        <div class="service-card card h-100 position-relative">
                            <div class="card-body">
                                <div class="service-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <h5 class="card-title">Wedding</h5>
                                <p class="card-text">
                                    Timeless coverage of your special day with elegance.
                                </p>
                                <ul class="pricing-list">
                                    <li><strong>3 Hours:</strong> ₱7,500 <span>(50% down: ₱3,750)</span></li>
                                    <li><strong>4 Hours:</strong> ₱11,000 <span>(50% down: ₱5,500)</span></li>
                                </ul>
                                <button type="button" class="btn btn-info info-btn position-absolute"
                                    style="bottom: 16px; right: 16px;" data-service="wedding">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <div class="service-tooltip" id="tooltip-wedding" style="display:none;">
                                    <strong>Wedding Inclusions:</strong>
                                    <ul>
                                        <li>Unlimited elegant photo sessions</li>
                                        <li>Romantic layout with couple’s names</li>
                                        <li>Floral or white backdrop options</li>
                                        <li>Premium wedding props (Mr & Mrs, love signs, etc.)</li>
                                        <li>Printed photo strips 2x6 or Polaroid style design</li>
                                        <li>Free digital copies via Google Drive</li>
                                        <li>Extended time for full guest coverage</li>
                                        <li>Optional live slideshow monitor display (if available)</li>
                                        <li>Printed photo strips 2x6, Polaroid style design or 1 Photo Standee (limited
                                            to 4 shots)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Portfolio / Past Photo Works Section -->
                    <section id="portfolio" class="py-5">
                        <h2 class="section-title text-center mb-5">Sample Photo Templates</h2>
                        <div class="container">
                            <div class="row">
                                <!-- Portfolio Item 1 -->
                                <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                                    <div class="portfolio-item" data-title="Wedding Event"
                                        data-description="A beautiful wedding ceremony captured with elegance."
                                        data-date="May 2019">
                                        <img src="/NEW-PM-JI-RESERVIFY/assets/portfolio/work1.jpg" alt="Wedding Event"
                                            class="img-fluid portfolio-img">
                                    </div>
                                </div>
                                <!-- Portfolio Item 2 -->
                                <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                                    <div class="portfolio-item" data-title="Corporate Event"
                                        data-description="Professional coverage of a corporate gathering at Quezon City Sports Club."
                                        data-date="March 2019">
                                        <img src="/NEW-PM-JI-RESERVIFY/assets/portfolio/work2.jpg" alt="Corporate Event"
                                            class="img-fluid portfolio-img">
                                    </div>
                                </div>
                                <!-- Portfolio Item 3 -->
                                <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                                    <div class="portfolio-item" data-title="Birthday Party"
                                        data-description="A lively birthday celebration with creative shots."
                                        data-date="March 2019">
                                        <img src="/NEW-PM-JI-RESERVIFY/assets/portfolio/work3.jpg" alt="Birthday Party"
                                            class="img-fluid portfolio-img">
                                    </div>
                                </div>
                                <!-- Portfolio Item 4 -->
                                <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                                    <div class="portfolio-item" data-title="Reunion Event"
                                        data-description="Reliving memories with a fun-filled reunion."
                                        data-date="May 2019">
                                        <img src="/NEW-PM-JI-RESERVIFY/assets/portfolio/work4.jpg" alt="Reunion Event"
                                            class="img-fluid portfolio-img">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Modal for Portfolio Items -->
                    <div class="modal fade" id="portfolioModal" tabindex="-1" role="dialog"
                        aria-labelledby="portfolioModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="portfolioModalLabel"></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body text-center">
                                    <img id="portfolioModalImage" src="" alt="Portfolio Image" class="img-fluid mb-3">
                                    <p id="portfolioModalDescription"></p>
                                    <p><strong>Date:</strong> <span id="portfolioModalDate"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Carousel Script -->
    <script>
        $(document).ready(function () {
            let currentIndex = 0;
            const items = $('.carousel-item');
            const itemAmt = items.length;
            const intervalTime = 8000; // 8 seconds

            function cycleItems() {
                items.removeClass('active');
                items.eq(currentIndex).addClass('active');
            }

            function nextItem() {
                currentIndex = (currentIndex + 1) % itemAmt;
                cycleItems();
            }

            function prevItem() {
                currentIndex = (currentIndex - 1 + itemAmt) % itemAmt;
                cycleItems();
            }

            // Auto Cycling
            let autoSlide = setInterval(nextItem, intervalTime);

            $('.carousel-control.next').click(function (e) {
                e.preventDefault();
                clearInterval(autoSlide);
                nextItem();
                autoSlide = setInterval(nextItem, intervalTime);
            });

            $('.carousel-control.prev').click(function (e) {
                e.preventDefault();
                clearInterval(autoSlide);
                prevItem();
                autoSlide = setInterval(nextItem, intervalTime);
            });
        });
    </script>
    <!-- End of Carousel Script -->

    <!-- Past Works Modal Script -->
    <script>
        $(document).ready(function () {
            // when a portfolio item is clicked
            $('.portfolio-item').on('click', function () {
                // get data attributes from the clicked portfolio item
                const title = $(this).data('title');
                const description = $(this).data('description');
                const date = $(this).data('date');
                const imageSrc = $(this).find('img').attr('src'); // Get the image source

                // update the modal content
                $('#portfolioModalLabel').text(title);
                $('#portfolioModalDescription').text(description);
                $('#portfolioModalDate').text(date);
                $('#portfolioModalImage').attr('src', imageSrc);

                // show the modal
                $('#portfolioModal').modal('show');
            });
        });
    </script>
    <!-- End of Past Works Modal Script -->

    <!-- Tooltip Script -->
    <script>
        // Add this script at the end of your body or in a JS file
        document.querySelectorAll('.info-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                // Hide all tooltips first
                document.querySelectorAll('.service-tooltip').forEach(function (tip) {
                    tip.style.display = 'none';
                });
                // Show the relevant tooltip
                const service = btn.getAttribute('data-service');
                const tooltip = document.getElementById('tooltip-' + service);
                if (tooltip) {
                    tooltip.style.display = 'block';
                }
            });
        });

        // Hide tooltip when clicking outside
        document.addEventListener('click', function () {
            document.querySelectorAll('.service-tooltip').forEach(function (tip) {
                tip.style.display = 'none';
            });
        });
    </script>
    <!-- End of Tooltip Script -->

    <!-- Loading Animation Script -->
    <script>
        NProgress.start();

        window.addEventListener('load', function () {
            NProgress.done();
        });
    </script>
    <!-- End of Loading Animation Script -->


    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/footer.html'; ?>
</body>

</html>