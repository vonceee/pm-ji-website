<div class="table-responsive">
    <table class="table table-hover" id="payment-history-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference ID</th>
                <th>Client</th>
                <th>Event</th>
                <th>Total Amount</th>
                <th>Amount Paid</th>
                <th>Balance</th>
                <th>Status</th>
                <th>Payment Method</th>
            </tr>
        </thead>
        <tbody id="payment-history-tbody">
            <?php foreach ($paymentHistory as $history): ?>
                <tr>
                    <td>
                        <div class="date-info">
                            <div><?= date('M d, Y', strtotime($history['created_at'])) ?></div>
                            <small class="text-muted"><?= date('h:i A', strtotime($history['created_at'])) ?></small>
                        </div>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($history['reference_id']) ?></strong>
                    </td>
                    <td>
                        <div class="client-info">
                            <div class="client-name">
                                <?= htmlspecialchars($history['client_name']) ?>
                            </div>
                            <small class="text-muted"><?= htmlspecialchars($history['phone_number']) ?></small>
                        </div>
                    </td>
                    <td>
                        <div class="event-info">
                            <div><?= htmlspecialchars($history['event_type']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($history['city']) ?></small>
                        </div>
                    </td>
                    <td>
                        <span class="total-amount">₱<?= number_format($history['total_amount'], 2) ?></span>
                    </td>
                    <td>
                        <span class="amount-paid">₱<?= number_format($history['amount_paid'], 2) ?></span>
                    </td>
                    <td>
                        <span class="balance-amount">₱<?= number_format($history['balance'], 2) ?></span>
                    </td>
                    <td>
                        <span class="payment-status status-<?= $history['payment_status'] ?>">
                            <?= ucfirst($history['payment_status']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="payment-method"><?= ucfirst($history['payment_method'] ?? 'N/A') ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>