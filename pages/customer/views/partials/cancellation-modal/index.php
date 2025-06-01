<div class="modal fade" id="cancelBookingModal" tabindex="-1" role="dialog" aria-labelledby="cancelBookingModalLabel"
    aria-hidden="true">
    <div class="cancel-booking-modal-dialog modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancelBookingModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Cancel Booking
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-warning"></i>
                    <strong>Are you sure you want to cancel this booking?</strong>
                    <p class="mb-0 mt-2"><small>This action cannot be undone. Please review the details below before
                            proceeding.</small></p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Booking Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Event:</strong> <span id="cancel-event-type"></span></p>
                                <p><strong>Date:</strong> <span id="cancel-event-date"></span></p>
                                <p><strong>Time:</strong> <span id="cancel-event-time"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Reference:</strong> <span id="cancel-reference-number"></span></p>
                                <p><strong>Status:</strong> <span class="badge badge-info"
                                        id="cancel-status">Confirmed</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label for="cancellation-reason">
                        <i class="fas fa-comment"></i> Reason for cancellation <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="cancellation-reason" rows="3" required
                        placeholder="Please provide a reason for cancelling this booking..."></textarea>
                    <small class="form-text text-muted">This information helps us improve our services.</small>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i>
                    <strong>Cancellation Policy:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Cancellations must be made at least 24 hours before the event</li>
                        <li>Refunds will be processed according to our refund policy</li>
                        <li>Cancellation fees may apply as per terms and conditions</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-arrow-left"></i> Keep Booking
                </button>
                <button type="button" class="btn btn-danger" id="confirm-cancel-booking">
                    <i class="fas fa-times"></i> Cancel Booking
                </button>
            </div>
        </div>
    </div>
</div>