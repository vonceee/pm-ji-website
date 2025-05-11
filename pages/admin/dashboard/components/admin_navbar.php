<head>
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/components/admin_navbar.css">
</head>

<!-- admin_sidebar.php -->
<nav class="admin-navbar">
    <ul class="navbar-menu">
        <li>
            <a href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/admin_dashboard.php"
               class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings"
               class="<?php echo strpos($_SERVER['PHP_SELF'], 'bookings') !== false ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i> Bookings
            </a>
        </li>
        <li>
            <a href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments"
               class="<?php echo strpos($_SERVER['PHP_SELF'], 'payments') !== false ? 'active' : ''; ?>">
                <i class="fas fa-credit-card"></i> Payments
            </a>
        </li>
        <li>
            <a href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/calendar"
               class="<?php echo strpos($_SERVER['PHP_SELF'], 'calendar') !== false ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Calendar
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
