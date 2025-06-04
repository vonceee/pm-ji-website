<?php
/**
 * components/filter-modal.php
 * Filter modal for booking data
 */
?>

<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">
                    <i class="fas fa-filter me-2"></i>Filter Bookings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from"
                                value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to"
                                value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="event_type" class="form-label">Event Type</label>
                            <select class="form-select" id="event_type" name="event_type">
                                <option value="">All Event Types</option>
                                <option value="Birthday" <?= ($_GET['event_type'] ?? '') === 'Birthday' ? 'selected' : '' ?>>Birthday</option>
                                <option value="Wedding" <?= ($_GET['event_type'] ?? '') === 'Wedding' ? 'selected' : '' ?>>
                                    Wedding</option>
                                <option value="Corporate" <?= ($_GET['event_type'] ?? '') === 'Corporate' ? 'selected' : '' ?>>Corporate</option>
                                <option value="Party" <?= ($_GET['event_type'] ?? '') === 'Party' ? 'selected' : '' ?>>
                                    Party</option>
                                <option value="Other" <?= ($_GET['event_type'] ?? '') === 'Other' ? 'selected' : '' ?>>
                                    Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select class="form-select" id="payment_status" name="payment_status">
                                <option value="">All Payment Status</option>
                                <option value="paid" <?= ($_GET['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>
                                    Paid</option>
                                <option value="partial" <?= ($_GET['payment_status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
                                <option value="unpaid" <?= ($_GET['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="?" class="btn btn-outline-warning">Clear Filters</a>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>