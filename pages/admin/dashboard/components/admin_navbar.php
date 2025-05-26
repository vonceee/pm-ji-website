
<?php
// Get current view for active state
$current_view = $_GET['view'] ?? 'dashboard';
?>

<head>
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/components/admin_navbar.css">
</head>

<nav class="admin-navbar">
    <div class="navbar-brand">
        <i class="fas fa-calendar-check"></i>
        <span>Reservify Admin</span>
    </div>

    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link <?= $current_view === 'dashboard' ? 'active' : '' ?>" href="?view=dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?= $current_view === 'bookings' ? 'active' : '' ?>" href="?view=bookings">
                <i class="fas fa-calendar-alt"></i>
                <span>Bookings</span>
            </a>
        </li>

        <li class="nav-item">
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
        </li>

        <li class="nav-item">
            <a class="nav-link <?= $current_view === 'reports' ? 'active' : '' ?>" href="?view=reports">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="/NEW-PM-JI-RESERVIFY/pages/admin/logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>

    <div class="navbar-user">
        <i class="fas fa-user-circle"></i>
        <span><?= htmlspecialchars($admin_username) ?></span>
    </div>
</nav>

<style>
    /* Notification Badge Styles */
    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        font-size: 0.7rem;
        font-weight: bold;
        padding: 2px 6px;
        min-width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .nav-item {
        position: relative;
    }

    .nav-link {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Animation for the badge */
    .notification-badge {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }

        100% {
            transform: scale(1);
        }
    }
</style>