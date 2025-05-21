<?php ?>
<div class="table-responsive mt-4">
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Reference ID</th>
                <th>Event</th>
                <th>Date</th>
                <th>Status</th>
                <th>Payment Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($historyBookings as $booking): ?>
                <?php
                $stmtUser = $pdo->prepare("SELECT email, CONCAT(first_name, ' ', last_name) AS customer_name, contact_number AS customer_contact FROM tbl_users WHERE id = ?");
                $stmtUser->execute([$booking['user_id']]);
                $user = $stmtUser->fetch();
                $userEmail = $user['email'];
                $customerName = $user['customer_name'];
                $customerContact = $user['customer_contact'];
                ?>
                <tr>
                    <td>
                        <a href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/customers/profile.php?user_id=<?php echo htmlspecialchars($booking['user_id']); ?>">
                            <?php echo htmlspecialchars($userEmail); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($booking['reference_id']); ?></td>
                    <td><?php echo htmlspecialchars($booking['event_type']); ?></td>
                    <td><?php echo htmlspecialchars($booking['reservation_date']); ?></td>
                    <td><?php echo htmlspecialchars($booking['status']); ?></td>
                    <td><?php echo htmlspecialchars($booking['payment_status']); ?></td>
                    <td>
                        <a href="edit_booking.php?booking_id=<?php echo $booking['id']; ?>"
                            class="btn btn-primary btn-sm mx-1" title="Edit">
                            <i class="fas fa-pen"></i>
                        </a>
                        <button class="btn btn-secondary btn-sm mx-1" data-bs-toggle="collapse"
                            data-bs-target="#history-details-<?php echo $booking['id']; ?>"
                            title="Expand/Collapse">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </td>
                </tr>
                <tr class="collapse" id="history-details-<?php echo $booking['id']; ?>">
                    <td colspan="7">
                        <div class="p-3 bg-light border rounded">
                            <strong>Event Details:</strong><br>
                            <span class="ms-3">Duration:</span>
                            <?php echo htmlspecialchars($booking['duration']); ?> hours<br>
                            <span class="ms-3">Time Slot:</span>
                            <?php echo htmlspecialchars($booking['time_slot']); ?><br>
                            <br><strong>Location:</strong><br>
                            <span class="ms-3"><?php echo htmlspecialchars($booking['street_address'] . ', ' . $booking['barangay'] . ', ' . $booking['city'] . ', ' . $booking['province']); ?></span><br>
                            <br><strong>Payment:</strong><br>
                            <span class="ms-3">Method:</span>
                            <?php echo htmlspecialchars($booking['payment_method']); ?><br>
                            <span class="ms-3">Type:</span>
                            <?php echo htmlspecialchars($booking['payment_type']); ?><br>
                            <span class="ms-3">Status:</span>
                            <?php echo htmlspecialchars($booking['payment_status']); ?><br>
                            <span class="ms-3">Reference #:</span>
                            <?php echo htmlspecialchars($booking['reference_number']); ?><br>
                            <span class="ms-3">Screenshot:</span>
                            <?php if (!empty($booking['payment_screenshot'])): ?>
                                <a href="/NEW-PM-JI-RESERVIFY/pages/customer/uploads/<?php echo htmlspecialchars($booking['payment_screenshot']); ?>"
                                    download class="btn btn-link">Download</a>
                            <?php else: ?>
                                <span class="text-danger">No screenshot uploaded.</span>
                            <?php endif; ?><br>
                            <br><strong>Created At:</strong>
                            <?php echo htmlspecialchars($booking['created_at']); ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>