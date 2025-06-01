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
        <div class="container py-5">
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
                                    <button class="nav-link active" id="bookings-tab" data-bs-toggle="tab"
                                        data-bs-target="#bookings" type="button" role="tab">My Bookings</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="settings-tab" data-bs-toggle="tab"
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
        // on document ready (or bottom of <body>)
        function openModal(detailsId) {
            // populate
            populateModal(document.querySelector(detailsId));
            // show
            const modal = document.getElementById('bookingDetailsModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('bookingDetailsModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        function populateModal(details) {
            // read data-attrs
            const { referenceId, eventType, eventDate, startTime, endTime,
                location, amountPaid, balance, paymentMethod,
                paymentStatus, status, duration } = details.dataset;

            document.getElementById('modalReferenceNumber').textContent = `Reference: ${referenceId}`;
            document.getElementById('modalEventType').textContent = eventType;
            document.getElementById('modalEventDate').textContent = formatDate(eventDate);
            document.getElementById('modalEventTime').textContent = `${formatTime(startTime)} – ${formatTime(endTime)}`;
            document.getElementById('modalDuration').textContent = duration;
            document.getElementById('modalLocation').textContent = location;
            document.getElementById('modalAmountPaid').textContent = `₱${parseFloat(amountPaid).toFixed(2)}`;
            document.getElementById('modalBalance').textContent = `₱${parseFloat(balance).toFixed(2)}`;
            document.getElementById('modalPaymentMethod').textContent = `${paymentMethod} / ${details.dataset.paymentType}`;

            // status badges
            document.getElementById('modalStatusBadge').innerHTML = `
    <div class="status-badge ${status}">
      <div class="status-dot"></div>
      ${status.charAt(0).toUpperCase() + status.slice(1)}
    </div>
  `;
            document.getElementById('modalPaymentStatus').innerHTML = `
    <span class="payment-status ${paymentStatus}">
      ${paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1)}
    </span>
  `;
        }

        // wire up buttons
        document.querySelectorAll('.toggle-details').forEach(btn => {
            btn.addEventListener('click', () => {
                openModal(btn.getAttribute('data-target'));
            });
        });

        // click‐outside & Escape to close
        document.getElementById('bookingDetailsModal').addEventListener('click', e => {
            if (e.target === e.currentTarget) closeModal();
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModal();
        });

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        function formatTime(timeStr) {
            const [hours, minutes] = timeStr.split(':');
            const date = new Date();
            date.setHours(parseInt(hours), parseInt(minutes));
            return date.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }

        // close modal when clicking outside
        document.getElementById('bookingDetailsModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // close modal with Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>

    <!-- add moment.js for time formatting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
</body>


</html>