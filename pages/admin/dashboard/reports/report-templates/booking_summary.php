<?php
// pages/admin/reports/report-templates/booking_summary.php
$data = $reportData;
$bookings = $data['bookings'];
$summary = $data['summary'];
?>

<!-- Detailed Booking List -->
<div class="report-section justify-content-between">
    <div class="section-header">
        <h3 class="section-title ml-2">
            <i class="fas fa-list me-2"></i>Detailed Booking List
        </h3>
    </div>
    
    <?php if (!empty($bookings)): ?>
        <div class="report-table">
            <table class="table table-striped" id="bookingsTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0, 'bookingsTable')" style="cursor: pointer;">
                            Reference ID <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(1, 'bookingsTable')" style="cursor: pointer;">
                            Client Name <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(2, 'bookingsTable')" style="cursor: pointer;">
                            Event Type <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(3, 'bookingsTable')" style="cursor: pointer;">
                            Date <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(4, 'bookingsTable')" style="cursor: pointer;">
                            Time <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(5, 'bookingsTable')" style="cursor: pointer;">
                            Location <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(6, 'bookingsTable')" style="cursor: pointer;">
                            Status <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(7, 'bookingsTable')" style="cursor: pointer;">
                            Payment <i class="fas fa-sort"></i>
                        </th>
                        <th onclick="sortTable(8, 'bookingsTable')" style="cursor: pointer;">
                            Amount <i class="fas fa-sort"></i>
                        </th>
                        <th>Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($booking['reference_id']) ?></strong>
                            </td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($booking['client_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($booking['client_email']) ?></small>
                                </div>
                            </td>
                            <td>
                                <span>
                                    <?= htmlspecialchars($booking['event_type']) ?>
                                </span>
                            </td>
                            <td>
                                <?= date('M d, Y', strtotime($booking['reservation_date'])) ?>
                            </td>
                            <td>
                                <div>
                                    <small>
                                        <?= date('g:i A', strtotime($booking['start_time'])) ?> - 
                                        <?= date('g:i A', strtotime($booking['end_time'])) ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="location-info">
                                    <small>
                                        <?= htmlspecialchars($booking['street_address']) ?><br>
                                        <?= htmlspecialchars($booking['barangay']) ?>, <?= htmlspecialchars($booking['city']) ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <?php
                                $statusClass = '';
                                switch ($booking['status']) {
                                    case 'pending':
                                        $statusClass = 'status-pending';
                                        break;
                                    case 'approved':
                                        $statusClass = 'status-approved';
                                        break;
                                    case 'confirmed':
                                        $statusClass = 'status-confirmed';
                                        break;
                                    case 'completed':
                                        $statusClass = 'status-completed';
                                        break;
                                    case 'cancelled':
                                        $statusClass = 'status-cancelled';
                                        break;
                                    default:
                                        $statusClass = 'status-pending';
                                }
                                ?>
                                <span <?= $statusClass ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($booking['payment_method'])): ?>
                                    <?php
                                    $paymentClass = '';
                                    switch ($booking['status']) {
                                        case 'paid':
                                            $paymentClass = 'payment-paid';
                                            break;
                                        case 'pending':
                                            $paymentClass = 'payment-pending';
                                            break;
                                        case 'partial':
                                            $paymentClass = 'payment-partial';
                                            break;
                                        case 'refunded':
                                            $paymentClass = 'payment-refunded';
                                            break;
                                        default:
                                            $paymentClass = 'payment-pending';
                                    }
                                    ?>
                                    <span <?= $paymentClass ?>>
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($booking['payment_method']) ?>
                                    </small>
                                    <?php if (!empty($booking['payment_date'])): ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('M d, Y', strtotime($booking['payment_date'])) ?>
                                        </small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="payment-badge payment-pending">No Payment</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="text-currency">
                                    ₱<?= number_format($booking['amount_paid'] ?? 0, 2) ?>
                                </strong>
                            </td>
                            <td>
                                <div class="contact-info">
                                    <?php if (!empty($booking['client_phone'])): ?>
                                        <small>
                                            <i class="fas fa-phone fa-xs"></i>
                                            <?= htmlspecialchars($booking['client_phone']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Table Summary -->
            <div class="table-summary mt-3 p-3 bg-light">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Total Records: <?= count($bookings) ?></strong>
                    </div>
                    <div class="col-md-6 text-end">
                        <strong>Total Revenue: ₱<?= number_format($summary['total_revenue'], 2) ?></strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Export Options (No Print) -->
        <div>
            <button type="button" class="btn btn-primary ml-2" onclick="printTableOnly()" <?= empty($reportType) ? 'disabled' : '' ?>>
                <i class="fas fa-print me-2"></i>Print Report
            </button>
        </div>
        
    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h5>No Bookings Found</h5>
            <p>no bookings match the selected criteria for the specified date range.</p>
            <div class="mt-3">
                <button type="button" class="btn btn-primary" onclick="resetFilters()">
                    <i></i>Reset Filters
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>