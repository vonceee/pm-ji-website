<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

$pdo = Database::getConnection();

$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$dateWhere = '';
$params = [];

if (!empty($dateFrom)) {
    $dateWhere .= " AND p.payment_date >= :date_from";
    $params['date_from'] = $dateFrom;
}

if (!empty($dateTo)) {
    $dateWhere .= " AND p.payment_date <= :date_to";
    $params['date_to'] = $dateTo;
}

// Fetch outstanding payments with user and booking info
$stmtOutstanding = $pdo->prepare("
    SELECT 
        p.*,
        (p.amount_paid + p.balance) AS total_amount,
        b.reference_id,
        b.event_type,
        b.reservation_date,
        u.first_name,
        u.last_name,
        u.email,
        u.contact_no as phone
    FROM tbl_payments p
    LEFT JOIN tbl_bookings b ON p.booking_id = b.id
    LEFT JOIN tbl_users u ON b.user_id = u.id
    WHERE p.status = 'partial' AND p.balance > 0 $dateWhere
    ORDER BY p.payment_date ASC
");

foreach ($params as $key => $value) {
    $stmtOutstanding->bindValue(":$key", $value);
}
$stmtOutstanding->execute();
$outstandingPayments = $stmtOutstanding->fetchAll(PDO::FETCH_ASSOC);

// Fetch completed payments
$stmtCompleted = $pdo->prepare("
    SELECT 
        p.*,
        (p.amount_paid + p.balance) AS total_amount,
        b.reference_id,
        b.event_type,
        b.reservation_date,
        u.first_name,
        u.last_name,
        u.email,
        u.contact_no as phone
    FROM tbl_payments p
    LEFT JOIN tbl_bookings b ON p.booking_id = b.id
    LEFT JOIN tbl_users u ON b.user_id = u.id
    WHERE p.status = 'paid' $dateWhere
    ORDER BY p.payment_date ASC
");

foreach ($params as $key => $value) {
    $stmtCompleted->bindValue(":$key", $value);
}
$stmtCompleted->execute();
$completedPayments = $stmtCompleted->fetchAll(PDO::FETCH_ASSOC);

// Fetch refund payments
$stmtRefunds = $pdo->prepare("
    SELECT 
        p.*,
        (p.amount_paid + p.balance) AS total_amount,
        b.reference_id,
        b.event_type,
        b.reservation_date,
        u.first_name,
        u.last_name,
        u.email,
        u.contact_no as phone
    FROM tbl_payments p
    LEFT JOIN tbl_bookings b ON p.booking_id = b.id
    LEFT JOIN tbl_users u ON b.user_id = u.id
    WHERE p.refund_amount > 0 $dateWhere
    ORDER BY p.refund_date ASC
");

foreach ($params as $key => $value) {
    $stmtRefunds->bindValue(":$key", $value);
}
$stmtRefunds->execute();
$refundPayments = $stmtRefunds->fetchAll(PDO::FETCH_ASSOC);

function renderPaymentsTable($payments, $title, $type = 'payment')
{
    if (empty($payments)) {
        echo "<div class='section-title'>$title</div>";
        echo "<p>No payments found for this period.</p>";
        return;
    }

    echo "<div class='section-title'>$title (" . count($payments) . ")</div>";
    echo "<table>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Reference ID</th>";
    echo "<th>Customer</th>";
    echo "<th>Event Type</th>";
    echo "<th>Event Date</th>";

    if ($type === 'outstanding') {
        echo "<th>Amount Paid</th>";
        echo "<th>Balance</th>";
    } elseif ($type === 'refund') {
        echo "<th>Original Amount</th>";
        echo "<th>Refund Amount</th>";
        echo "<th>Refund Date</th>";
    } else {
        echo "<th>Total Amount</th>";
        echo "<th>Payment Date</th>";
    }

    echo "<th>Payment Method</th>";
    echo "<th>Status</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    foreach ($payments as $payment) {
        $statusClass = '';
        switch ($payment['status']) {
            case 'partial':
                $statusClass = 'status-partial';
                break;
            case 'paid':
                $statusClass = 'status-paid';
                break;
            default:
                $statusClass = 'status-pending';
        }

        echo "<tr>";
        echo "<td>" . htmlspecialchars($payment['reference_id']) . "</td>";
        echo "<td>" . htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($payment['event_type']) . "</td>";
        echo "<td>" . date('M d, Y', strtotime($payment['reservation_date'])) . "</td>";

        if ($type === 'outstanding') {
            echo "<td>₱" . number_format($payment['amount_paid'], 2) . "</td>";
            echo "<td>₱" . number_format($payment['balance'], 2) . "</td>";
        } elseif ($type === 'refund') {
            echo "<td>₱" . number_format($payment['total_amount'], 2) . "</td>";
            echo "<td>₱" . number_format($payment['refund_amount'], 2) . "</td>";
            echo "<td>" . ($payment['refund_date'] ? date('M d, Y', strtotime($payment['refund_date'])) : 'Pending') . "</td>";
        } else {
            echo "<td>₱" . number_format($payment['total_amount'], 2) . "</td>";
            echo "<td>" . date('M d, Y', strtotime($payment['payment_date'])) . "</td>";
        }

        echo "<td>" . ucfirst($payment['payment_method']) . "</td>";
        echo "<td class='$statusClass'>" . ucfirst($payment['status']) . "</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
}

// Render the report
renderPaymentsTable($outstandingPayments, 'Outstanding Payments', 'outstanding');
renderPaymentsTable($completedPayments, 'Completed Payments', 'completed');
renderPaymentsTable($refundPayments, 'Payment Refunds', 'refund');

// Summary calculations
$totalOutstanding = count($outstandingPayments);
$totalCompleted = count($completedPayments);
$totalRefunds = count($refundPayments);
$totalPayments = $totalOutstanding + $totalCompleted;

$totalOutstandingAmount = array_sum(array_column($outstandingPayments, 'balance'));
$totalCompletedAmount = array_sum(array_column($completedPayments, 'total_amount'));
$totalRefundAmount = array_sum(array_column($refundPayments, 'refund_amount'));

echo "<div class='section-title'>Payment Summary</div>";
echo "<table style='width: 400px;'>";
echo "<tr><td><strong>Total Outstanding Payments:</strong></td><td>$totalOutstanding</td></tr>";
echo "<tr><td><strong>Total Completed Payments:</strong></td><td>$totalCompleted</td></tr>";
echo "<tr><td><strong>Total Refund Requests:</strong></td><td>$totalRefunds</td></tr>";
echo "<tr><td><strong>Total Payment Records:</strong></td><td>$totalPayments</td></tr>";
echo "</table>";

echo "<div class='section-title'>Financial Summary</div>";
echo "<table style='width: 400px;'>";
echo "<tr><td><strong>Outstanding Balance:</strong></td><td>₱" . number_format($totalOutstandingAmount, 2) . "</td></tr>";
echo "<tr><td><strong>Total Completed Amount:</strong></td><td>₱" . number_format($totalCompletedAmount, 2) . "</td></tr>";
echo "<tr><td><strong>Total Refund Amount:</strong></td><td>₱" . number_format($totalRefundAmount, 2) . "</td></tr>";
echo "</table>";
?>