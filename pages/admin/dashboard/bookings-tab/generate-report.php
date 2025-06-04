<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

$pdo = Database::getConnection();

$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$dateWhere = '';
$params = [];

if (!empty($dateFrom)) {
    $dateWhere .= " AND b.reservation_date >= :date_from";
    $params['date_from'] = $dateFrom;
}

if (!empty($dateTo)) {
    $dateWhere .= " AND b.reservation_date <= :date_to";
    $params['date_to'] = $dateTo;
}

// Fetch pending and approved bookings
$stmt = $pdo->prepare("
    SELECT 
        b.*,
        u.first_name,
        u.last_name,
        u.email,
        u.contact_no as phone,
        p.amount_paid,
        p.balance,
        p.payment_method
    FROM tbl_bookings b
    LEFT JOIN tbl_users u ON b.user_id = u.id
    LEFT JOIN tbl_payments p ON b.id = p.booking_id
    WHERE b.status IN ('pending', 'approved') $dateWhere
    ORDER BY b.status, b.reservation_date ASC, b.start_time ASC
");

foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate bookings by status
$pendingBookings = array_filter($bookings, fn($b) => $b['status'] === 'pending');
$approvedBookings = array_filter($bookings, fn($b) => $b['status'] === 'approved');

function renderBookingsTable($bookings, $title)
{
    if (empty($bookings)) {
        echo "<div class='section-title'>$title</div>";
        echo "<p>No bookings found for this period.</p>";
        return;
    }

    echo "<div class='section-title'>$title (" . count($bookings) . ")</div>";
    echo "<table>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Reference ID</th>";
    echo "<th>Customer</th>";
    echo "<th>Email</th>";
    echo "<th>Phone</th>";
    echo "<th>Event Type</th>";
    echo "<th>Date</th>";
    echo "<th>Status</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    foreach ($bookings as $booking) {
        $statusClass = $booking['status'] === 'pending' ? 'status-pending' : 'status-approved';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($booking['reference_id']) . "</td>";
        echo "<td>" . htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($booking['email']) . "</td>";
        echo "<td>" . htmlspecialchars($booking['phone']) . "</td>";
        echo "<td>" . htmlspecialchars($booking['event_type']) . "</td>";
        echo "<td>" . date('M d, Y', strtotime($booking['reservation_date'])) . "</td>";
        echo "<td class='$statusClass'>" . ucfirst($booking['status']) . "</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
}

// Render the report
renderBookingsTable($pendingBookings, 'Pending Bookings');
renderBookingsTable($approvedBookings, 'Approved Bookings');

// Summary
$totalPending = count($pendingBookings);
$totalApproved = count($approvedBookings);
$totalBookings = $totalPending + $totalApproved;

echo "<div class='section-title'>Summary</div>";
echo "<table style='width: 300px;'>";
echo "<tr><td><strong>Total Pending:</strong></td><td>$totalPending</td></tr>";
echo "<tr><td><strong>Total Approved:</strong></td><td>$totalApproved</td></tr>";
echo "<tr><td><strong>Total Bookings:</strong></td><td>$totalBookings</td></tr>";
echo "</table>";
?>