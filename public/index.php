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

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/components/login-modal/login-modal.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/public/css/index.css">
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
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/navigation-bar/index.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/public/components/carousel/index.php'; ?>
    </section>

    <!-- Services Cards Section -->
    <section id="service-cards-section">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/public/components/service-cards/index.php'; ?>
    </section>

    <!-- Portfolio / Past Photo Works Section -->
    <section id="portfolio-section">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/public/components/portfolio/index.php'; ?>
    </section>

    <!-- About Section -->
    <section id="about-section">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/public/components/about/index.php'; ?>
    </section>

    <!-- Loading Animation Script -->
    <script>
        NProgress.start();

        window.addEventListener('load', function () {
            NProgress.done();
        });
    </script>

    <!-- Footer Section -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/footer.html'; ?>
</body>

</html>