<?php
// get current view for active state
$current_view = $_GET['view'] ?? 'dashboard';

// get user info (you can customize this based on your session management)
$user_name = $_SESSION['admin_name'] ?? 'Admin';
$user_initial = strtoupper(substr($user_name, 0, 1));
?>

<header class="admin-header">
    <!-- Left Section - Branding -->
    <div class="header-left">
        <div class="brand-section">
            <div class="company-logo"></div>
            <span class="company-name">PM&JI Reservify Admin</span>
        </div>

        <!-- Navigation Menu -->
        <nav class="header-nav">
            <div class="nav-item">
                <a class="nav-link <?= $current_view === 'dashboard' ? 'active' : '' ?>" href="?view=dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link <?= $current_view === 'bookings' ? 'active' : '' ?>" href="?view=bookings">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Bookings</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link <?= $current_view === 'payments' ? 'active' : '' ?>" href="?view=payments">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                    <?php
                    // Show notification badge for outstanding payments
                    try {
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
                        $pdo = Config\Database::getConnection();

                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_payments WHERE balance > 0");
                        $stmt->execute();
                        $outstandingCount = $stmt->fetchColumn();

                        if ($outstandingCount > 0): ?>
                            <span class="notification-badge"><?= $outstandingCount ?></span>
                        <?php endif;
                    } catch (Exception $e) {
                        // Silently handle any database errors
                    }
                    ?>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link <?= $current_view === 'calendar' ? 'active' : '' ?>" href="?view=calendar">
                    <i class="fas fa-calendar"></i>
                    <span>Calendar</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Right Section - User Actions -->
    <div class="header-right">
        <!-- Profile Dropdown -->
        <div class="profile-dropdown">
            <div class="profile-trigger">
                <div class="profile-avatar">
                    <?= $user_initial ?>
                </div>
            </div>

            <div class="profile-dropdown-menu">
                <a class="dropdown-item" href="/NEW-PM-JI-RESERVIFY/pages/admin/profile-page/index.php">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/NEW-PM-JI-RESERVIFY/pages/admin/process-admin-logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</header>