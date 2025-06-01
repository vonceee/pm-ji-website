<!-- Step 2: Set Date & Time -->
<div class="form-step" data-step="2">
    <!-- Error Message -->

    <div id="step2-error" class="error-message"></div>

    <!-- Date Selection -->
    <div class="form-group">
        <label for="reservationDate">Step 1: Select Event Date</label>
        <input type="text" id="reservationDate" name="reservation_date" form="reservationForm" readonly required
            placeholder="Select Date">

        <!-- Calendar Legend -->
        <div id="calendarLegend" class="calendar-legend">
            <p><span class="legend-box green"></span> Available</p>
            <p><span class="legend-box yellow"></span> Partially Booked</p>
            <p><span class="legend-box gray"></span> Unavailable</p>
        </div>
    </div>

    <!-- Time Selection Row -->
    <div class="form-row">
        <div class="col-md-6 mb-2">
            <div class="form-group">
                <label for="startTime">Step 2: Select Start Time</label>
                <select class="form-select" name="start_time" id="startTime" form="reservationForm" required>
                    <?php for ($h = 8; $h <= 18; $h++): ?>
                        <?php 
                            $military = sprintf('%02d:00', $h);
                            // Convert to 12-hour format for display
                            $display_time = date('g:i A', strtotime($military));
                        ?>
                        <option value="<?= $military ?>" data-display="<?= $display_time ?>">
                            <?= $display_time ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <div class="col-md-6 mb-2">
            <div class="form-group">
                <label for="endTime">End Time (Auto-calculated)</label>
                <input type="text" class="form-select" name="end_time" id="endTime" form="reservationForm" readonly
                    tabindex="-1" placeholder="End time will appear here">
            </div>
        </div>
    </div>

    <!-- Important Note -->
    <p class="important-note">
        <small><i class="fas fa-exclamation-circle"></i> <b>Note:</b> Bookings must be made at least <u>1-Day</u>
            prior to the event date!<br>
        </small>
    </p>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="/NEW-PM-JI-RESERVIFY/pages/customer/booking/components/booking-step2/booking-step2.js"></script>