<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_email'])) {
    header('Location: /NEW-PM-JI-RESERVIFY/index.php');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
use Config\Database;

$pdo = Database::getConnection();
$userEmail = $_SESSION['user_email'];

$stmtUser = $pdo->prepare('SELECT id FROM tbl_users WHERE email = :email');
$stmtUser->execute([':email' => $userEmail]);
$userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
if (!$userRow) {
    die('user not found.');
}
$userId = (int) $userRow['id'];

$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page'])
    ? (int) $_GET['page']
    : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_date = isset($_GET['filter_date']) ? trim($_GET['filter_date']) : '';
$filter_status = isset($_GET['filter_status']) ? trim($_GET['filter_status']) : '';

$where = 'b.user_id = :user_id';
$params = [':user_id' => $userId];

if ($search !== '') {
    $where .= ' AND (b.event_type LIKE :search1 OR b.reference_number LIKE :search2)';
    $params[':search1'] = "%{$search}%";
    $params[':search2'] = "%{$search}%";
}

if ($filter_date !== '') {
    $where .= ' AND b.reservation_date = :filter_date';
    $params[':filter_date'] = $filter_date;
}

if ($filter_status !== '') {
    $where .= ' AND b.status = :filter_status';
    $params[':filter_status'] = $filter_status;
}

$countSql = "SELECT COUNT(*) FROM tbl_bookings b WHERE {$where}";
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalBookings = (int) $stmtCount->fetchColumn();

$dataSql = <<<SQL
SELECT
    b.id,
    b.event_type,
    b.duration,
    b.reservation_date,
    b.start_time,
    b.end_time,
    b.street_address,
    b.barangay,
    b.city,
    b.reference_number,
    b.reference_id,
    b.status,
    b.created_at,
    b.full_address,
    p.payment_method,
    p.payment_type,
    p.status     AS payment_status,
    p.amount_paid,
    p.balance,
    p.payment_date,
    p.payment_screenshot,
    c.reason AS cancellation_reason,
    c.cancelled_at,
    c.refund_status,
    c.refund_amount
FROM tbl_bookings b
LEFT JOIN tbl_payments p 
    ON b.id = p.booking_id
LEFT JOIN tbl_cancellations c
    ON b.id = c.booking_id
WHERE {$where}
ORDER BY b.created_at DESC
LIMIT :limit OFFSET :offset
SQL;

$params[':limit'] = $limit;
$params[':offset'] = $offset;

$stmtData = $pdo->prepare($dataSql);
$stmtData->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmtData->bindValue(':offset', $offset, PDO::PARAM_INT);

foreach ($params as $key => $value) {
    if (in_array($key, [':limit', ':offset'], true))
        continue;
    $stmtData->bindValue($key, $value);
}
$stmtData->execute();
$result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/src/utils/pagination.php';
use Utils\Pagination;

function canCancelBooking($status, $reservationDate)
{
    $cancelableStatuses = ['confirmed', 'pending'];
    if (!in_array(strtolower($status), $cancelableStatuses)) {
        return false;
    }

    $bookingDateTime = strtotime($reservationDate);
    $currentTime = time();
    $timeDifference = $bookingDateTime - $currentTime;

    return $timeDifference >= 86400; // 24 hours in seconds
}

function getStatusBadgeClass($status)
{
    $statusClasses = [
        'pending' => 'warning',
        'confirmed' => 'success',
        'completed' => 'primary',
        'cancelled_by_user' => 'danger',
        'cancelled_by_admin' => 'danger',
        'no_show' => 'dark'
    ];

    return $statusClasses[strtolower($status)] ?? 'secondary';
}

function getStatusDisplay($status)
{
    $statusDisplays = [
        'cancelled_by_user' => 'Cancelled',
        'cancelled_by_admin' => 'Cancelled by Admin',
        'no_show' => 'No Show'
    ];

    return $statusDisplays[$status] ?? ucfirst($status);
}

function getTimeDifferenceText($reservationDate)
{
    $bookingDateTime = strtotime($reservationDate);
    $currentTime = time();
    $timeDifference = $bookingDateTime - $currentTime;

    if ($timeDifference < 0) {
        return 'Past event';
    }

    $hours = floor($timeDifference / 3600);
    $days = floor($hours / 24);

    if ($days > 0) {
        return $days . ' day' . ($days > 1 ? 's' : '') . ' away';
    } elseif ($hours > 0) {
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' away';
    } else {
        return 'Less than 1 hour away';
    }
}
?>

<form class="form-inline mb-3" method="get" action="">
    <input type="hidden" name="page" value="1">

    <!-- Search Input -->
    <div class="form-group mr-2">
        <input type="text" class="form-control" name="search" placeholder="Search by event or reference"
            value="<?= htmlspecialchars($search) ?>">
    </div>

    <!-- Date Filter -->
    <div class="form-group mr-2">
        <input type="date" class="form-control" name="filter_date" value="<?= htmlspecialchars($filter_date) ?>">
    </div>

    <!-- Status Filter -->
    <div class="form-group mr-2">
        <select class="form-control" name="filter_status">
            <option value="">All Statuses</option>
            <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="confirmed" <?= $filter_status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="completed" <?= $filter_status === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="cancelled_by_user" <?= $filter_status === 'cancelled_by_user' ? 'selected' : '' ?>>Cancelled</option>
        </select>
    </div>

    <!-- Submit & Reset -->
    <button type="submit" class="btn btn-primary">Filter</button>
    <?php if ($search || $filter_date || $filter_status): ?>
            <a href="?page=1" class="btn btn-secondary ml-2">Reset</a>
    <?php endif; ?>
</form>

<section>
    <?php if (count($result) > 0): ?>
            <div class="table-responsive">
                <table class="bookings-table table table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Event</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $num = 1 + $offset; ?>
                        <?php foreach ($result as $row): ?>
                                <tr>
                                    <td><?= $num++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['event_type']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            Ref: <?= htmlspecialchars($row['reference_number']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $date = htmlspecialchars($row['reservation_date']);
                                        $start = date('g:i A', strtotime($row['start_time']));
                                        $end = date('g:i A', strtotime($row['end_time']));
                                        echo "<strong>{$date}</strong><br>";
                                        echo "<small class='text-muted'>{$start} - {$end}</small><br>";
                                        echo "<small class='text-info'>" . getTimeDifferenceText($row['reservation_date']) . "</small>";
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= getStatusBadgeClass($row['status']) ?>">
                                            <?= getStatusDisplay($row['status']) ?>
                                        </span>
                                        <?php if ($row['status'] === 'cancelled_by_user' && $row['refund_status']): ?>
                                                <br>
                                                <small class="text-muted">
                                                    Refund: <?= ucfirst($row['refund_status']) ?>
                                                    <?php if ($row['refund_amount']): ?>
                                                            (₱<?= number_format($row['refund_amount'], 2) ?>)
                                                    <?php endif; ?>
                                                </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- View Details Button -->
                                        <button class="btn btn-info btn-sm toggle-details mb-1"
                                            data-target="#details-<?= $row['reference_number'] ?>">
                                            <i class="fas fa-eye"></i> Details
                                        </button>

                                        <!-- Action Buttons Based on Status -->
                                        <?php if (strtolower($row['status']) === 'cancelled_by_user'): ?>
                                                <a href="/NEW-PM-JI-RESERVIFY/pages/customer/rebook.php?reference_id=<?= htmlspecialchars($row['reference_id']) ?>"
                                                    class="btn btn-success btn-sm mb-1">
                                                    <i class="fas fa-redo"></i> Re-book
                                                </a>
                                        <?php elseif (canCancelBooking($row['status'], $row['reservation_date'])): ?>
                                                <button type="button" class="btn btn-danger btn-sm cancel-booking mb-1"
                                                    data-booking-id="<?= $row['id'] ?>"
                                                    data-reference-number="<?= htmlspecialchars($row['reference_number']) ?>"
                                                    data-event-type="<?= htmlspecialchars($row['event_type']) ?>"
                                                    data-event-date="<?= htmlspecialchars($row['reservation_date']) ?>"
                                                    data-event-time="<?= $start . ' - ' . $end ?>">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                        <?php elseif (in_array(strtolower($row['status']), ['confirmed', 'pending'])): ?>
                                                <button type="button" class="btn btn-secondary btn-sm mb-1" disabled
                                                    title="Cannot cancel - less than 24 hours to event">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                        <?php endif; ?>

                                        <!-- Hidden Details for Modal -->
                                        <div id="details-<?= $row['reference_number'] ?>" class="booking-details" style="display:none;"
                                            data-reference-id="<?= htmlspecialchars($row['reference_id']) ?>"
                                            data-event-type="<?= htmlspecialchars($row['event_type']) ?>"
                                            data-event-date="<?= htmlspecialchars($row['reservation_date']) ?>"
                                            data-start-time="<?= htmlspecialchars($row['start_time']) ?>"
                                            data-end-time="<?= htmlspecialchars($row['end_time']) ?>"
                                            data-location="<?= htmlspecialchars($row['full_address']) ?>"
                                            data-amount-paid="<?= htmlspecialchars($row['amount_paid'] ?? '0') ?>"
                                            data-balance="<?= htmlspecialchars($row['balance'] ?? '0') ?>"
                                            data-payment-method="<?= htmlspecialchars($row['payment_method'] ?? 'N/A') ?>"
                                            data-payment-type="<?= htmlspecialchars($row['payment_type'] ?? 'N/A') ?>"
                                            data-payment-status="<?= htmlspecialchars($row['payment_status'] ?? 'N/A') ?>"
                                            data-payment-date="<?= htmlspecialchars($row['payment_date'] ?? 'N/A') ?>"
                                            data-status="<?= htmlspecialchars($row['status']) ?>"
                                            data-duration="<?= htmlspecialchars($row['duration']) ?>"
                                            data-cancellation-reason="<?= htmlspecialchars($row['cancellation_reason'] ?? '') ?>"
                                            data-cancelled-at="<?= htmlspecialchars($row['cancelled_at'] ?? '') ?>"
                                            data-refund-status="<?= htmlspecialchars($row['refund_status'] ?? '') ?>"
                                            data-refund-amount="<?= htmlspecialchars($row['refund_amount'] ?? '0') ?>">
                                        </div>
                                    </td>
                                </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $totalPages = ceil($totalBookings / $limit);
            if ($totalPages > 1) {
                echo Pagination::render($totalBookings, $limit, $page, basename($_SERVER['PHP_SELF']));
            }
            ?>
    <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <p class="text-muted">No bookings found. Make a booking now!</p>
                <a href="/NEW-PM-JI-RESERVIFY/pages/customer/booking.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Make a Booking
                </a>
            </div>
    <?php endif; ?>
</section>

<!-- =========================================================================== -->
<!-- 12. Enhanced Cancellation Modal -->
<!-- =========================================================================== -->
<div class="modal fade" id="cancelBookingModal" tabindex="-1" role="dialog" aria-labelledby="cancelBookingModalLabel" aria-hidden="true">
    <div class="cancel-booking-modal-dialog modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancelBookingModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Cancel Booking
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-warning"></i>
                    <strong>Are you sure you want to cancel this booking?</strong>
                    <p class="mb-0 mt-2"><small>This action cannot be undone. Please review the details below before proceeding.</small></p>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Booking Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Event:</strong> <span id="cancel-event-type"></span></p>
                                <p><strong>Date:</strong> <span id="cancel-event-date"></span></p>
                                <p><strong>Time:</strong> <span id="cancel-event-time"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Reference:</strong> <span id="cancel-reference-number"></span></p>
                                <p><strong>Status:</strong> <span class="badge badge-info" id="cancel-status">Confirmed</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label for="cancellation-reason">
                        <i class="fas fa-comment"></i> Reason for cancellation <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="cancellation-reason" rows="3" required
                        placeholder="Please provide a reason for cancelling this booking..."></textarea>
                    <small class="form-text text-muted">This information helps us improve our services.</small>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i>
                    <strong>Cancellation Policy:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Cancellations must be made at least 24 hours before the event</li>
                        <li>Refunds will be processed according to our refund policy</li>
                        <li>Cancellation fees may apply as per terms and conditions</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-arrow-left"></i> Keep Booking
                </button>
                <button type="button" class="btn btn-danger" id="confirm-cancel-booking">
                    <i class="fas fa-times"></i> Cancel Booking
                </button>
            </div>
        </div>
    </div>
</div>

<div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;"></div>

<script src="/NEW-PM-JI-RESERVIFY/pages/customer/views/partials/booking-table/cancel-booking.js"></script>
