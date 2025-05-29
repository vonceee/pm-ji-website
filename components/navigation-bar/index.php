<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PM&JI Reservify - Polished Navigation</title>

    <!-- External Dependencies -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" rel="stylesheet">

    <style>
        /* CSS Variables */
        :root {
            --primary-bg: #2C2C2C;
            --secondary-bg: #94C8F8;
            --card-bg: rgba(30, 30, 30, 0.8);
            --primary-color: #000;
            --secondary-color: #fff;
            --accent-color: #94C8F8;
            --text-muted: #b0bec5;
            --gradient-primary: linear-gradient(135deg, #94C8F8 0%, #E0F7FA 100%);
            --gradient-accent: linear-gradient(135deg, #94C8F8 0%, #81C4E8 100%);
            --shadow-glow: 0 8px 32px rgba(148, 200, 248, 0.15);
            --shadow-hover: 0 20px 40px rgba(148, 200, 248, 0.25);
            --transition-speed: 0.3s;
            --border-radius: 8px;
            --header-height: 140px;
        }

        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--primary-color);
            padding-top: var(--header-height);
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Header Container */
        .header-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(148, 200, 248, 0.2);
            box-shadow: var(--shadow-glow);
        }

        /* Top Header Styles */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 30px;
            background: var(--gradient-primary);
            color: var(--secondary-color);
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .top-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                left: -100%;
            }

            100% {
                left: 100%;
            }
        }

        .top-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 2;
        }

        .company-logo {
            height: 35px;
            width: auto;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            transition: transform var(--transition-speed) ease;
        }

        .company-logo:hover {
            transform: scale(1.05);
        }

        .company-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary-color);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .top-header-right {
            display: flex;
            align-items: center;
            gap: 20px;
            z-index: 2;
        }

        /* Login/Register Button */
        .login-register {
            background: rgba(255, 255, 255, 0.1);
            color: var(--secondary-color);
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: all var(--transition-speed) ease;
            backdrop-filter: blur(10px);
        }

        .login-register:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: var(--secondary-color);
            text-decoration: none;
        }

        /* Profile Section */
        .profile-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .bookings-link,
        .profile-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: var(--secondary-color);
            font-size: 1.1rem;
            transition: all var(--transition-speed) ease;
            text-decoration: none;
            backdrop-filter: blur(10px);
        }

        .bookings-link:hover,
        .profile-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: var(--secondary-color);
        }

        /* Main Navigation */
        .main-nav {
            background: rgba(255, 255, 255, 0.98);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-social {
            display: flex;
            gap: 15px;
        }

        .social-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            background: var(--gradient-accent);
            border-radius: 50%;
            color: var(--secondary-color);
            font-size: 0.9rem;
            text-decoration: none;
            transition: all var(--transition-speed) ease;
            box-shadow: 0 2px 8px rgba(148, 200, 248, 0.3);
        }

        .social-icon:hover {
            transform: translateY(-3px) scale(1.1);
            box-shadow: var(--shadow-hover);
            color: var(--secondary-color);
        }

        /* Navigation Menu */
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            padding: 10px 0;
            transition: all var(--transition-speed) ease;
            position: relative;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient-accent);
            transition: width var(--transition-speed) ease;
        }

        .nav-link:hover {
            color: var(--secondary-bg);
            text-decoration: none;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Dropdown Styles */
        .dropdown {
            position: relative;
        }

        .dropdown-toggle::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-left: 5px;
            transition: transform var(--transition-speed) ease;
        }

        .dropdown:hover .dropdown-toggle::after {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            min-width: 200px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-hover);
            border: 1px solid rgba(148, 200, 248, 0.2);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all var(--transition-speed) ease;
            z-index: 1000;
            padding: 10px 0;
            margin-top: 10px;
        }

        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: block;
            padding: 12px 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 400;
            transition: all var(--transition-speed) ease;
            border-radius: 4px;
            margin: 2px 8px;
        }

        .dropdown-item:hover {
            background: var(--gradient-accent);
            color: var(--secondary-color);
            transform: translateX(5px);
            text-decoration: none;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            gap: 4px;
            cursor: pointer;
            padding: 10px;
        }

        .mobile-menu-toggle span {
            width: 25px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 2px;
            transition: all var(--transition-speed) ease;
        }

        .mobile-menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }

        .mobile-menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .mobile-menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }

        /* AI Chat Button */
        .ai-chat-button {
            position: fixed;
            bottom: 25px;
            right: 25px;
            width: 60px;
            height: 60px;
            background: var(--gradient-accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary-color);
            font-size: 1.3rem;
            text-decoration: none;
            box-shadow: var(--shadow-glow);
            transition: all var(--transition-speed) ease;
            z-index: 999;
            animation: float-chat 3s ease-in-out infinite;
        }

        @keyframes float-chat {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .ai-chat-button:hover {
            transform: scale(1.1) translateY(-5px);
            box-shadow: var(--shadow-hover);
            color: var(--secondary-color);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            :root {
                --header-height: 120px;
            }

            .top-header {
                padding: 10px 20px;
            }

            .main-nav {
                padding: 12px 20px;
            }

            .nav-menu {
                gap: 20px;
            }

            .company-name {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 768px) {
            :root {
                --header-height: 80px;
            }

            .top-header {
                padding: 8px 15px;
            }

            .main-nav {
                padding: 10px 15px;
                position: relative;
            }

            .mobile-menu-toggle {
                display: flex;
            }

            .nav-menu {
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(20px);
                flex-direction: column;
                padding: 20px;
                border-radius: 0 0 var(--border-radius) var(--border-radius);
                box-shadow: var(--shadow-glow);
                opacity: 0;
                visibility: hidden;
                transform: translateY(-20px);
                transition: all var(--transition-speed) ease;
            }

            .nav-menu.active {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }

            .nav-item {
                margin: 5px 0;
            }

            .dropdown-menu {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                background: rgba(148, 200, 248, 0.1);
                margin: 10px 0;
                box-shadow: none;
            }

            .company-name {
                font-size: 1.1rem;
            }

            .nav-social {
                gap: 10px;
            }

            .social-icon {
                width: 30px;
                height: 30px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .top-header-right {
                gap: 10px;
            }

            .company-logo {
                height: 28px;
            }

            .bookings-link,
            .profile-link {
                width: 35px;
                height: 35px;
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Header Wrapper -->
    <div class="header-wrapper">
        <!-- Top Header -->
        <div class="top-header">
            <div class="top-header-left">
                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiNmZmYiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTkgMTJMMTEgMTRMMTUgMTBNMjEgMTJDMjEgMTYuOTcwNiAxNi45NzA2IDIxIDEyIDIxQzcuMDI5NDQgMjEgMyAxNi45NzA2IDMgMTJDMyA3LjAyOTQ0IDcuMDI5NDQgMyAxMiAzQzE2Ljk3MDYgMyAyMSA3LjAyOTQ0IDIxIDEyWiIgc3Ryb2tlPSIjOTRDOEY4IiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8L3N2Zz4KPC9zdmc+"
                    alt="PM&JI Reservify" class="company-logo" />
                <span class="company-name">PM&JI Reservify</span>
            </div>
            <div class="top-header-right">
                <!-- Logged In State (Demo) -->
                <div class="profile-section" id="loggedInSection" style="display: flex;">
                    <a href="#" class="bookings-link" title="My Bookings">
                        <i class="fas fa-calendar-check"></i>
                    </a>
                    <div class="dropdown">
                        <a href="#" class="profile-link" title="My Profile">
                            <i class="fas fa-user"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">Profile</a>
                            <a class="dropdown-item" href="#">Inbox</a>
                            <a class="dropdown-item" href="#">Preferences</a>
                            <div style="height: 1px; background: rgba(148, 200, 248, 0.2); margin: 8px 16px;"></div>
                            <a class="dropdown-item" href="#" onclick="toggleLoginState()">Logout</a>
                        </div>
                    </div>
                </div>
                <!-- Logged Out State -->
                <a href="#" class="login-register" id="loginButton" style="display: none;"
                    onclick="toggleLoginState()">Login</a>
            </div>
        </div>

        <!-- Main Navigation -->
        <nav class="main-nav">
            <!-- Social Icons -->
            <div class="nav-social">
                <a href="https://www.facebook.com/pmandjipictures" target="_blank" class="social-icon" title="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="mailto:photoapp@example.com" class="social-icon" title="Email">
                    <i class="fas fa-envelope"></i>
                </a>
            </div>

            <!-- Mobile Menu Toggle -->
            <div class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <!-- Navigation Menu -->
            <ul class="nav-menu" id="navMenu">
                <li class="nav-item">
                    <a class="nav-link" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">About</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#">Services</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#">Baptism</a>
                        <a class="dropdown-item" href="#">Birthday</a>
                        <a class="dropdown-item" href="#">Company Event</a>
                        <a class="dropdown-item" href="#">Reunion</a>
                        <a class="dropdown-item" href="#">Wedding</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Contact</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- AI Chat Button -->
    <a href="#" class="ai-chat-button" title="Chat with us">
        <i class="fas fa-comments"></i>
    </a>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const toggle = document.querySelector('.mobile-menu-toggle');
            const menu = document.getElementById('navMenu');

            toggle.classList.toggle('active');
            menu.classList.toggle('active');
        }

        // Login state toggle (demo)
        function toggleLoginState() {
            const loggedInSection = document.getElementById('loggedInSection');
            const loginButton = document.getElementById('loginButton');

            if (loggedInSection.style.display === 'none') {
                loggedInSection.style.display = 'flex';
                loginButton.style.display = 'none';
            } else {
                loggedInSection.style.display = 'none';
                loginButton.style.display = 'block';
            }
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function (event) {
            const navMenu = document.getElementById('navMenu');
            const toggle = document.querySelector('.mobile-menu-toggle');

            if (!navMenu.contains(event.target) && !toggle.contains(event.target)) {
                navMenu.classList.remove('active');
                toggle.classList.remove('active');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll effect to header
        window.addEventListener('scroll', function () {
            const header = document.querySelector('.header-wrapper');
            if (window.scrollY > 50) {
                header.style.background = 'rgba(255, 255, 255, 0.9)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
            }
        });
    </script>
</body>

</html>