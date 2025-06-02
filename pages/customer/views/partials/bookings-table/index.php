<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_email'])) {
    header('Location: /NEW-PM-JI-RESERVIFY/public/index.php');
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

$limit = 3;
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
    p.payment_screenshot_path,
    p.payment_screenshot_thumbnail,
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

<form class="bookings-filter-form form-inline mb-3" method="get" action="">
    <input type="hidden" name="page" value="1">

    <!-- Search Input -->
    <div class="form-group mr-2">
        <input type="text" class="form-control" name="search" placeholder="Search"
            value="<?= htmlspecialchars($search) ?>">
    </div>

    <!-- Date Filter -->
    <div class="form-group mr-2">
        <input type="date" class="form-control" name="filter_date" value="<?= htmlspecialchars($filter_date) ?>">
    </div>

    <!-- Status Filter -->
    <div class="form-group mr-2">
        <select class="form-control" name="filter_status">
            <option value="">All</option>
            <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="confirmed" <?= $filter_status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="completed" <?= $filter_status === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="cancelled_by_user" <?= $filter_status === 'cancelled_by_user' ? 'selected' : '' ?>>Cancelled
            </option>
        </select>
    </div>

    <!-- Submit & Reset -->
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-search"></i> Filter
    </button>
    <?php if ($search || $filter_date || $filter_status): ?>
        <a href="?page=1" class="btn btn-secondary ml-2">
            <i class="fas fa-undo"></i> Reset
        </a>
    <?php endif; ?>
</form>

<section>
    <?php if (count($result) > 0): ?>
        <div class="bookings-table-container">
            <div class="table-responsive">
                <table class="bookings-table table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Event Details</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th class="actions-column">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $num = 1 + $offset; ?>
                        <?php foreach ($result as $row): ?>
                            <tr>
                                <td><?= $num++ ?></td>
                                <td class="event-info">
                                    <strong><?= htmlspecialchars($row['event_type']) ?></strong>
                                    <small class="text-muted d-block">
                                         <?= htmlspecialchars($row['reference_id']) ?>
                                    </small>
                                </td>
                                <td class="datetime-info">
                                    <?php
                                    $date = htmlspecialchars($row['reservation_date']);
                                    $start = date('g:i A', strtotime($row['start_time']));
                                    $end = date('g:i A', strtotime($row['end_time']));
                                    echo "<strong>{$date}</strong>";
                                    echo "<small class='text-muted d-block'>{$start} - {$end}</small>";
                                    echo "<small class='text-info d-block'>" . getTimeDifferenceText($row['reservation_date']) . "</small>";
                                    ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= getStatusBadgeClass($row['status']) ?>">
                                        <?= getStatusDisplay($row['status']) ?>
                                    </span>
                                    <?php if ($row['status'] === 'cancelled_by_user' && $row['refund_status']): ?>
                                        <small class="text-muted d-block mt-1">
                                            Refund: <?= ucfirst($row['refund_status']) ?>
                                            <?php if ($row['refund_amount']): ?>
                                                (₱<?= number_format($row['refund_amount'], 2) ?>)
                                            <?php endif; ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-column">
                                    <!-- View Details Button - Fixed to use unique ID -->
                                    <button class="btn btn-info btn-sm toggle-details"
                                        data-target="#details-<?= $row['id'] ?>">
                                        <i class="fas fa-eye"></i> Details
                                    </button>

                                    <!-- Action Buttons Based on Status -->
                                    <?php if (strtolower($row['status']) === 'cancelled_by_user'): ?>
                                        <a href="/NEW-PM-JI-RESERVIFY/pages/customer/rebook.php?reference_id=<?= htmlspecialchars($row['reference_id']) ?>"
                                            class="btn btn-success btn-sm">
                                            <i class="fas fa-redo"></i> Re-book
                                        </a>
                                    <?php elseif (canCancelBooking($row['status'], $row['reservation_date'])): ?>
                                        <button type="button" class="btn btn-danger btn-sm cancel-booking"
                                            data-booking-id="<?= $row['id'] ?>"
                                            data-reference-number="<?= htmlspecialchars($row['reference_number']) ?>"
                                            data-event-type="<?= htmlspecialchars($row['event_type']) ?>"
                                            data-event-date="<?= htmlspecialchars($row['reservation_date']) ?>"
                                            data-event-time="<?= $start . ' - ' . $end ?>">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    <?php elseif (in_array(strtolower($row['status']), ['confirmed', 'pending'])): ?>
                                        <button type="button" class="btn btn-secondary btn-sm" disabled
                                            title="Cannot cancel - less than 24 hours to event">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    <?php endif; ?>

                                    <!-- Hidden Details for Modal - Fixed ID and Data Attributes -->
                                    <div id="details-<?= $row['id'] ?>" class="booking-details" style="display:none;"
                                        data-reference-id="<?= htmlspecialchars($row['reference_id'] ?? '') ?>"
                                        data-event-type="<?= htmlspecialchars($row['event_type'] ?? '') ?>"
                                        data-event-date="<?= htmlspecialchars($row['reservation_date'] ?? '') ?>"
                                        data-start-time="<?= htmlspecialchars($row['start_time'] ?? '') ?>"
                                        data-end-time="<?= htmlspecialchars($row['end_time'] ?? '') ?>"
                                        data-location="<?= htmlspecialchars($row['full_address'] ?? '') ?>"
                                        data-amount-paid="<?= htmlspecialchars($row['amount_paid'] ?? '0') ?>"
                                        data-balance="<?= htmlspecialchars($row['balance'] ?? '0') ?>"
                                        data-payment-method="<?= htmlspecialchars($row['payment_method'] ?? '') ?>"
                                        data-payment-type="<?= htmlspecialchars($row['payment_type'] ?? '') ?>"
                                        data-payment-status="<?= htmlspecialchars($row['payment_status'] ?? 'pending') ?>"
                                        data-payment-date="<?= htmlspecialchars($row['payment_date'] ?? '') ?>"
                                        data-payment-screenshot="<?= htmlspecialchars($row['payment_screenshot_path'] ?? '') ?>"
                                        data-payment-screenshot-thumbnail="<?= htmlspecialchars($row['payment_screenshot_thumbnail'] ?? '') ?>"
                                        data-status="<?= htmlspecialchars($row['status'] ?? '') ?>"
                                        data-duration="<?= htmlspecialchars($row['duration'] ?? '') ?>"
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
        </div>

        <?php
        $totalPages = ceil($totalBookings / $limit);
        if ($totalPages > 1) {
            echo Pagination::render($totalBookings, $limit, $page, basename($_SERVER['PHP_SELF']));
        }
        ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times fa-3x"></i>
            <p>No bookings found. Make a booking now!</p>
            <a href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/index.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Make a Booking
            </a>
        </div>
    <?php endif; ?>
</section>

<!-- Booking Details Modal -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/views/partials/booking-details-modal/index.php'; ?>

<!-- Cancellation Modal -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/customer/views/partials/cancellation-modal/index.php'; ?>

<div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;"></div>

<script src="/NEW-PM-JI-RESERVIFY/pages/customer/views/actions/view-booking-details.js"></script>
<script src="/NEW-PM-JI-RESERVIFY/pages/customer/views/actions/cancel-booking.js"></script>