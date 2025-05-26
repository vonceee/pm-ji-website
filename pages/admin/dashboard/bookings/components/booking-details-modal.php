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
                                    <li><strong>Status:</strong> <span id="modalPaymentStatus"
                                            class="payment-badge payment-partial">Partial</span></li>
                                    <li><strong>Amount Paid:</strong> <span id="modalAmountPaid">₱15,000.00</span>
                                    </li>
                                    <li><strong>Balance:</strong> <span id="modalBalance">₱10,000.00</span></li>
                                    <li><strong>Total:</strong> <span id="modalTotal">₱25,000.00</span></li>
                                    <li><strong>Payment Date:</strong> <span id="modalPaymentDate">May 20,
                                            2025</span></li>
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
                                    <li><strong>Venue:</strong> <span id="modalVenue">Grand Ballroom</span></li>
                                    <li><strong>Guests:</strong> <span id="modalGuests">150 people</span></li>
                                    <li><strong>Package:</strong> <span id="modalPackage">Premium Wedding
                                            Package</span></li>
                                    <li><strong>Special Requests:</strong> <span id="modalRequests">Live band,
                                            flower arrangements</span></li>
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
                                        <small class="text-muted">May 20, 2025 2:30 PM</small><br>
                                        <strong>Booking Created</strong><br>
                                        Customer submitted booking request
                                    </div>
                                    <div class="timeline-item">
                                        <small class="text-muted">May 20, 2025 3:15 PM</small><br>
                                        <strong>Payment Received</strong><br>
                                        Partial payment of ₱15,000.00
                                    </div>
                                    <div class="timeline-item">
                                        <small class="text-muted">May 20, 2025 4:00 PM</small><br>
                                        <strong>Status: Pending Review</strong><br>
                                        Awaiting admin approval
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-comment me-2"></i>Notes & Comments</h6>
                            </div>
                            <div class="card-body">
                                <div id="modalNotes">
                                    <p><strong>Customer Note:</strong> Please arrange for a live band setup area.
                                        We'll need power outlets for instruments.</p>
                                    <p><strong>Admin Note:</strong> Confirmed venue availability. Need to verify
                                        catering arrangements.</p>
                                </div>
                                <div class="mt-3">
                                    <textarea class="form-control" rows="3" placeholder="Add a note..."
                                        id="newNote"></textarea>
                                    <button class="btn btn-primary btn-sm mt-2" onclick="addNote()">
                                        <i class="fas fa-plus me-1"></i>Add Note
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success"
                    onclick="updateBookingStatus(currentBookingId, 'approved')">
                    <i class="fas fa-check me-1"></i>Approve Booking
                </button>
                <button type="button" class="btn btn-danger"
                    onclick="updateBookingStatus(currentBookingId, 'cancelled')">
                    <i class="fas fa-times me-1"></i>Cancel Booking
                </button>
            </div>
        </div>
    </div>
</div>