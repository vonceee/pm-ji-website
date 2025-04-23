<?php
session_start();

if (!isset($_SESSION['user_email'])) {
  header("Location: /NEW-PM-JI-RESERVIFY/index.php");
  exit();
}

$userEmail = $_SESSION['user_email'];

$host = "localhost";
$user = "root";
$password = "";
$database = "db_pmji";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// get the corresponding user id from tbl_users
$userIdQuery = "SELECT id FROM tbl_users WHERE email = ?";
$stmt = $conn->prepare($userIdQuery);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
  die("User not found.");
}
$stmt->bind_result($userId);
$stmt->fetch();
$stmt->close();

// retrieve bookings from tbl_bookings for the user
$query = "SELECT event_type, duration, reservation_date, time_slot, street_address, barangay, city, province, reference_number, reference_id, payment_method, payment_type, payment_screenshot, status, payment_status, created_at 
          FROM tbl_bookings 
          WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings - PM&JI Reservify</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/mybookings.css">
  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/components/top_header.css">
  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/components/footer.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>

<body>

  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/components/top_header.php'; ?>

  <!-- Booking List Section -->
  <section class="py-5">
    <div class="container mt-5 my-bookings-container">
      <h2 class="my-bookings-title text-center mb-4">My Bookings</h2>
      <?php if ($result->num_rows > 0): ?>
        <div class="horizontal-scroll-bookings">
          <?php while ($row = $result->fetch_assoc()): ?>
            <div id="booking-card-<?php echo htmlspecialchars($row['reference_id']); ?>"
              class="booking-card card flex-fill mx-2 <?php echo strtolower($row['status']) === 'cancelled_by_user' ? 'disabled' : ''; ?>">
              <div class="card-header">
                <?php echo htmlspecialchars($row['event_type']); ?>
              </div>
              <div class="card-body d-flex flex-column">
                <div class="booking-info mb-2">
                  <strong>Date &amp; Time:</strong>
                  <?php echo htmlspecialchars($row['reservation_date']); ?>,
                  <?php echo htmlspecialchars($row['time_slot']); ?>
                </div>
                <div class="booking-info mb-2">
                  <strong>Duration:</strong>
                  <?php echo htmlspecialchars($row['duration']); ?> HOURS
                </div>
                <div class="booking-info mb-2">
                  <strong>Location:</strong>
                  <?php echo htmlspecialchars($row['street_address']) . ', ' .
                    htmlspecialchars($row['barangay']) . ', ' .
                    htmlspecialchars($row['city']); ?>
                </div>
                <div class="booking-info mb-2">
                  <strong>Reference ID:</strong>
                  <?php echo htmlspecialchars($row['reference_id']); ?>
                </div>
                <div class="booking-info mb-2">
                  <strong>Status:</strong>
                  <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                    <?php echo htmlspecialchars($row['status'] === 'cancelled_by_user' ? 'Cancelled' : $row['status']); ?>
                  </span>
                </div>
                <div class="mt-auto pt-2">
                  <?php if (strtolower($row['status']) === 'cancelled_by_user'): ?>
                    <a href="/NEW-PM-JI-RESERVIFY/pages/customer/rebook.php?reference_id=<?php echo htmlspecialchars($row['reference_id']); ?>"
                      class="btn btn-success w-100">Re-book</a>
                  <?php else: ?>
                    <button class="btn btn-primary w-100 toggle-details"
                      data-target="#details-<?php echo $row['reference_number']; ?>">
                      View Details
                    </button>
                    <form method="POST" action="" class="mt-2">
                      <input type="hidden" name="cancel_reference_id"
                        value="<?php echo htmlspecialchars($row['reference_id']); ?>">
                      <button type="submit" class="btn btn-danger w-100">Cancel Booking</button>
                    </form>
                  <?php endif; ?>
                </div>
                <div id="details-<?php echo $row['reference_number']; ?>" class="booking-details mt-3"
                  style="display: none;">
                  <div class="booking-info mb-2">
                    <strong>Reference #:</strong>
                    <?php echo htmlspecialchars($row['reference_number']); ?>
                  </div>
                  <div class="booking-info mb-2">
                    <strong>Payment Status:</strong>
                    <?php echo htmlspecialchars($row['payment_status']); ?>
                  </div>
                  <div class="booking-info mb-2">
                    <strong>Payment:</strong>
                    <?php
                    $paymentType = htmlspecialchars($row['payment_type']);
                    $badgeClass = 'pending';
                    if (strtolower($paymentType) === 'paid') {
                      $badgeClass = 'paid';
                    } else if (strtolower($paymentType) === 'partial') {
                      $badgeClass = 'partial';
                    }
                    ?>
                    <span class="booking-badge <?php echo $badgeClass; ?>">
                      <?php echo $paymentType; ?>
                    </span>
                  </div>
                  <div class="booking-info mb-2">
                    <strong>Payment Method:</strong>
                    <?php echo htmlspecialchars($row['payment_method']); ?>
                  </div>
                  <div class="booking-info mb-2">
                    <strong>Screenshot:</strong>
                    <?php if (!empty($row['payment_screenshot'])): ?>
                      <a href="uploads/<?php echo htmlspecialchars($row['payment_screenshot']); ?>" target="_blank">
                        <i class="fas fa-image"></i> View
                      </a>
                    <?php else: ?>
                      N/A
                    <?php endif; ?>
                  </div>
                  <div class="booking-info mb-2">
                    <strong>Created At:</strong>
                    <?php echo htmlspecialchars($row['created_at']); ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <p class="text-center">No bookings found. Make a booking now!</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Modal Structure -->
  <div class="modal fade" id="bookingDetailsModal" tabindex="-1" role="dialog"
    aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="bookingDetailsModalLabel">Booking Details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" stlye="text-align: justify;">
          <!-- Booking details will be dynamically inserted here -->
          <div id="modalBookingDetails"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Cancel Confirmation Modal -->
  <div class="modal fade" id="cancelConfirmationModal" tabindex="-1" role="dialog"
    aria-labelledby="cancelConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cancelConfirmationModalLabel">Cancel Booking</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="text-align: justify;">
          <?php
          if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_reference_id'])) {
            $cancelReferenceId = $_POST['cancel_reference_id'];

            // Check booking status
            $statusQuery = "SELECT status FROM tbl_bookings WHERE reference_id = ? AND user_id = ?";
            $stmt = $conn->prepare($statusQuery);
            $stmt->bind_param("si", $cancelReferenceId, $userId);
            $stmt->execute();
            $stmt->bind_result($status);
            $stmt->fetch();
            $stmt->close();

            if (strtolower($status) === 'pending') {
              echo "You haven’t been approved yet. Cancelling now is penalty-free and you’ll receive a full refund. Do you want to continue?";
            } else {
              echo "Are you sure you want to cancel this booking?";
            }
          }
          ?>
        </div>
        <div class="modal-footer">
          <form method="POST" action="">
            <input type="hidden" name="cancel_reference_id" value="<?php echo htmlspecialchars($cancelReferenceId); ?>">
            <button type="submit" name="confirm_cancel" class="btn btn-danger">Confirm</button>
          </form>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/components/footer.php'; ?>

  <!-- Optionally include any additional JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.toggle-details').forEach(function (button) {
        button.addEventListener('click', function () {
          const target = button.getAttribute('data-target');
          const details = document.querySelector(target).innerHTML;
          document.getElementById('modalBookingDetails').innerHTML = details;
          $('#bookingDetailsModal').modal('show');
        });
      });

      // Trigger the cancel confirmation modal only once
      <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_reference_id'])): ?>
        $('#cancelConfirmationModal').modal('show');
      <?php endif; ?>
    });
  </script>
</body>

</html>