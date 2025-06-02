<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: /NEW-PM-JI-RESERVIFY/public/index.php");
    exit;
}

// database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
use Config\Database;

// fetch the shared PDO instance
$pdo = Database::getConnection();

// get the logged-in user's email from session
$userEmail = $_SESSION['user_email'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css" />
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/views/dashboard.css" />
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/views/partials/cancellation-modal/cancellation-modal.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/views/partials/bookings-table/bookings-table.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/navigation-bar/index.php'; ?>

    <div class="animated-bg"></div>

    <div class="container-body">
        <div class="px-5 py-5">
            <div class="row g-3">

                <!-- Left: 4-column profile card -->
                <div class="col-md-3 mb-4">
                    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/views/partials/profile-sidebar/index.php'; ?>
                </div>

                <!-- Right: 8-column content card -->
                <div class="col-md-9">
                    <div class="card content-card shadow-sm">
                        <div class="card-body">
                            <!-- tabs -->
                            <ul class="nav nav-tabs" id="customerPanelTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="bookings-tab-nav-link active" id="bookings-tab" data-bs-toggle="tab"
                                        data-bs-target="#bookings" type="button" role="tab">My Bookings</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="bookings-tab-nav-link" id="settings-tab" data-bs-toggle="tab"
                                        data-bs-target="#settings" type="button" role="tab">Settings</button>
                                </li>
                            </ul>
                            <div class="tab-content mt-3" id="customerPanelTabsContent">
                                <!-- Bookings Table Tab -->
                                <div class="tab-pane fade show active" id="bookings" role="tabpanel">
                                    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/views/partials/bookings-table/index.php'; ?>
                                </div>
                                <!-- Edit Profile Tab -->
                                <div class="tab-pane fade" id="editprofile" role="tabpanel"
                                    aria-labelledby="editprofile-tab">
                                    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/partials/edit_profile.php'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        
    </script>

    <!-- add moment.js for time formatting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
</body>


</html>