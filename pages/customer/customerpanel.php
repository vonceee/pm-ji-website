<?php
session_start();

if (!isset($_SESSION['user_email'])) {
  header("Location: /NEW-PM-JI-RESERVIFY/index.php");
  exit;
}

$userEmail = $_SESSION['user_email'];

$host = 'localhost';
$db = 'db_pmji';
$db_user = 'root';
$pass = '';
$charset = 'utf8mb4';

// PDO for profile
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
  $pdo = new PDO($dsn, $db_user, $pass, $options); // use $db_user
} catch (PDOException $e) {
  die('Database connection failed: ' . $e->getMessage());
}

// fetch user data using email
$sql = "SELECT first_name, middle_name, last_name, contact_no, email FROM tbl_users WHERE email = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute([':email' => $userEmail]);
$user = $stmt->fetch();
if (!$user) {
  die('User not found.');
}

// MySQLi for bookings
$conn = new mysqli($host, $db_user, $pass, $db); // use $db_user
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// get the corresponding user id from tbl_users
$userIdQuery = "SELECT id FROM tbl_users WHERE email = ?";
$stmt2 = $conn->prepare($userIdQuery);
$stmt2->bind_param("s", $userEmail);
$stmt2->execute();
$stmt2->store_result();
if ($stmt2->num_rows === 0) {
  die("User not found.");
}
$stmt2->bind_result($userId);
$stmt2->fetch();
$stmt2->close();

// retrieve bookings from tbl_bookings for the user
// --- Pagination Logic ---
$limit = 5; // limit of bookings per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- Filtering & Search Logic ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_date = isset($_GET['filter_date']) ? trim($_GET['filter_date']) : '';

// Build WHERE clause
$where = "user_id = ?";
$params = [$userId];
$types = "i";

if ($search !== '') {
  $where .= " AND (event_type LIKE ? OR reference_number LIKE ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $types .= "ss";
}
if ($filter_date !== '') {
  $where .= " AND reservation_date = ?";
  $params[] = $filter_date;
  $types .= "s";
}

// get total bookings count (with filters)
$countQuery = "SELECT COUNT(*) FROM tbl_bookings WHERE $where";
$stmtCount = $conn->prepare($countQuery);
$stmtCount->bind_param($types, ...$params);
$stmtCount->execute();
$stmtCount->bind_result($totalBookings);
$stmtCount->fetch();
$stmtCount->close();

// get bookings for current page (with filters)
$query = "SELECT event_type, duration, reservation_date, start_time, end_time, street_address, barangay, city, reference_number, reference_id, payment_method, payment_type, payment_screenshot, status, payment_status, created_at, CONCAT(street_address, ', ', barangay, ', ', city) AS full_address
          FROM tbl_bookings 
          WHERE $where
          ORDER BY created_at DESC
          LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";
$stmt3 = $conn->prepare($query);
$stmt3->bind_param($types, ...$params);
$stmt3->execute();
$result = $stmt3->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Customer Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap & FontAwesome -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css" />
  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/components/top-header.css" />
  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/customerpanel.css" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/top-header.php'; ?>

  <div class="container-body">
    <div class="container py-5">
      <div class="row g-3">

        <!-- Left: 4-column profile card -->
        <div class="col-md-3 mb-4">
          <div class="card profile-card shadow-sm">
            <div class="card-body text-center">
              <i class="fas fa-user-circle fa-2x" style="font-size: 4rem;"></i>
              <h4 class="card-title">
                <br>
                <?= htmlspecialchars($user['first_name']) ?>
                <?php if (!empty($user['middle_name'])): ?>
                  <?= htmlspecialchars(strtoupper(substr($user['middle_name'], 0, 1))) ?>.
                <?php endif; ?>
                <?= htmlspecialchars($user['last_name']) ?>
              </h4>
              <p class="mb-1" style="font-size: 0.8rem;">
                <?= htmlspecialchars($user['email']) ?>
              </p>
              <p class="mb-1" style="font-size: 0.8rem;">
                <?= htmlspecialchars($user['contact_no']) ?>
              </p>
              <hr>
            </div>
          </div>
        </div>

        <!-- Right: 8-column content card -->
        <div class="col-md-9">
          <div class="card content-card shadow-sm">
            <div class="card-body">
              <!-- THIS is where you “add” your tabs or any other content -->
              <ul class="nav nav-tabs" id="customerPanelTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings"
                    type="button" role="tab">My Bookings</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings"
                    type="button" role="tab">Settings</button>
                </li>
              </ul>
              <div class="tab-content mt-3" id="customerPanelTabsContent">
                <div class="tab-pane fade show active" id="bookings" role="tabpanel">
                  <form class="form-inline mb-3" method="get" action="">
                    <input type="hidden" name="page" value="1">
                    <div class="form-group mr-2">
                      <input type="text" class="form-control" name="search" placeholder="Search Booking" value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="form-group mr-2">
                      <input type="date" class="form-control" name="filter_date" value="<?= htmlspecialchars($filter_date) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <?php if ($search || $filter_date): ?>
                      <a href="?page=1" class="btn btn-secondary ml-2">Reset</a>
                    <?php endif; ?>
                  </form>
                  <section>
                    <?php if ($result->num_rows > 0): ?>
                      <div class="table-responsive">
                        <table class="bookings-table">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th>Event</th>
                              <th>Date & Time</th>
                              <th>Status</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php $num = 1 + $offset; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                              <tr>
                                <td><?= $num++ ?></td>
                                <td><?= htmlspecialchars($row['event_type']) ?></td>
                                <td>
                                  <?php
                                  // Combine date and time as "YYYY-MM-DD, 1 PM - 4 PM"
                                  $date = htmlspecialchars($row['reservation_date']);
                                  $start = date('g A', strtotime($row['start_time']));
                                  $end = date('g A', strtotime($row['end_time']));
                                  echo "{$date}, {$start} - {$end}";
                                  ?>
                                </td>
                                <td>
                                  <span class="badge badge-<?= strtolower($row['status']) ?>">
                                    <?= htmlspecialchars($row['status'] === 'cancelled_by_user' ? 'Cancelled' : $row['status']) ?>
                                  </span>
                                </td>
                                <td>
                                  <?php if (strtolower($row['status']) === 'cancelled_by_user'): ?>
                                    <a href="/NEW-PM-JI-RESERVIFY/pages/customer/rebook.php?reference_id=<?= htmlspecialchars($row['reference_id']) ?>"
                                      class="btn btn-success btn-sm mb-1">Re-book</a>
                                  <?php else: ?>
                                    <button class="btn btn-primary btn-sm toggle-details"
                                      data-target="#details-<?= $row['reference_number'] ?>">View Details</button>
                                    <form method="POST" action="" class="d-inline">
                                      <input type="hidden" name="cancel_reference_id"
                                        value="<?= htmlspecialchars($row['reference_id']) ?>">
                                      <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                    </form>
                                  <?php endif; ?>
                                  <!-- Hidden details for modal -->
                                  <div id="details-<?= $row['reference_number'] ?>" class="booking-details" style="display:none;"
                                    data-reference-id="<?= htmlspecialchars($row['reference_id']) ?>"
                                    data-event-type="<?= htmlspecialchars($row['event_type']) ?>"
                                    data-event-date="<?= htmlspecialchars($row['reservation_date']) ?>"
                                    data-start-time="<?= htmlspecialchars($row['start_time']) ?>"
                                    data-end-time="<?= htmlspecialchars($row['end_time']) ?>"
                                    data-location="<?= htmlspecialchars($row['full_address']) ?>"
                                    data-total-cost="<?= isset($row['total_cost']) ? htmlspecialchars($row['total_cost']) : '' ?>"
                                    data-payment-type="<?= htmlspecialchars($row['payment_type']) ?>"
                                    data-payment-status="<?= htmlspecialchars($row['payment_status']) ?>"
                                    data-status="<?= htmlspecialchars($row['status']) ?>"
                                    data-remaining-balance="<?= isset($row['remaining_balance']) ? htmlspecialchars($row['remaining_balance']) : '' ?>"
                                    data-duration="<?= htmlspecialchars($row['duration']) ?>"
                                  ></div>
                                </td>
                              </tr>
                            <?php endwhile; ?>
                          </tbody>
                        </table>
                      </div>
                      <?php
                        $totalPages = ceil($totalBookings / $limit);
                        if ($totalPages > 1):
                      ?>
                      <nav aria-label="Bookings pagination" class="mt-3">
                        <ul class="pagination justify-content-center">
                          <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>" tabindex="-1">Previous</a>
                          </li>
                          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item<?= $i == $page ? ' active' : '' ?>">
                              <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                          <?php endfor; ?>
                          <li class="page-item<?= $page >= $totalPages ? ' disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                          </li>
                        </ul>
                      </nav>
                      <?php endif; ?>
                    <?php else: ?>
                      <p class="text-center">No bookings found. Make a booking now!</p>
                    <?php endif; ?>
                  </section>
                </div>
                <!-- Edit Profile Tab -->
                <div class="tab-pane fade" id="editprofile" role="tabpanel" aria-labelledby="editprofile-tab">
                  <section>
                    <h5 class="mb-4 text-center"><i class="fas fa-user-edit"></i> Edit Profile</h5>
                    <form id="editProfileForm" method="POST"
                      action="/NEW-PM-JI-RESERVIFY/pages/customer/profile/update_profile.php">
                      <div class="form-group">
                        <label for="edit_first_name">First Name</label>
                        <input type="text" class="form-control" id="edit_first_name" name="first_name"
                          value="<?= htmlspecialchars($user['first_name']) ?>" required>
                      </div>
                      <div class="form-group">
                        <label for="edit_middle_name">Middle Name</label>
                        <input type="text" class="form-control" id="edit_middle_name" name="middle_name"
                          value="<?= htmlspecialchars($user['middle_name']) ?>">
                      </div>
                      <div class="form-group">
                        <label for="edit_last_name">Last Name</label>
                        <input type="text" class="form-control" id="edit_last_name" name="last_name"
                          value="<?= htmlspecialchars($user['last_name']) ?>" required>
                      </div>
                      <div class="form-group">
                        <label for="edit_contact_no">Contact No</label>
                        <input type="text" class="form-control" id="edit_contact_no" name="contact_no"
                          value="<?= htmlspecialchars($user['contact_no']) ?>" required>
                      </div>
                      <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email"
                          value="<?= htmlspecialchars($user['email']) ?>" required>
                      </div>
                      <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                  </section>

                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1" role="dialog"
      aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog booking-modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="bookingDetailsModalLabel">Booking Details</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" style="text-align: justify;">
            <div id="modalBookingDetails"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <script>
      $(document).ready(function () {
        // Profile Inline Editing (left column)
        $('.edit-btn').on('click', function () {
          var $fieldContainer = $(this).closest('.profile-field');
          var $span = $fieldContainer.find('.field-value');
          var currentValue = $span.text().trim();

          $(this).hide();

          $span.data('old-value', currentValue);

          $span.attr('contenteditable', 'true').addClass('editing').focus();

          var iconsHtml = '<span class="edit-actions">' +
            '<i class="fas fa-check btn-save" title="Save"></i>' +
            '<i class="fas fa-times btn-cancel" title="Cancel"></i>' +
            '</span>';
          if ($fieldContainer.find('.edit-actions').length === 0) {
            $fieldContainer.find('p').append(iconsHtml);
          }
        });

        $(document).on('click', '.btn-save', function () {
          var $fieldContainer = $(this).closest('.profile-field');
          var fieldName = $fieldContainer.data('field');
          var $span = $fieldContainer.find('.field-value');
          var newValue = $span.text().trim();

          $.ajax({
            url: '/NEW-PM-JI-RESERVIFY/pages/customer/profile/update_profile.php',
            method: 'POST',
            data: { field: fieldName, value: newValue },
            success: function (response) {
              try {
                var res = JSON.parse(response);
                if (res.status === 'success') {
                  // Optionally show a success indicator here.
                } else {
                  alert('Update failed: ' + res.message);
                  $span.text($span.data('old-value'));
                }
              } catch (e) {
                alert('Unexpected error');
                $span.text($span.data('old-value'));
              }
            },
            error: function () {
              alert('Error updating profile.');
              $span.text($span.data('old-value'));
            },
            complete: function () {
              $span.removeAttr('contenteditable').removeClass('editing');
              $fieldContainer.find('.edit-actions').remove();
              $fieldContainer.find('.edit-btn').show();
            }
          });
        });

        $(document).on('click', '.btn-cancel', function () {
          var $fieldContainer = $(this).closest('.profile-field');
          var $span = $fieldContainer.find('.field-value');
          $span.text($span.data('old-value'));
          $span.removeAttr('contenteditable').removeClass('editing');
          $fieldContainer.find('.edit-actions').remove();
          $fieldContainer.find('.edit-btn').show();
        });

        // ...inside your <script> block, before the .toggle-details click handler...

        // 1. Base for 3 hours
        const eventPrices = {
          'Baptism': 4500,
          'Birthday': 4000,
          'Corporate Event': 7000,
          'Reunion': 5000,
          'Wedding': 5000
        };

        // 2. Base for 4 hours
        const overridePrices = {
          4: {
            'Baptism': 4600,
            'Birthday': 4500,
            'Corporate Event': 8000,
            'Reunion': 6500,
            'Wedding': 11000
          }
        };

        function computeTotalCost(eventType, duration) {
          if (overridePrices[duration] && overridePrices[duration][eventType]) {
            return overridePrices[duration][eventType];
          }
          return eventPrices[eventType] || 0;
        }

        function computeRemainingBalance(totalCost, paymentStatus) {
          // if payment is pending, remaining balance is 50% of total cost
          if (paymentStatus && paymentStatus.toLowerCase() === 'pending') {
            return totalCost / 2;
          }
          return 0;
        }

        // Booking Details Modal
        $('.toggle-details').on('click', function () {
          var target = $(this).data('target');
          var $details = $(target);

          // gather data attributes
          var referenceId = $details.data('reference-id');
          var eventType = $details.data('event-type');
          var eventDate = $details.data('event-date');
          var startTime = $details.data('start-time');
          var endTime = $details.data('end-time');
          var location = $details.data('location');
          var paymentType = $details.data('payment-type');
          var paymentStatus = $details.data('payment-status');
          var status = $details.data('status');
          var duration = $details.data('duration');
          // compute total cost and remaining balance
          var totalCost = computeTotalCost(eventType, duration);
          var remainingBalance = computeRemainingBalance(totalCost, paymentStatus);

          // format date and time
          var eventDateTime = eventDate + ', ' + moment(startTime, 'HH:mm:ss').format('h A') + ' - ' + moment(endTime, 'HH:mm:ss').format('h A');

          // Payment Badge
          var paymentBadge = '';
          if (paymentType && paymentType.toLowerCase() === 'paid') {
            paymentBadge = '<span class="badge badge-paid ml-2">Paid</span>';
          } else if (paymentType && paymentType.toLowerCase() === 'partial') {
            paymentBadge = '<span class="badge badge-partial ml-2">Partial</span>';
          } else {
            paymentBadge = '<span class="badge badge-pending ml-2">Pending</span>';
          }

          // payment status and remaining balance
          var paymentInfo = '';
          if (paymentType && paymentType.toLowerCase() === 'pending' && remainingBalance) {
            paymentInfo = '₱' + totalCost + paymentBadge + '<br><small>Remaining Balance: ₱' + remainingBalance + '</small>';
          } else {
            paymentInfo = '₱' + totalCost + paymentBadge;
          }

          // Booking Status Badge
          var statusBadge = '<span class="badge badge-' + status.toLowerCase() + '">' + (status === 'cancelled_by_user' ? 'Cancelled' : status) + '</span>';

          // Modal Content
          var html = `
            <div class="mb-2"><strong>Reference ID:</strong> ${referenceId}</div>
            <div class="mb-2"><strong>Event Type:</strong> ${eventType}</div>
            <div class="mb-2"><strong>Event Date & Time:</strong> ${eventDateTime}</div>
            <div class="mb-2"><strong>Location/Venue:</strong> ${location}</div>
            <div class="mb-2"><strong>Total Cost:</strong> ${paymentInfo}</div>
            <div class="mb-2"><strong>Booking Status:</strong> ${statusBadge}</div>
          `;

          $('#modalBookingDetails').html(html);
          $('#bookingDetailsModal').modal('show');
        });

        // activate correct tab on hash change (optional)
        if (window.location.hash) {
          $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
        }
        $('.nav-tabs a').on('shown.bs.tab', function (e) {
          window.location.hash = e.target.hash;
        });
      });
    </script>
    <!-- Add moment.js for time formatting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
</body>

</html>