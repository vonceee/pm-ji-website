<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit;
}
$admin_username = $_SESSION['admin_username'];

// header, navbar
require_once $_SERVER['DOCUMENT_ROOT']
    . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/components/admin_header.php';
require_once $_SERVER['DOCUMENT_ROOT']
    . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/components/admin_navbar.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- End Bootstrap CSS -->

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/admin_dashboard.css">
    <!-- End Custom CSS -->

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <!-- End Font Awesome Icons -->
</head>

<body>
    <main class="content-container">
        <div class="main-content-wrapper">
            <div class="main-content">
                <?php

                $view = $_GET['view'] ?? 'dashboard';

                switch ($view) {
                    case 'bookings':
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/index.php';
                        break;
                    case 'payments':
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/payments/index.php';
                        break;
                    default:
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/dashboard/index.php';
                        break;
                }

                ?>
            </div>
        </div>
    </main>
</body>

</html>