<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Ref ID</th>
                <th>Customer</th>
                <th>Event Type</th>
                <th>Date & Time</th>
                <th>Duration</th>
                <th>Location</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($approvedBookings as $booking): ?>
                <tr>
                    <td>
                        <strong>#<?= htmlspecialchars($booking['reference_id']) ?></strong>
                    </td>
                    <td>
                        <div class="customer-info">
                            <i class="fas fa-user me-2"></i>
                            <?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?>
                        </div>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($booking['event_type']) ?></strong>
                    </td>
                    <td>
                        <div class="datetime-info">
                            <div><?= date('M d, Y', strtotime($booking['reservation_date'])) ?></div>
                            <small><?= htmlspecialchars($booking['start_time'] . ' - ' . $booking['end_time']) ?></small>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-secondary"><?= $booking['duration'] ?> hrs</span>
                    </td>
                    <td>
                        <?= htmlspecialchars($booking['city']) ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                            <?= ucfirst($booking['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-primary"
                                onclick="updateBookingStatus(<?= $booking['id'] ?>, 'completed')" title="Mark Complete">
                                <i class="fas fa-check-double"></i>
                            </button>
                            <button class="btn btn-warning" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'pending')"
                                title="Back to Pending">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button class="btn btn-outline-info"
                                onclick="viewBookingDetails(<?= htmlspecialchars(json_encode($booking)) ?>)"
                                title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>