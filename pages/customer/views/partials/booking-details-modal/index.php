<!-- Modern Booking Details Modal -->
<div class="modal-overlay" id="bookingDetailsModal">
    <div class="modal-container">
        <div class="modal-header">
            <div>
                <h2 class="modal-title">Booking Details</h2>
                <p class="modal-subtitle" id="modalReferenceNumber">Reference: </p>
            </div>
            <button class="close-button" onclick="closeModal()">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="modalStatusBadge"></div>
            <div class="info-grid">
                <!-- Event Info -->
                <div class="info-section">
                    <h4>
                        <svg class="info-section-icon" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Event Information
                    </h4>
                    <div class="info-row">
                        <span class="info-label">Event Type</span>
                        <span class="info-value" id="modalEventType">–</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date</span>
                        <span class="info-value" id="modalEventDate">–</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Time</span>
                        <span class="info-value" id="modalEventTime">–</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Duration</span>
                        <span class="info-value" id="modalDuration">–</span>
                    </div>
                </div>
                <!-- Location -->
                <div class="info-section">
                    <h4>
                        <svg class="info-section-icon" viewBox="0 0 24 24" fill="none">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        Location
                    </h4>
                    <div class="info-row">
                        <span class="info-label">Venue</span>
                        <span class="info-value" id="modalLocation">–</span>
                    </div>
                </div>
                <!-- Payment -->
                <div class="info-section">
                    <h4>
                        <svg class="info-section-icon" viewBox="0 0 24 24" fill="none">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        Payment Details
                    </h4>
                    <div class="info-row">
                        <span class="info-label">Amount Paid</span>
                        <span class="info-value amount" id="modalAmountPaid">₱0.00</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Balance</span>
                        <span class="info-value balance" id="modalBalance">₱0.00</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Method / Type</span>
                        <span class="info-value" id="modalPaymentMethod">–</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Status</span>
                        <span class="payment-status" id="modalPaymentStatus">–</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Date</span>
                        <span class="info-value" id="modalPaymentDate">–</span>
                    </div>
                </div>
                <!-- Payment Screenshot -->
                <div class="info-section" id="paymentScreenshotSection">
                    <h4>
                        <svg class="info-section-icon" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 15l-5-5L5 21" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Payment Screenshot
                    </h4>
                    <div class="screenshot-container">
                        <img id="paymentScreenshot" src="" alt="Payment Screenshot" class="payment-screenshot" onclick="openScreenshotModal(this.src)">
                        <p class="screenshot-note">Click image to view full size</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Close</button>
            <!-- <button class="btn btn-primary" id="downloadReceiptBtn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Download Receipt
            </button> -->
        </div>
    </div>
</div>

<!-- Screenshot Modal for Full Size View -->
<div class="modal-overlay" id="screenshotModal">
    <div class="screenshot-modal-container">
        <div class="screenshot-modal-header">
            <h3>Payment Screenshot</h3>
            <button class="close-button" onclick="closeScreenshotModal()">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
        <div class="screenshot-modal-body">
            <img id="fullSizeScreenshot" src="" alt="Payment Screenshot Full Size">
        </div>
    </div>
</div>