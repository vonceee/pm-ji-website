<?php
session_start();
if (!isset($_SESSION['user_email'])) {
  header("Location: /NEW-PM-JI-RESERVIFY/index.php");
  exit();
}

// gets the pre-selected event type from the query parameter
$preselectedEvent = isset($_GET['event']) ? htmlspecialchars($_GET['event']) : '';

$mysqli = new mysqli('127.0.0.1', 'root', '', 'db_pmji');
if ($mysqli->connect_error) {
  die('DB Connection Error: ' . $mysqli->connect_error);
}

/* ================= Calendar (Step 2: Booking Process) ================= */

$sql = "
  SELECT
    reservation_date,
    COUNT(*) AS cnt
  FROM tbl_bookings
  GROUP BY reservation_date
";
$result = $mysqli->query($sql);

$availability = [];
while ($row = $result->fetch_assoc()) {
  $date = $row['reservation_date'];
  $count = (int) $row['cnt'];

  if ($count >= 1) {
    $availability[$date] = 'red';    // Fully booked
  } else {
    $availability[$date] = 'green';  // Available
  }
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reserve Your Service - PM&JI Reservify</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/booking.css">
  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/components/top-header.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />

  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>

<body>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/top-header.php'; ?>

  <!-- Booking Form Container -->
  <div class="reservation-container">
    <div class="header-container">
      <h1 class="reservation-title">Booking</h1>
      <!-- Progress Indicator -->
      <div class="progress-indicator">
        <ul>
          <li class="step active" data-step="1">
            <span class="step-number">1</span>
            <span class="step-description">Event Type</span>
          </li>
          <li class="step" data-step="2">
            <span class="step-number">2</span>
            <span class="step-description">Date &amp; Time</span>
          </li>
          <li class="step" data-step="3">
            <span class="step-number">3</span>
            <span class="step-description">Event Location</span>
          </li>
          <li class="step" data-step="4">
            <span class="step-number">4</span>
            <span class="step-description">Review Details</span>
          </li>
          <li class="step" data-step="5">
            <span class="step-number">5</span>
            <span class="step-description">Payment</span>
          </li>
        </ul>
      </div>
    </div>

    <form action="process_booking.php" method="post" class="reservation-form" id="reservationForm"
      enctype="multipart/form-data">
      <!-- Step 1: Select Event Type -->
      <div class="form-step active" data-step="1">
        <div class="form-group">
          <label for="eventType">Event</label>
          <select class="form-control" name="event_type" id="eventType" required>
            <option value="" disabled <?= empty($preselectedEvent) ? 'selected' : '' ?>>select event type</option>
            <option value="Baptism" <?= $preselectedEvent === 'Baptism' ? 'selected' : '' ?>>Baptism</option>
            <option value="Birthday" <?= $preselectedEvent === 'Birthday' ? 'selected' : '' ?>>Birthday</option>
            <option value="Corporate Event" <?= $preselectedEvent === 'Corporate Event' ? 'selected' : '' ?>>Corporate
              Event</option>
            <option value="Reunion" <?= $preselectedEvent === 'Reunion' ? 'selected' : '' ?>>Reunion</option>
            <option value="Wedding" <?= $preselectedEvent === 'Wedding' ? 'selected' : '' ?>>Wedding</option>
          </select>
        </div>

        <div class="form-group">
          <label>Duration</label>
          <label for="duration2hr">
            <input id="duration2hr" type="radio" name="duration" value="3" required checked>
            <span class="name">3 hr/s</span>
          </label>
          <label for="duration4hr">
            <input id="duration4hr" type="radio" name="duration" value="4">
            <span class="name">4 hr/s</span>
          </label>
        </div>

        <div class="review-item">
          <span class="label">Price:</span>
          <span class="value" id="previewPrice"></span>
        </div>
        <br>

        <p class="important-note mt-3">
          <small><i class="fas fa-exclamation-circle"></i> <b>Note:</b> Extension of Hours during Event cost ₱1,800 per
            hour (Fixed at any Event).</small>
        </p>

        <!-- Package Selection as Cards -->
        <div class="form-group packages-selection">
          <label style="margin-bottom: 8px">Select Package(1):</label>
          <div class="card-deck">
            <!-- Package 1: Photo Standee Frame -->
            <label class="card package-card">
              <input class="form-check-input" type="radio" name="package" value="PhotoStandeeFrame" id="package_1"
                required>
              <img src="/NEW-PM-JI-RESERVIFY/assets/packages/photo_standee_frame.jpg" class="card-img-top package-img"
                alt="Photo Standee Frame">
              <div class="card-body">
                <h5 class="card-title">Photo Standee</h5>
                <p class="card-text">
                  - Customized Layout<br>
                  - 4 Plastic Standee Frame<br>
                  - 3 Ref Magnets (Single Shot)
                </p>
              </div>
            </label>

            <!-- Package 2: Polaroid Frame -->
            <label class="card package-card">
              <input class="form-check-input" type="radio" name="package" value="PolaroidFrame" id="package_2" required>
              <img src="/NEW-PM-JI-RESERVIFY/assets/packages/polaroid_frame.png" class="card-img-top package-img"
                alt="Polaroid Frame">
              <div class="card-body">
                <h5 class="card-title">Polaroid Frame</h5>
                <p class="card-text">
                  - Customized Layout<br>
                  - 4 Polaroid Frame<br>
                  - 3 Ref Magnets (Single Shot)
                </p>
              </div>
            </label>

            <!-- Package 3: 2x6 Photo Strip Frame -->
            <label class="card package-card">
              <input class="form-check-input" type="radio" name="package" value="PhotoStripFrame" id="package_3"
                required>
              <img src="/NEW-PM-JI-RESERVIFY/assets/packages/photo_strip_frame.png" class="card-img-top package-img"
                alt="2x6 Photo Strip Frame">
              <div class="card-body">
                <h5 class="card-title">2x6 Photo Strip</h5>
                <p class="card-text">
                  - Customized Layout<br>
                  - 4 2x6 Photo Strip Frame<br>
                  - 3 Ref Magnets (Single Shot)
                </p>
              </div>
            </label>
          </div>
        </div>

        <div class="form-navigation">
          <a href="/NEW-PM-JI-RESERVIFY/pages/customer/home.php" class="btn btn-danger">Cancel</a>
          <button type="button" class="next-btn btn">Next</button>
        </div>
      </div>

      <!-- Step 2: Set Date & Time -->
      <div class="form-step" data-step="2">
        <div class="form-group">
          <label for="reservationDate">Date</label>
          <input type="text" id="reservationDate" name="reservation_date" readonly required placeholder="select a date">
        </div>
        <div id="calendarLegend" class="calendar-legend">
          <p><span class="legend-box gray"></span> Unavailable</p>
          <p><span class="legend-box green"></span> Available</p>
          <p><span class="legend-box yellow"></span> Partially Booked</p> <!-- Added legend for Partially Booked -->
        </div>
        <br>

        <div class="form-group">
          <label for="startTime">Start Time</label>
          <select class="form-control" name="start_time" id="startTime" required>
            <?php for ($h = 8; $h <= 18; $h++): ?>
              <option value="<?= sprintf('%02d:00', $h) ?>">
                <?= date('g A', strtotime("$h:00")) // This outputs "8 AM", "9 AM", ... ?>
              </option>
            <?php endfor; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="endTime">End Time</label>
          <input type="text" class="form-control" name="end_time" id="endTime" readonly tabindex="-1"
            placeholder="End Time">
        </div>

        <p class="important-note mt-3">
          <small><i class="fas fa-exclamation-circle"></i> <b>Note:</b> Bookings must be made at least <u>1-Day</u>
            prior to the event date!<br>
          </small>
        </p>

        <div class="form-navigation">
          <button type="button" class="prev-btn btn btn-secondary">Previous</button>
          <button type="button" class="next-btn btn">Next</button>
        </div>
      </div>

      <!-- Step 3: Enter Location -->
      <div class="form-step" data-step="3">
        <!-- Note -->
        <p class="important-note mt-3">
          <small><i class="fas fa-exclamation-circle"></i> <b>Note:</b> Bookings are currently available only for
            locations within the National Capital Region (NCR).</small>
        </p>

        <div class="form-group">
          <label for="streetAddress">Street Address</label>
          <input type="text" class="form-control" name="street_address" id="streetAddress"
            placeholder="e.g., 123 Main St" required>
        </div>

        <script>
          document.getElementById('streetAddress').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '');
          });
        </script>

        <!-- City Dropdown -->
        <div class="form-group">
          <label for="citySelect">City</label>
          <select id="citySelect" name="city" class="form-control" required>
            <option value="">Loading…</option>
          </select>
          <input type="hidden" name="city_name" id="cityName">
        </div>

        <!-- Barangay Dropdown -->
        <div class="form-group">
          <label for="barangaySelect">Barangay</label>
          <select id="barangaySelect" name="barangay" class="form-control" required>
            <option value="">select a city first</option>
          </select>
          <input type="hidden" name="barangay_name" id="barangayName">
        </div>

        <!-- Full Address Field -->
        <div class="form-group">
          <label for="fullAddress">Full Address</label>
          <input type="text" class="form-control" name="full_address" id="fullAddress"
            placeholder="e.g., 123 Main St, Barangay, City, NCR" required>
        </div>

        <div class="form-navigation">
          <button type="button" class="prev-btn btn btn-secondary">Previous</button>
          <button type="button" class="next-btn btn">Next</button>
        </div>
      </div>

      <!-- Step 4: Review Booking -->
      <div class="form-step" data-step="4">
        <div class="review-card">
          <div class="review-item">
            <span class="label">Booking Details</span>
          </div>
          <div class="review-item">
            <span class="label">Event:</span>
            <span class="value" id="previewEventType"></span>
          </div>
          <div class="review-item">
            <span class="label">Duration:</span>
            <span class="value" id="previewDuration"></span>
          </div>
          <div class="review-item">
            <span class="label">Date:</span>
            <span class="value" id="previewDate"></span>
          </div>
          <div class="review-item">
            <span class="label">Start Time:</span>
            <span class="value" id="previewStartTime"></span>
          </div>
          <div class="review-item">
            <span class="label">End Time:</span>
            <span class="value" id="previewEndTime"></span>
          </div>
          <div class="review-item">
            <span class="label">Street Address:</span>
            <span class="value" id="previewStreetAddress"></span>
          </div>
          <div class="review-item">
            <span class="label">City:</span>
            <span class="value" id="previewCity"></span>
          </div>
          <div class="review-item">
            <span class="label">Barangay:</span>
            <span class="value" id="previewBarangay"></span>
          </div>
          <div class="review-item">
            <span class="label">Full Address:</span>
            <span class="value" id="previewFullAddress"></span>
          </div>
          <div class="review-item">
            <span class="label">Selected Package:</span>
            <span class="value" id="previewPackages"></span>
          </div>
        </div>
        <div class="review-item">
          <span class="label">Price:</span>
          <span class="value" id="previewPriceReview"></span>
        </div>
        <div class="form-navigation">
          <button type="button" class="prev-btn btn btn-secondary">Previous</button>
          <button type="button" class="next-btn btn">Next</button>
        </div>
      </div>

      <!-- Step 5: Payment -->
      <div class="form-step" data-step="5">
        <div class="form-group">
          <label>Payment Type</label>
          <div class="radio-inputs-19">
            <label for="downPayment">
              <input id="downPayment" type="radio" name="payment_type" value="Down Payment" required>
              <span class="name">Down Payment</span>
            </label>
            <label for="fullPayment">
              <input id="fullPayment" type="radio" name="payment_type" value="Full Payment" required>
              <span class="name">Full Payment</span>
            </label>
          </div>
        </div>
        <!-- Price Preview -->
        <div class="form-group">
          <label for="step5PricePreview">Price</label>
          <div class="price-preview" id="step5PricePreview">₱0.00</div>
        </div>
        <div class="form-group">
          <label>Payment Method</label>
          <div class="radio-inputs-19">
            <label for="paymentGCash">
              <input id="paymentGCash" type="radio" name="payment_method" value="GCash" required>
              <span class="name">GCash</span>
            </label>
            <label for="paymentPaymaya">
              <input id="paymentPaymaya" type="radio" name="payment_method" value="Paymaya" required>
              <span class="name">Paymaya</span>
            </label>
          </div>
        </div>
        <!-- Container to display QR Code based on Payment Method -->
        <div class="form-group" id="qrContainer" style="display:none;">
          <label>Scan QR Code:</label>
          <div id="qrBox" style="border:1px solid #ccc; border-radius:8px; padding:16px; background:#fafbfc;">
            <div class="row" style="align-items:center;">
              <!-- Left column: logo and details -->
              <div class="col-7" style="text-align:left;">
                <div id="qrLogo" style="margin-bottom:10px;">
                  <!-- Logo will be set dynamically -->
                </div>
                <div id="qrDetails" style="font-size:15px; margin-top:8px;">
                  <!-- Payment details will be set dynamically -->
                </div>
              </div>
              <!-- Right column: QR image -->
              <div class="col-5" style="text-align:center;">
                <div id="qrCode">
                  <!-- QR code image will be set dynamically -->
                  <img src="" alt="QR Code" id="qrImage" style="max-width: 120px; margin-bottom:12px;">
                </div>
              </div>
            </div>
          </div>
        </div>

        <script>
          document.addEventListener('DOMContentLoaded', () => {
            const qrContainer = document.getElementById('qrContainer');
            const qrImage = document.getElementById('qrImage');
            const qrDetails = document.getElementById('qrDetails');
            const qrLogo = document.getElementById('qrLogo');

            const qrPaths = {
              'GCash': '/NEW-PM-JI-RESERVIFY/assets/qr/gcash.png',
              'Paymaya': '/NEW-PM-JI-RESERVIFY/assets/qr/paymaya.png'
            };

            const logoPaths = {
              'GCash': '/NEW-PM-JI-RESERVIFY/assets/qr/gcash-logo.png',
              'Paymaya': '/NEW-PM-JI-RESERVIFY/assets/qr/paymaya-logo.png'
            };

            const paymentDetails = {
              'GCash': `
        <b>Account Name:</b> CL****L B.<br>
        <b>Mobile No.:</b> 091* ****138<br>
        <b>UserID:</b> ************WG22IK
      `,
              'Paymaya': `
        <b>Account Name:</b> CLEZIEL BERNIL<br>
        <b>Mobile No.:</b> +63 *** *** 2138<br>
        <b>UserID:</b> @cibernil
      `
            };

            document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
              radio.addEventListener('change', () => {
                if (!radio.checked) return;
                const method = radio.value;
                const path = qrPaths[method] || '';
                const logo = logoPaths[method] ? `<img src="${logoPaths[method]}" alt="${method} Logo" style="height:32px;vertical-align:middle;margin-right:8px;">` : '';
                qrImage.src = path;
                qrImage.alt = path ? `${method} QR Code` : 'QR Code';
                qrDetails.innerHTML = paymentDetails[method] || '';
                qrLogo.innerHTML = logo;
                qrContainer.style.display = path ? 'block' : 'none';
              });
            });
          });
        </script>

        <div class="form-group">
          <label for="referenceNumber">Reference Number</label>
          <input type="text" class="form-control" name="reference_number" id="referenceNumber"
            placeholder="Enter reference number" required>
        </div>
        <div class="form-group">
          <label for="paymentScreenshot">Upload Payment Screenshot</label>
          <input type="file" class="form-control" name="payment_screenshot" id="paymentScreenshot" accept="image/*"
            required>
        </div>
        <div class="form-navigation">
          <button type="button" class="prev-btn btn btn-secondary">Previous</button>
          <button type="submit" class="btn btn-reserve">Confirm Booking</button>
        </div>
      </div>
      <input type="hidden" name="price" id="bookingPrice" value="0">
    </form>

  </div>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

  <!-- location-select API-->
  <script src="/NEW-PM-JI-RESERVIFY/pages/customer/API/location-select.js"></script>

  <script>
    /***********************
     * Calendar Availability
     ***********************/
    var availability = <?= json_encode($availability, JSON_UNESCAPED_SLASHES) ?>;

    $(function () {
      $("#reservationDate").datepicker({
        dateFormat: "yy-mm-dd",
        minDate: 1, // Disable today and past dates, allow only dates starting from tomorrow
        beforeShowDay: function (date) {
          var dateString = $.datepicker.formatDate("yy-mm-dd", date);
          var status = availability[dateString];

          if (!status) {
            // Default: Green (available) for dates not in the availability array
            return [true, "green", "Available"];
          }

          if (status === "red") {
            // Red (fully booked)
            return [false, "red", "Fully booked"];
          }

          return [true, "green", "Available"];
        }
      });
    });

    /***********************
     * Price Calculation & Preview
     ***********************/
    // defined event prices.
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

    function updatePrice() {
      const eventType = document.getElementById('eventType').value;
      const durationEl = document.querySelector('input[name="duration"]:checked');
      const pricePreview = document.getElementById('previewPrice');
      const priceDisplay = document.getElementById('priceDisplay');
      const step5PricePreview = document.getElementById('step5PricePreview');
      const paymentTypeEl = document.querySelector('input[name="payment_type"]:checked');

      if (!eventType || !durationEl) {
        if (pricePreview) pricePreview.textContent = "₱0.00";
        if (priceDisplay) priceDisplay.textContent = "Price: ₱0.00";
        if (step5PricePreview) step5PricePreview.textContent = "₱0.00";
        return;
      }

      const durationHours = parseInt(durationEl.value, 10);
      let totalPrice = 0;

      if (
        overridePrices[durationHours] &&
        overridePrices[durationHours][eventType] !== undefined
      ) {
        totalPrice = overridePrices[durationHours][eventType];
      } else {
        // fallback: use 3-hour base price
        totalPrice = eventPrices[eventType] || 0;
      }

      // determine payment type and calculate price accordingly
      let paymentType = paymentTypeEl ? paymentTypeEl.value : 'Full Payment';
      let displayPrice = totalPrice;
      let label = '';

      if (paymentType === 'Down Payment') {
        displayPrice = Math.round(totalPrice * 0.5); // 30% down payment
        label = ' (Down Payment)';
      } else {
        label = ' (Full Payment)';
      }

      const formatted = `₱${displayPrice.toLocaleString()}.00${label}`;
      if (pricePreview) pricePreview.textContent = formatted;
      if (priceDisplay) priceDisplay.textContent = `Price: ${formatted}`;
      if (step5PricePreview) step5PricePreview.textContent = formatted;

      // step 4: update price in the review section
      const reviewPrice = document.getElementById('previewPriceReview');
      if (reviewPrice) reviewPrice.textContent = formatted;

      const bookingPriceInput = document.getElementById('bookingPrice');
      if (bookingPriceInput) bookingPriceInput.value = displayPrice; // Set the numeric value
    }

    // update price when payment type changes
    document.querySelectorAll('input[name="payment_type"]').forEach(input => {
      input.addEventListener('change', updatePrice);
    });

    // update price on event type and duration change (Step 1)
    document.getElementById('eventType').addEventListener('change', updatePrice);
    document.querySelectorAll('input[name="duration"]').forEach(input => {
      input.addEventListener('change', updatePrice);
    });

    function updatePreview() {
      document.getElementById('previewEventType').textContent = document.getElementById('eventType').value;

      const selectedDuration = document.querySelector('input[name="duration"]:checked');
      document.getElementById('previewDuration').textContent = selectedDuration ? selectedDuration.value + " Hours" : '';

      document.getElementById('previewDate').textContent = document.getElementById('reservationDate').value;
      document.getElementById('previewStreetAddress').textContent = document.getElementById('streetAddress').value;

      // Start Time
      const startTimeSelect = document.getElementById('startTime');
      let startTimeValue = startTimeSelect.value;
      let startTimeText = '';
      if (startTimeValue) {
        const [h, m] = startTimeValue.split(':').map(Number);
        startTimeText = formatTimeToAMPM(h, m);
      }
      document.getElementById('previewStartTime').textContent = startTimeText;

      // End Time
      const endTimeInput = document.getElementById('endTime');
      document.getElementById('previewEndTime').textContent = endTimeInput.value;

      // Full Address
      document.getElementById('previewFullAddress').textContent = document.getElementById('fullAddress').value;

      // City & Barangay
      const citySelect = document.getElementById('citySelect');
      const barangaySelect = document.getElementById('barangaySelect');
      const selectedCityOption = citySelect.options[citySelect.selectedIndex];
      document.getElementById('previewCity').textContent = selectedCityOption ? selectedCityOption.text : '';
      const selectedBarangayOption = barangaySelect.options[barangaySelect.selectedIndex];
      document.getElementById('previewBarangay').textContent = selectedBarangayOption ? selectedBarangayOption.text : '';

      updatePrice();

      // Package
      const selectedPackage = document.querySelector('input[name="package"]:checked');
      const packageName = selectedPackage
        ? selectedPackage.closest('.package-card').querySelector('.card-title').textContent
        : 'None selected';
      document.getElementById('previewPackages').textContent = packageName;
    }

    /***********************
 * Multi-Step Form Navigation
 ***********************/
    const formSteps = document.querySelectorAll('.form-step');
    const nextButtons = document.querySelectorAll('.next-btn');
    const prevButtons = document.querySelectorAll('.prev-btn');
    const progressSteps = document.querySelectorAll('.progress-indicator .step');

    // helper function to validate all required fields in current step.
    function validateStep(stepElement) {
      // get all input, select, and textarea elements in the current step.
      const inputs = stepElement.querySelectorAll('input, select, textarea');
      for (let input of inputs) {
        // if an input field is invalid according to HTML5 validations...
        if (!input.checkValidity()) {
          // show the built-in validation message.
          input.reportValidity();
          return false;
        }
      }
      return true;
    }

    function updateProgressIndicator(stepNumber) {
      progressSteps.forEach(step => {
        const stepData = parseInt(step.getAttribute('data-step'));
        if (stepData === stepNumber) {
          step.classList.add('active');
        } else {
          step.classList.remove('active');
        }
      });
    }

    nextButtons.forEach(button => {
      button.addEventListener('click', () => {
        const currentStep = button.closest('.form-step');
        // validate all required fields in the current step.
        if (!validateStep(currentStep)) {
          // do not continue to the next step if validation fails.
          return;
        }

        let currentStepNum = parseInt(currentStep.getAttribute('data-step'));
        // remove active class from the current step.

        currentStep.classList.remove('active');

        // special: when moving to the review step (step 4), update the preview.
        if (currentStepNum + 1 === 4) {
          updatePreview();
        }

        // find the next step and add active class.
        const nextStep = document.querySelector(`.form-step[data-step="${currentStepNum + 1}"]`);
        if (nextStep) {
          nextStep.classList.add('active');
          updateProgressIndicator(currentStepNum + 1);
          // scroll smoothly to the top of the page.
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      });
    });

    prevButtons.forEach(button => {
      button.addEventListener('click', () => {
        const currentStep = button.closest('.form-step');
        let currentStepNum = parseInt(currentStep.getAttribute('data-step'));
        currentStep.classList.remove('active');
        const prevStep = document.querySelector(`.form-step[data-step="${currentStepNum - 1}"]`);
        if (prevStep) {
          prevStep.classList.add('active');
          updateProgressIndicator(currentStepNum - 1);
          // scroll to the top upon navigating back.
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      });
    });

    // initialize the first step and price.
    updateProgressIndicator(1);
    updatePrice();

  </script>

  <!-- Setting up the End Time based on Start Time and Duration -->
  <script>

    function pad(num) {
      return num.toString().padStart(2, '0');
    }

    function updateEndTime() {
      const startTimeInput = document.getElementById('startTime');
      const endTimeInput = document.getElementById('endTime');
      const durationInput = document.querySelector('input[name="duration"]:checked');
      if (!startTimeInput.value || !durationInput) {
        endTimeInput.value = '';
        return;
      }
      const [startHour, startMin] = startTimeInput.value.split(':').map(Number);
      const duration = parseInt(durationInput.value, 10);

      let endHour = startHour + duration;
      let endMin = startMin;

      // if end time exceeds 24:00 or 18:00, clamp to 18:00
      if (endHour > 18 || (endHour === 18 && endMin > 0)) {
        endHour = 18;
        endMin = 0;
      }

      endTimeInput.value = `${pad(endHour)}:${pad(endMin)}`;
    }

    // update End Time when Start Time or Duration changes
    document.getElementById('startTime').addEventListener('input', updateEndTime);
    document.querySelectorAll('input[name="duration"]').forEach(input => {
      input.addEventListener('change', updateEndTime);
    });

    function formatTimeToAMPM(hour, minute) {
      const date = new Date();
      date.setHours(hour);
      date.setMinutes(minute);
      return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }).replace(':00', '').toUpperCase();
    }

    function updateEndTime() {
      const startTimeInput = document.getElementById('startTime');
      const endTimeInput = document.getElementById('endTime');
      const durationInput = document.querySelector('input[name="duration"]:checked');
      if (!startTimeInput.value || !durationInput) {
        endTimeInput.value = '';
        return;
      }
      const [startHour, startMin] = startTimeInput.value.split(':').map(Number);
      const duration = parseInt(durationInput.value, 10);

      let endHour = startHour + duration;
      let endMin = startMin;

      endTimeInput.value = formatTimeToAMPM(endHour, endMin);
    }
  </script>
  <!-- End of Setting up the End Time based on Start Time and Duration -->

  <!-- Loading Animation Script -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var form = document.getElementById('reservationForm');
      if (form) {
        form.addEventListener('submit', function () {
          NProgress.start();
        });
      }
      window.addEventListener('load', function () {
        NProgress.done();
      });
    });
  </script>
  <!-- End of Loading Animation Script -->

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

  <!-- location-select API-->
  <script src="/NEW-PM-JI-RESERVIFY/pages/customer/API/location-select.js"></script>

  <script>
    // Ensure city_name and barangay_name are set before form submit
    document.getElementById('reservationForm').addEventListener('submit', function () {
      var citySelect = document.getElementById('citySelect');
      var barangaySelect = document.getElementById('barangaySelect');
      var cityName = document.getElementById('cityName');
      var barangayName = document.getElementById('barangayName');
      cityName.value = citySelect.options[citySelect.selectedIndex]?.text || '';
      barangayName.value = barangaySelect.options[barangaySelect.selectedIndex]?.text || '';
    });
  </script>
</body>

</html>