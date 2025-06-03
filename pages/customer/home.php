<?php
session_start();

if (!isset($_SESSION['user_email'])) {
  header("Location: /NEW-PM-JI-RESERVIFY/public/index.php");
  exit();
}

$firstName = htmlspecialchars($_SESSION['first_name'] ?? 'User');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PM&JI Reservify</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/home.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
</head>

<body>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/navigation-bar/index.php'; ?>

  <!-- Hero Section -->
  <section class="customer-hero-section">
    <!-- carousel background -->
    <div class="customer-carousel">
      <div class="customer-carousel-inner">
        <div class="customer-carousel-item active">
          <img src="/NEW-PM-JI-RESERVIFY/assets/carousel/sample4.jpg" alt="Image 1">
        </div>
      </div>
    </div>

    <!-- Hero Content Overlay -->
    <div class="customer-hero-content">
      <h1 class="customer-hero-title">Welcome, <?php echo $firstName; ?>!</h1>
      <p class="customer-hero-tagline">
        Welcome to PM&JI Reservify—booking photo booth services and capturing memories.
        Check out our services and reserve your spot today!
      </p>
      <a href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/index.php" class="customer-hero-button">Book now!</a>
    </div>
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

  <!-- Footer Section -->
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/footer.html'; ?>

  <!-- Loading Animation Script -->
  <script>
    NProgress.start();

    window.addEventListener('load', function () {
      NProgress.done();
    });
  </script>
  <!-- End of Loading Animation Script -->

</body>

</html>