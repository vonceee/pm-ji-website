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
                <th>Payment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($historyBookings as $booking): ?>
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
                        <?php if ($booking['payment_status']): ?>
                            <div class="payment-info">
                                <span class="payment-badge payment-<?= strtolower($booking['payment_status']) ?>">
                                    <?= ucfirst($booking['payment_status']) ?>
                                </span>
                                <div>₱<?= number_format($booking['amount_paid'] ?? 0, 2) ?></div>
                                <?php if ($booking['refund_amount']): ?>
                                    <small class="text-muted">Refund:
                                        ₱<?= number_format($booking['refund_amount'], 2) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                            <?= ucfirst($booking['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-info"
                                onclick="viewBookingDetails(<?= htmlspecialchars(json_encode($booking)) ?>)"
                                title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-outline-primary" onclick="printBooking(<?= $booking['id'] ?>)"
                                title="Print Receipt">
                                <i class="fas fa-print"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>