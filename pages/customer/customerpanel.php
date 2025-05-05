<?php
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_email'])) {
    header("Location: /NEW-PM-JI-RESERVIFY/index.php");
    exit;
}

$userEmail = $_SESSION['user_email'];

// --- Database Connections ---
$host = 'localhost';
$db = 'db_pmji';
$db_user = 'root'; // instead of $user
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

// Fetch user data using email
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
$query = "SELECT event_type, duration, reservation_date, street_address, barangay, city, province, reference_number, reference_id, payment_method, payment_type, payment_screenshot, status, payment_status, created_at 
          FROM tbl_bookings 
          WHERE user_id = ?";
$stmt3 = $conn->prepare($query);
$stmt3->bind_param("i", $userId);
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
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/components/top_header.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/components/footer.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/customerpanel.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        .tab-content > .tab-pane { padding-top: 30px; }
        .nav-tabs .nav-link.active { font-weight: bold; }
        .profile-container { max-width: 600px; margin: 0 auto; }
        .my-bookings-container { margin-top: 0 !important; }
    </style>
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/components/top_header.php'; ?>

<div class="container py-5">
  <div class="row">
    <!-- Profile Left Column -->
    <div class="col-md-4 mb-4">
      <div class="card profile-card shadow-sm">
        <div class="card-body">
          <h4 class="card-title mb-4 text-center">
            <i class="fas fa-user-circle fa-2x"></i><br>My Profile
          </h4>
          <div class="profile-details">
            <!-- First Name -->
            <div class="profile-field" data-field="first_name">
              <p>
                <strong>First Name:</strong>
                <span class="field-value"><?= htmlspecialchars($user['first_name']) ?></span>
                <i class="fas fa-pen edit-btn"></i>
              </p>
            </div>
            <!-- Middle Name -->
            <div class="profile-field" data-field="middle_name">
              <p>
                <strong>Middle Name:</strong>
                <span class="field-value"><?= htmlspecialchars($user['middle_name']) ?></span>
                <i class="fas fa-pen edit-btn"></i>
              </p>
            </div>
            <!-- Last Name -->
            <div class="profile-field" data-field="last_name">
              <p>
                <strong>Last Name:</strong>
                <span class="field-value"><?= htmlspecialchars($user['last_name']) ?></span>
                <i class="fas fa-pen edit-btn"></i>
              </p>
            </div>
            <!-- Contact No -->
            <div class="profile-field" data-field="contact_no">
              <p>
                <strong>Contact No:</strong>
                <span class="field-value"><?= htmlspecialchars($user['contact_no']) ?></span>
                <i class="fas fa-pen edit-btn"></i>
              </p>
            </div>
            <!-- Email -->
            <div class="profile-field" data-field="email">
              <p>
                <strong>Email:</strong>
                <span class="field-value"><?= htmlspecialchars($user['email']) ?></span>
                <i class="fas fa-pen edit-btn"></i>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs Right Column -->
    <div class="col-md-8">
      <ul class="nav nav-tabs" id="customerPanelTabs" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="bookings-tab" data-toggle="tab" href="#bookings" role="tab"
            aria-controls="bookings" aria-selected="true"><i class="fas fa-calendar-alt"></i> My Bookings</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="editprofile-tab" data-toggle="tab" href="#editprofile" role="tab"
            aria-controls="editprofile" aria-selected="false"><i class="fas fa-user-edit"></i> Edit Profile</a>
        </li>
      </ul>
      <div class="tab-content" id="customerPanelTabsContent">
        <!-- Bookings Tab -->
        <div class="tab-pane fade show active" id="bookings" role="tabpanel" aria-labelledby="bookings-tab">
          <section>
            <h5 class="my-bookings-title text-center mb-4">My Bookings</h5>
            <?php if ($result->num_rows > 0): ?>
              <div class="table-responsive">
                <table class="bookings-table">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Event</th>
                      <th>Duration</th>
                      <th>Location</th>
                      <th>Reference ID</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($row['reservation_date']) ?></td>
                      <td><?= htmlspecialchars($row['event_type']) ?></td>
                      <td><?= htmlspecialchars($row['duration']) ?> hrs</td>
                      <td>
                        <?= htmlspecialchars($row['street_address']) ?>,
                        <?= htmlspecialchars($row['barangay']) ?>,
                        <?= htmlspecialchars($row['city']) ?>
                      </td>
                      <td><?= htmlspecialchars($row['reference_id']) ?></td>
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
                          <button class="btn btn-primary btn-sm toggle-details mb-1"
                            data-target="#details-<?= $row['reference_number'] ?>">View Details</button>
                          <form method="POST" action="" class="d-inline">
                            <input type="hidden" name="cancel_reference_id"
                              value="<?= htmlspecialchars($row['reference_id']) ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                          </form>
                        <?php endif; ?>
                        <!-- Hidden details for modal -->
                        <div id="details-<?= $row['reference_number'] ?>" class="booking-details" style="display:none;">
                          <div><strong>Reference #:</strong> <?= htmlspecialchars($row['reference_number']) ?></div>
                          <div><strong>Payment Status:</strong> <?= htmlspecialchars($row['payment_status']) ?></div>
                          <div><strong>Payment:</strong>
                            <?php
                              $paymentType = htmlspecialchars($row['payment_type']);
                              $badgeClass = 'pending';
                              if (strtolower($paymentType) === 'paid') $badgeClass = 'paid';
                              else if (strtolower($paymentType) === 'partial') $badgeClass = 'partial';
                            ?>
                            <span class="badge badge-<?= $badgeClass ?>"><?= $paymentType ?></span>
                          </div>
                          <div><strong>Payment Method:</strong> <?= htmlspecialchars($row['payment_method']) ?></div>
                          <div><strong>Screenshot:</strong>
                            <?php if (!empty($row['payment_screenshot'])): ?>
                              <a href="uploads/<?= htmlspecialchars($row['payment_screenshot']) ?>" target="_blank">
                                <i class="fas fa-image"></i> View
                              </a>
                            <?php else: ?>N/A<?php endif; ?>
                          </div>
                          <div><strong>Created At:</strong> <?= htmlspecialchars($row['created_at']) ?></div>
                        </div>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <p class="text-center">No bookings found. Make a booking now!</p>
            <?php endif; ?>
          </section>
        </div>
        <!-- Edit Profile Tab -->
        <div class="tab-pane fade" id="editprofile" role="tabpanel" aria-labelledby="editprofile-tab">
          <section>
            <h5 class="mb-4 text-center"><i class="fas fa-user-edit"></i> Edit Profile</h5>
            <form id="editProfileForm" method="POST" action="/NEW-PM-JI-RESERVIFY/pages/customer/profile/update_profile.php">
              <div class="form-group">
                <label for="edit_first_name">First Name</label>
                <input type="text" class="form-control" id="edit_first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
              </div>
              <div class="form-group">
                <label for="edit_middle_name">Middle Name</label>
                <input type="text" class="form-control" id="edit_middle_name" name="middle_name" value="<?= htmlspecialchars($user['middle_name']) ?>">
              </div>
              <div class="form-group">
                <label for="edit_last_name">Last Name</label>
                <input type="text" class="form-control" id="edit_last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
              </div>
              <div class="form-group">
                <label for="edit_contact_no">Contact No</label>
                <input type="text" class="form-control" id="edit_contact_no" name="contact_no" value="<?= htmlspecialchars($user['contact_no']) ?>" required>
              </div>
              <div class="form-group">
                <label for="edit_email">Email</label>
                <input type="email" class="form-control" id="edit_email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
              </div>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
          </section>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Booking Details Modal -->
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
      <div class="modal-body" style="text-align: justify;">
        <div id="modalBookingDetails"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/components/footer.php'; ?>

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

  // Booking Details Modal
  $('.toggle-details').on('click', function () {
    var target = $(this).data('target');
    var details = $(target).html();
    $('#modalBookingDetails').html(details);
    $('#bookingDetailsModal').modal('show');
  });

  // Activate correct tab on hash change (optional)
  if(window.location.hash) {
    $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
  }
  $('.nav-tabs a').on('shown.bs.tab', function(e) {
    window.location.hash = e.target.hash;
  });
});
</script>
</body>
</html>