<?php ?>
<div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
    <div class="table-responsive mt-4">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Customer Email</th>
                    <th>Event</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingBookings as $booking): ?>
                    <?php
                    $stmtUser = $pdo->prepare("SELECT email, CONCAT(first_name, ' ', last_name) AS customer_name, contact_no AS customer_contact FROM tbl_users WHERE id = ?");
                    $stmtUser->execute([$booking['user_id']]);
                    $user = $stmtUser->fetch();
                    $userEmail = $user['email'];
                    $customerName = $user['customer_name'];
                    $customerContact = $user['customer_contact'];
                    ?>
                    <tr class="booking-row" data-reference-id="<?= htmlspecialchars($booking['reference_id']) ?>"
                        data-customer-name="<?= htmlspecialchars($customerName) ?>"
                        data-date="<?= date('M d, Y', strtotime($booking['reservation_date'])) ?>"
                        data-time="<?= htmlspecialchars($booking['start_time']) ?>"
                        data-event="<?= htmlspecialchars($booking['event_type']) ?>"
                        data-location="<?= htmlspecialchars($booking['street_address'] . ', ' . $booking['barangay'] . ', ' . $booking['city']) ?>"
                        data-customer-fullname="<?= htmlspecialchars($customerName) ?>"
                        data-customer-contact="<?= htmlspecialchars($customerContact) ?>"
                        data-customer-email="<?= htmlspecialchars($userEmail) ?>"
                        data-payment-method="<?= htmlspecialchars($booking['payment_method']) ?>"
                        data-payment-status="<?= htmlspecialchars($booking['payment_status']) ?>"
                        data-total-amount="<?= number_format($booking['price'], 2) ?>" style="cursor:pointer;">
                        <td>
                            <?php
                            $date = date('M d, Y', strtotime($booking['reservation_date']));
                            $time = isset($booking['start_time']) ? htmlspecialchars($booking['start_time']) : '';
                            echo $date . ($time ? " <br><small class='text-muted'>$time</small>" : '');
                            ?>
                        </td>
                        <td>
                            <a
                                href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/customers/profile.php?user_id=<?= htmlspecialchars($booking['user_id']) ?>">
                                <?= htmlspecialchars($userEmail) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($booking['event_type']) ?></td>
                        <td><?= htmlspecialchars($booking['duration']) ?> Hours</td>
                        <td>
                            <form action="update_booking_status.php" method="POST" class="mb-0">
                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending
                                    </option>
                                    <option value="approved" <?= $booking['status'] === 'approved' ? 'selected' : '' ?>>
                                        Approved</option>
                                    <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>
                                        Cancelled</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <?= htmlspecialchars($booking['payment_status']) ?>
                            <?php if (!empty($booking['payment_method'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($booking['payment_method']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= date('M d, Y h:i A', strtotime($booking['created_at'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>