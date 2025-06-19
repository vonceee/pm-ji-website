<?php
// check for session variable $_SESSION['user_email']
$isLoggedIn = !empty($_SESSION['user_email']);

$homeLink = $isLoggedIn
    ? "/NEW-PM-JI-RESERVIFY/pages/customer/home.php"
    : "/NEW-PM-JI-RESERVIFY/public/index.php";
?>

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/components/navigation-bar/navigation-bar.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<!-- Single Navigation Header -->
<header>
    <nav class="unified-navbar">
        <!-- Left Section: Logo + Company Name -->
        <div class="navbar-left">
            <img src="/NEW-PM-JI-RESERVIFY/assets/logo/PM&JI-logo.png" alt="PM&JI Reservify" class="company-logo" />
            <span class="company-name">PM&JI Reservify</span>
        </div>

        <!-- Center Section: Social Icons -->
        <div class="navbar-center">
            <a href="https://www.facebook.com/pmandjipictures" target="_blank" rel="noopener noreferrer"
                class="social-icon" title="Follow us on Facebook">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="mailto:reservifypm&ji@gmail.com" class="social-icon" title="Send us an email">
                <i class="fas fa-envelope"></i>
            </a>
        </div>

        <!-- Right Section: Navigation + User Actions -->
        <div class="navbar-right">
            <!-- Main Navigation -->
            <ul class="main-navigation">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $homeLink; ?>">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Services
                    </a>
                    <div class="dropdown-menu" aria-labelledby="servicesDropdown">
                        <a class="dropdown-item service-link" href="#baptism-card">
                            <i class="fas fa-baby"></i> Baptism
                        </a>
                        <a class="dropdown-item service-link" href="#birthday-card">
                            <i class="fas fa-birthday-cake"></i> Birthday
                        </a>
                        <a class="dropdown-item service-link" href="#company-card">
                            <i class="fas fa-building"></i> Company Event
                        </a>
                        <a class="dropdown-item service-link" href="#reunion-card">
                            <i class="fas fa-users"></i> Reunion
                        </a>
                        <a class="dropdown-item service-link" href="#wedding-card">
                            <i class="fas fa-heart"></i> Wedding
                        </a>
                    </div>
                </li>
                                <li class="nav-item">
                    <a class="nav-link" href="#about-section">About</a>
                </li>
            </ul>

            <!-- User Actions -->
            <div class="user-actions">
                <?php if ($isLoggedIn): ?>
                    <!-- My Bookings Link -->
                    <a href="/NEW-PM-JI-RESERVIFY/pages/customer/views/dashboard.php" class="bookings-link"
                        title="My Bookings">
                        <i class="fas fa-calendar-check"></i>
                    </a>

                    <!-- Profile Dropdown -->
                    <div class="dropdown profile-dropdown">
                        <a href="#" class="profile-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            title="My Profile">
                            <i class="fas fa-user"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="/NEW-PM-JI-RESERVIFY/pages/customer/profile/profile.php">
                                <i class="fas fa-user-circle"></i> Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/NEW-PM-JI-RESERVIFY/pages/customer/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="#" class="login-register-btn" data-toggle="modal" data-target="#loginModal"
                        title="Login to your account">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>
<!-- End Header -->

<!-- Login Modal -->
<div class="modal login-modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel"
    aria-hidden="true">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/login-modal/index.php'; ?>
</div>

<!-- Sign Up Modal -->
<div class="modal login-modal fade" id="signupModal" tabindex="-1" role="dialog" aria-labelledby="signupModalLabel">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/signup-modal/index.php'; ?>
</div>

<!-- Login Modal Script -->
<script src="/NEW-PM-JI-RESERVIFY/components/login-modal/login-modal.js"></script>
<!-- Sign Up Modal Script -->
<script src="/NEW-PM-JI-RESERVIFY/components/signup-modal/signup-modal.js"></script>
<!-- Navigation Bar Script -->
<script src="/NEW-PM-JI-RESERVIFY/components/navigation-bar/navigation-bar.js"></script>