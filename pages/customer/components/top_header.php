<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
</head>

<!-- Header -->
<header>
    <div class="top-header">
        <div class="top-header-left">
            <img src="/NEW-PM-JI-RESERVIFY/assets/logo/PM&JI-logo.png" alt="PM&JI Reservify" class="company-logo" />
            <span class="company-name">PM&JI Reservify</span>
        </div>
        <div class="top-header-right">
            <!-- My Bookings Link -->
            <a href="/NEW-PM-JI-RESERVIFY/pages/customer/customerpanel.php" class="bookings-link" title="My Bookings">My
                Bookings</a>
            <!-- Profile Dropdown -->
            <div class="dropdown profile-dropdown">
                <a href="#" class="profile-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    title="My Profile">
                    <i class="fas fa-user"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="/NEW-PM-JI-RESERVIFY/pages/customer/profile/profile.php">Profile</a>
                    <a class="dropdown-item" href="inbox.php">Inbox</a>
                    <a class="dropdown-item" href="preference.php">Preference</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="/NEW-PM-JI-RESERVIFY/pages/customer/logout.php">Logout</a>
                </div>
            </div>
        </div>

    </div>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <!-- Social Icons on Left -->
            <div class="navbar-social">
                <a href="https://www.facebook.com/pmandjipictures" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-facebook-f social-icon"></i>
                </a>
                <a href="mailto:photoapp@example.com">
                    <i class="fas fa-envelope social-icon"></i>
                </a>
            </div>

            <!-- Navigation Links on Right -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/NEW-PM-JI-RESERVIFY/pages/customer/home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/NEW-PM-JI-RESERVIFY/about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="/NEW-PM-JI-RESERVIFY/pages/customer/home.php#services-section">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="/NEW-PM-JI-RESERVIFY/pages/customer/home.php#footer-section">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<!-- End Header -->

<!-- Login Modal Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var bookingsLink = document.querySelector('.bookings-link');
        if (bookingsLink) {
            bookingsLink.addEventListener('click', function () {
                NProgress.start();
            });
        }

        window.addEventListener('load', function () {
            NProgress.done();
        });
    });
</script>
<!-- End Login Modal Script -->