<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Reference ID</th>
                <th>Client</th>
                <th>Event</th>
                <th>Date</th>
                <th>Paid Amount</th>
                <th>Outstanding Balance</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($outstandingPayments as $payment): ?>
                <tr data-payment-id="<?= $payment['payment_id'] ?>">
                    <td>
                        <strong><?= htmlspecialchars($payment['reference_id']) ?></strong>
                    </td>
                    <td>
                        <div class="client-info">
                            <div class="client-name"><?= htmlspecialchars($payment['client_name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($payment['phone_number']) ?></small>
                        </div>
                    </td>
                    <td>
                        <div class="event-info">
                            <div><?= htmlspecialchars($payment['event_type']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($payment['city']) ?></small>
                        </div>
                    </td>
                    <td>
                        <div class="date-info">
                            <div><?= date('M d, Y', strtotime($payment['reservation_date'])) ?></div>
                            <small class="text-muted">
                                <?= date('h:i A', strtotime($payment['start_time'])) ?> -
                                <?= date('h:i A', strtotime($payment['end_time'])) ?>
                            </small>
                            <?php if (strtotime($payment['reservation_date']) < time()): ?>
                                <span class="badge badge-danger badge-sm">Overdue</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="amount-paid">₱<?= number_format($payment['amount_paid'], 2) ?></span>
                    </td>
                    <td>
                        <span class="balance-amount">
                            ₱<?= number_format($payment['balance'], 2) ?>
                        </span>
                    </td>
                    <td>
                        <span class="payment-status status-<?= $payment['payment_status'] ?>">
                            <?= ucfirst($payment['payment_status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-success btn-sm"
                                onclick="markAsPaid(<?= $payment['payment_id'] ?>, <?= $payment['balance'] ?>)"
                                title="Mark as Fully Paid">
                                <i class="fas fa-check"></i> Mark Paid
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>