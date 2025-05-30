<head>
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step2/booking-step2.css">
</head>

<!-- Step 2: Set Date & Time -->
<div class="form-step" data-step="2">
    <div class="form-group">
        <label for="reservationDate">Date</label>
        <input type="text" id="reservationDate" name="reservation_date" readonly required placeholder="select a date">
    </div>
    <div id="calendarLegend" class="calendar-legend">
        <p><span class="legend-box gray"></span> Unavailable</p>
        <p><span class="legend-box green"></span> Available</p>
        <p><span class="legend-box yellow"></span> Partially Booked</p>
    </div>
    <br>

    <div class="form-group">
        <label for="startTime">Start Time</label>
        <select class="form-control" name="start_time" id="startTime" required>
            <?php for ($h = 8; $h <= 18; $h++): ?>
                <?php $military = sprintf('%02d:00', $h); ?>
                <option value="<?= $military ?>">
                    <?= $military ?>
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

    <!-- Error Message -->
    <div id="step2-error" class="error-note text-danger" style="display:none;"></div>

</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> <!-- jQuery library for DOM manipulation -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script> <!-- jQuery UI for datepicker widget -->
<script src="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step2/booking-step2.js"></script>