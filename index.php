<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PM&JI Reservify</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/index.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/components/top-header.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/components/footer.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/top-header.php'; ?>

    <section class="hero-section">
        <!-- Carousel Background -->
        <div class="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="/NEW-PM-JI-RESERVIFY/assets/carousel/sample1.jpg" alt="Image 1">
                </div>
                <div class="carousel-item">
                    <img src="/NEW-PM-JI-RESERVIFY/assets/carousel/sample2.jpg" alt="Image 2">
                </div>
                <div class="carousel-item">
                    <img src="/NEW-PM-JI-RESERVIFY/assets/carousel/sample3.jpg" alt="Image 3">
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
    </section>

    <!-- Services Section -->
    <section id="services-section" class="py-5">
        <div>
            <h2 class="section-title text-center mb-5">
                <i class="fas fa-cogs"></i> Our Services
            </h2>
        </div>

        <!-- Modified Service Inclusions & Horizontal Testimonials Row -->
        <div class="container mb-4">
            <div class="row">
                <!-- Service Inclusions Column (left side) -->
                <div class="col-md-7">
                    <div class="service-inclusions">
                        <h5><i class="fas fa-concierge-bell"></i> Service Inclusions:</h5>
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
                <div class="col-md-5">
                    <h5>What Our Clients Say</h5>
                    <div class="testimonials-container">
                        <!-- Testimonial Card 1 -->
                        <div class="testimonial-card">
                            <div class="testimonial-header">
                                <img src="/NEW-PM-JI-RESERVIFY/assets/profile/profile1.png" alt="Profile 1"
                                    class="profile-pic">
                                <div class="user-info">
                                    <span class="username">Jamie D.</span>
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
                                <img src="/NEW-PM-JI-RESERVIFY/assets/profile/profile2.png" alt="Profile 2"
                                    class="profile-pic">
                                <div class="user-info">
                                    <span class="username">Alex T.</span>
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
                        <!-- Add additional testimonial cards as needed -->
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">

                <!-- Baptism Service Card -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="service-card card h-100">
                        <div class="card-body">
                            <div class="service-icon">
                                <i class="fas fa-church"></i>
                            </div>
                            <h5 class="card-title">Baptism</h5>
                            <p class="card-text">
                                Capture precious moment with professional coverage.
                            </p>
                            <ul class="pricing-list">
                                <li><strong>3 Hours:</strong> ₱4,500 <span>(50% down: ₱2,250)</span></li>
                                <li><strong>4 Hours:</strong> ₱4,600 <span>(50% down: ₱2,300)</span></li>
                            </ul>
                            <p class="inclusions">Includes unlimited enhanced shots delivered digitally via Google
                                Drive.</p>
                            <a href="#" class="btn btn-primary service-cta" data-toggle="modal"
                                data-target="#loginModal">Book Now</a>
                        </div>
                    </div>
                </div>

                <!-- Birthday Service Card -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="service-card card h-100">
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
                            <p class="inclusions">Includes unlimited enhanced shots delivered digitally via Google
                                Drive.</p>
                            <a href="#" class="btn btn-primary service-cta" data-toggle="modal"
                                data-target="#loginModal">Book Now</a>
                        </div>
                    </div>
                </div>

                <!-- Company Event Service Card -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="service-card card h-100">
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
                            <p class="inclusions">Includes unlimited enhanced shots delivered digitally via Google
                                Drive.</p>
                            <a href="#" class="btn btn-primary service-cta" data-toggle="modal"
                                data-target="#loginModal">Book Now</a>
                        </div>
                    </div>
                </div>

                <!-- Reunion Service Card -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="service-card card h-100">
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
                            <p class="inclusions">Includes unlimited enhanced shots delivered digitally via Google
                                Drive.</p>
                            <a href="#" class="btn btn-primary service-cta" data-toggle="modal"
                                data-target="#loginModal">Book Now</a>
                        </div>
                    </div>
                </div>

                <!-- Wedding Service Card -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="service-card card h-100">
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
                            <p class="inclusions">Includes unlimited enhanced shots delivered digitally via Google
                                Drive.</p>
                            <a href="#" class="btn btn-primary service-cta" data-toggle="modal"
                                data-target="#loginModal">Book Now</a>
                        </div>
                    </div>
                </div>

                <!-- Portfolio / Past Photo Works Section -->
                <section id="portfolio" class="py-5">
                    <h2 class="section-title text-center mb-5">Our Past Works</h2>
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
    </section>

    <a href="connect_with_us.php" class="message-link">
        <div class="message-icon">
            <i class="fa fa-message"></i>
        </div>
    </a>

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

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/footer.html'; ?>
</body>

</html>