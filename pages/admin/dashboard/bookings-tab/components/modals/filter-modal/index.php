<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="filterModalLabel">
                <i class="fas fa-filter me-2"></i>Filter Bookings
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="GET" id="filterForm">
            <!-- maintain the view parameter to stay on bookings page -->
            <input type="hidden" name="view" value="bookings">
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from"
                            value="<?= htmlspecialchars($dateFrom) ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to"
                            value="<?= htmlspecialchars($dateTo) ?>">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <label class="form-label">Quick Date Ranges</label>
                        <div class="btn-group-vertical d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="setDateRange('today')">
                                Today
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="setDateRange('this_week')">
                                This Week
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="setDateRange('this_month')">
                                This Month
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="setDateRange('last_30_days')">
                                Last 30 Days
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="?view=bookings" class="btn btn-outline-secondary">Clear All</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
</div>