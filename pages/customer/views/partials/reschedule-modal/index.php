<div class="modal fade" id="rescheduleBookingModal" tabindex="-1" role="dialog"
    aria-labelledby="rescheduleBookingModalLabel" aria-hidden="true">
    <div class="reschedule-booking-modal-dialog modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="rescheduleBookingModalLabel">
                    <i class="fas fa-calendar-alt"></i> Reschedule Booking
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Reschedule your booking to a new date and time.</strong>
                </div>

                <!-- Current Booking Details -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Current Booking Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Event:</strong> <span id="reschedule-event-type"></span></p>
                                <p><strong>Reference:</strong> <span id="reschedule-reference-id"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Current Date:</strong> <span id="reschedule-current-date"></span></p>
                                <p><strong>Current Time:</strong> <span id="reschedule-current-time"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Booking Details Form -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Select New Date & Time</h6>
                    </div>
                    <div class="card-body">
                        <!-- New Date Selection -->
                        <div class="form-group">
                            <label for="new-event-date">
                                <i class="fas fa-calendar"></i> New Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="new-event-date" required>
                            <small class="form-text text-muted">Select a date at least 24 hours from now.</small>
                        </div>

                        <!-- Time Slots Section -->
                        <div id="time-slots-section" style="display: none;">
                            <label class="form-label">
                                <i class="fas fa-clock"></i> Available Time Slots <span class="text-danger">*</span>
                            </label>
                            <div id="time-slots-container">
                                <!-- Time slots will be loaded here dynamically -->
                            </div>
                        </div>

                        <!-- Hidden inputs for selected times -->
                        <input type="hidden" id="new-start-time">
                        <input type="hidden" id="new-end-time">

                        <!-- Reason for Reschedule -->
                        <div class="form-group mt-3">
                            <label for="reschedule-reason">
                                <i class="fas fa-comment"></i> Reason for Rescheduling <span
                                    class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="reschedule-reason" rows="3" required
                                placeholder="Please provide a reason for rescheduling this booking..."></textarea>
                            <small class="form-text text-muted">This information helps us improve our services.</small>
                        </div>
                    </div>
                </div>

                <!-- Reschedule Policy -->
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Reschedule Policy:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Rescheduling must be done at least 24 hours before the original event date.</li>
                        <li>You can reschedule your booking once without additional charges.</li>
                        <li>Additional rescheduling requests may incur fees.</li>
                        <li>New date must be within the next 6 months.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-arrow-left"></i> Cancel
                </button>
                <button type="button" class="btn btn-warning" id="confirm-reschedule-booking">
                    <i class="fas fa-calendar-alt"></i> Confirm Reschedule
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .time-slot-btn {
        transition: all 0.2s ease;
    }

    .time-slot-btn:hover {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    .time-slot-btn.active {
        background-color: #28a745;
        color: white;
        border-color: #28a745;
    }

    .reschedule-booking-modal-dialog {
        max-width: 700px;
    }

    #time-slots-container .row {
        margin: 0;
    }

    #time-slots-container .col-md-4,
    #time-slots-container .col-sm-6 {
        padding: 0 5px;
    }
</style>