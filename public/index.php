<?php
// public/index.php

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

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/public/css/index.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/public/css/loading.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/components/top-header.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/components/footer.css">

    <!-- Icons Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />

    <!-- Loading CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />

    <!-- JQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Bootrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Loading JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>

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
            <img src="/NEW-PM-JI-RESERVIFY/assets/logo/PM&JI-logo.png" alt="PM&JI Reservify Logo" class="hero-logo">
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

    <!-- Horizontal Testimonials Column (right side) -->
    <div class="col-md-7">
        <h5>What Our Clients Say!</h5>
        <div class="testimonials-container">
            <!-- Testimonial Card 1 -->
            <div class="testimonial-card">
                <div class="testimonial-header">
                    <img src="/NEW-PM-JI-RESERVIFY/assets/profile/profile01.jpg" alt="Profile 1" class="profile-pic">
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
                    <img src="/NEW-PM-JI-RESERVIFY/assets/profile/profile02.jpg" alt="Profile 2" class="profile-pic">
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
                    <img src="/NEW-PM-JI-RESERVIFY/assets/profile/profile03.jpg" alt="Profile 3" class="profile-pic">
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
                    <img src="/NEW-PM-JI-RESERVIFY/assets/profile/profile04.jpg" alt="Profile 4" class="profile-pic">
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
                    <img src="/NEW-PM-JI-RESERVIFY/assets/profile/profile05.jpg" alt="Profile 5" class="profile-pic">
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

    <!-- Services Cards Section -->
    <section id="service-cards-section">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/public/components/service-cards/index.php'; ?>
    </section>

    <!-- Portfolio / Past Photo Works Section -->
    <section id="portfolio" class="py-5">
        <h2 class="section-title text-center mb-5">Sample Photo Templates</h2>
        <div class="container">
            <div class="row">
                <!-- Portfolio Item 1 -->
                <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="portfolio-item" data-title="Wedding Event"
                        data-description="A beautiful wedding ceremony captured with elegance." data-date="May 2019">
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
                        data-description="A lively birthday celebration with creative shots." data-date="March 2019">
                        <img src="/NEW-PM-JI-RESERVIFY/assets/portfolio/work3.jpg" alt="Birthday Party"
                            class="img-fluid portfolio-img">
                    </div>
                </div>
                <!-- Portfolio Item 4 -->
                <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="portfolio-item" data-title="Reunion Event"
                        data-description="Reliving memories with a fun-filled reunion." data-date="May 2019">
                        <img src="/NEW-PM-JI-RESERVIFY/assets/portfolio/work4.jpg" alt="Reunion Event"
                            class="img-fluid portfolio-img">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal for Portfolio Items -->
    <div class="modal fade" id="portfolioModal" tabindex="-1" role="dialog" aria-labelledby="portfolioModalLabel"
        aria-hidden="true">
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

    <!-- Service Cards Section Tooltip Script -->
    <script src="/NEW-PM-JI-RESERVIFY/public/service-cards/index.js"></script>

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