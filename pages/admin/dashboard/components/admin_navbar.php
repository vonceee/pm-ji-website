<!-- admin_navbar.php -->

<head>
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/components/admin_navbar.css">
</head>

<nav class="admin-navbar">
    <ul class="navbar-menu">
        <li>
            <a href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/admin_dashboard.php" class="<?php
            // Highlight Dashboard if no 'view' param or view=dashboard
            $isDashboard = !isset($_GET['view']) || $_GET['view'] === 'dashboard';
            echo $isDashboard ? 'active' : '';
            ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/admin_dashboard.php?view=bookings"
                class="<?php echo (isset($_GET['view']) && $_GET['view'] === 'bookings') ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i> Bookings
            </a>
        </li>
        <li>
            <a href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments"
                class="<?php echo (isset($_GET['view']) && $_GET['view'] === 'payments') ? 'active' : ''; ?>">
                <i class="fas fa-credit-card"></i> Payments
            </a>
        </li>
        <li>
            <a href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/calendar"
                class="<?php echo (isset($_GET['view']) && $_GET['view'] === 'calendar') ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Calendar
            </a>
        </li>
                <li>
            <a href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/admin_dashboard.php?view=reports"
                class="<?php echo (isset($_GET['view']) && $_GET['view'] === 'reports') ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
        </li>
        <li class="navbar-help">
            <a href="admin_help.php"
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_help.php' ? 'active' : ''; ?>">
                <i class="fas fa-question-circle"></i> Help <span class="help-icon">?</span>
            </a>
        </li>
    </ul>
</nav>