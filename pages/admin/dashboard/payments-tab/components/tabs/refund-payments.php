<div class="table-responsive">
    <table class="table table-hover" id="refunds-table">
        <thead>
            <tr>
                <th>Cancelled Date</th>
                <th>Reference ID</th>
                <th>Client</th>
                <th>Event</th>
                <th>Total Paid</th>
                <th>Refund Amount</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="refunds-tbody">
            <?php foreach ($pendingRefunds as $refund): ?>
                <tr data-refund-id="<?= $refund['refund_id'] ?>">
                    <td>
                        <div class="date-info">
                            <div><?= date('M d, Y', strtotime($refund['cancelled_at'])) ?></div>
                            <small class="text-muted"><?= date('h:i A', strtotime($refund['cancelled_at'])) ?></small>
                        </div>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($refund['reference_id']) ?></strong>
                    </td>
                    <td>
                        <div class="client-info">
                            <div class="client-name"><?= htmlspecialchars($refund['client_name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($refund['contact_no']) ?></small>
                        </div>
                    </td>
                    <td>
                        <div class="event-info">
                            <div><?= htmlspecialchars($refund['event_type']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($refund['city']) ?></small>
                            <div class="text-muted mt-1" style="font-size: 0.85em;">
                                <?= date('M d, Y', strtotime($refund['reservation_date'])) ?>
                                <?= date('h:i A', strtotime($refund['start_time'])) ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="amount-paid">₱<?= number_format($refund['amount_paid'] ?? 0, 2) ?></span>
                    </td>
                    <td>
                        <span class="refund-amount">
                            ₱<?= number_format($refund['refund_amount'], 2) ?>
                        </span>
                    </td>
                    <td>
                        <span>
                            <?= ucfirst($refund['refund_status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-info btn-sm me-1"
                                onclick="viewRefundReason('<?= htmlspecialchars($refund['reason']) ?>', '<?= htmlspecialchars($refund['client_name']) ?>')"
                                title="View Reason">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn btn-success btn-sm me-1"
                                onclick="processRefund(<?= $refund['refund_id'] ?>, <?= $refund['refund_amount'] ?>, '<?= htmlspecialchars($refund['client_name']) ?>')"
                                title="Process Refund">
                                <i class="fas fa-check"></i> Process
                            </button>
                            <button class="btn btn-danger btn-sm"
                                onclick="rejectRefund(<?= $refund['refund_id'] ?>, '<?= htmlspecialchars($refund['client_name']) ?>')"
                                title="Reject Refund">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>