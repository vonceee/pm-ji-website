<!-- Booking Details Modal -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-labelledby="bookingDetailsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Booking Details - <span id="modalReferenceId"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Booking Information</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Reference ID:</strong> <span id="modalRefId"></span></li>
                                    <li><strong>Date:</strong> <span id="modalDate"></span></li>
                                    <li><strong>Time:</strong> <span id="modalTime"></span></li>
                                    <li><strong>Duration:</strong> <span id="modalDuration"></span></li>
                                    <li><strong>Event Type:</strong> <span id="modalEvent"></span></li>
                                    <li><strong>Status:</strong> <span id="modalStatus"></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-user me-2"></i>Customer Information</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Name:</strong> <span id="modalCustomerName"></span></li>
                                    <li><strong>Email:</strong> <span id="modalCustomerEmail"></span></li>
                                    <li><strong>Phone:</strong> <span id="modalCustomerPhone"></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Information</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Method:</strong> <span id="modalPaymentMethod"></span></li>
                                    <li><strong>Type:</strong> <span id="modalPaymentType"></span></li>
                                    <li><strong>Status:</strong> <span id="modalPaymentStatus"></span></li>
                                    <li><strong>Amount Paid:</strong> <span id="modalAmountPaid"></span></li>
                                    <li><strong>Balance:</strong> <span id="modalBalance"></span></li>
                                    <li><strong>Payment Date:</strong> <span id="modalPaymentDate"></span></li>
                                    <li>
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                                id="modalPaymentScreenshotBtn" style="display: none;">
                                            <i class="fas fa-image me-1"></i>View Payment Screenshot
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Event Details</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Location:</strong> <span id="modalLocation"></span></li>
                                    <li><strong>City:</strong> <span id="modalCity"></span></li>
                                    <li><strong>Special Requests:</strong> <span id="modalRequests"></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Activity Timeline</h6>
                            </div>
                            <div class="card-body">
                                <div class="timeline" id="modalTimeline">
                                    <div class="timeline-item">
                                        <small class="text-muted">Booking Created</small><br>
                                        <strong>Status: Pending</strong><br>
                                        Customer submitted booking request
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="approveBtn" style="display: none;"
                    onclick="updateBookingStatusFromModal('approved')">
                    <i class="fas fa-check me-1"></i>Approve Booking
                </button>
                <button type="button" class="btn btn-danger" id="cancelBtn" style="display: none;"
                    onclick="updateBookingStatusFromModal('cancelled')">
                    <i class="fas fa-times me-1"></i>Cancel Booking
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Screenshot Modal -->
<div class="modal fade" id="paymentScreenshotModal" tabindex="-1" aria-labelledby="paymentScreenshotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentScreenshotModalLabel">
                    <i class="fas fa-image me-2"></i>Payment Screenshot
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <!-- Loading State -->
                <div id="screenshotLoading" class="p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading Payment Screenshot...</p>
                </div>
                
                <!-- Screenshot Image -->
                <img id="paymentScreenshotImg" 
                     class="img-fluid rounded border" 
                     style="display: none; max-height: 500px;" 
                     alt="Payment Screenshot">
                
                <!-- Error State -->
                <div id="paymentScreenshotError" class="p-4" style="display: none;">
                    <div class="text-muted">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <h5>Screenshot Not Available</h5>
                        <p>no payment screenshot found for this booking or there was an error loading the image.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadPaymentScreenshot()" id="downloadBtn" style="display: none;">
                    <i class="fas fa-download me-1"></i>Download
                </button>
            </div>
        </div>
    </div>
</div>