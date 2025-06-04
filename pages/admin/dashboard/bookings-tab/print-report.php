<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: /NEW-PM-JI-RESERVIFY/pages/admin/index.php");
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

$admin_username = $_SESSION['admin_username'];

// Get PDO connection from your Database class
$pdo = Database::getConnection();

// Get filter parameters
$printDateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$printDateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$includePending = isset($_GET['include_pending']);
$includeApproved = isset($_GET['include_approved']);
$includeHistory = isset($_GET['include_history']);

// Build WHERE clause for date filtering
$printDateWhere = '';
$printParams = [];

if (!empty($printDateFrom)) {
    $printDateWhere .= " AND b.reservation_date >= :date_from";
    $printParams['date_from'] = $printDateFrom;
}

if (!empty($printDateTo)) {
    $printDateWhere .= " AND b.reservation_date <= :date_to";
    $printParams['date_to'] = $printDateTo;
}

// Build status filter
$statusConditions = [];
if ($includePending)
    $statusConditions[] = "'pending'";
if ($includeApproved)
    $statusConditions[] = "'approved'";
if ($includeHistory)
    $statusConditions[] = "'completed', 'cancelled', 'no_show'";

$statusWhere = '';
if (!empty($statusConditions)) {
    $statusWhere = " AND b.status IN (" . implode(', ', $statusConditions) . ")";
}

// Fetch all bookings for report
$stmtReport = $pdo->prepare("
    SELECT 
        b.*,
        u.first_name,
        u.last_name,
        u.email,
        u.contact_no as phone,
        p.amount_paid,
        p.balance,
        p.payment_method,
        p.payment_type,
        p.status as payment_status,
        p.payment_date,
        p.refund_amount,
        p.refund_date
    FROM tbl_bookings b
    LEFT JOIN tbl_users u ON b.user_id = u.id
    LEFT JOIN tbl_payments p ON b.id = p.booking_id
    WHERE 1=1 $printDateWhere $statusWhere
    ORDER BY b.reservation_date ASC, b.start_time ASC
");

foreach ($printParams as $key => $value) {
    $stmtReport->bindValue(":$key", $value);
}
$stmtReport->execute();
$reportBookings = $stmtReport->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Report - <?= date('Y-m-d') ?></title>
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 0.5in;
            }

            body {
                font-size: 10px;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .report-table {
                page-break-inside: auto;
            }

            .report-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .report-header {
                page-break-after: avoid;
            }
        }

        @media screen {
            body {
                background-color: #f8f9fa;
                padding: 20px;
            }

            .report-container {
                background-color: white;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                border-radius: 8px;
                overflow: hidden;
            }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.4;
        }

        .report-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 30px;
        }

        .report-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
        }

        .report-title {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .report-subtitle {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .report-filters {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #007bff;
        }

        .filter-title {
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .filter-item {
            margin-bottom: 8px;
            color: #495057;
        }

        .print-actions {
            margin-bottom: 20px;
            text-align: center;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 8px;
        }

        .btn {
            padding: 10px 20px;
            margin: 0 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #545b62;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            background-color: white;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            text-align: left;
            vertical-align: top;
        }

        .report-table th {
            background-color: #007bff;
            color: white;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-table td {
            font-size: 10px;
        }

        .report-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .report-table tbody tr:hover {
            background-color: #e3f2fd;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-completed {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-no_show {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        .report-summary {
            margin-top: 25px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .summary-title {
            font-size: 16px;
            font-weight: bold;
            color: #495057;
            margin-bottom: 15px;
            text-align: center;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .summary-item {
            background-color: white;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .summary-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #dee2e6;
        }

        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .report-container {
                padding: 15px;
            }

            .report-table {
                font-size: 9px;
            }

            .report-table th,
            .report-table td {
                padding: 6px 4px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="report-container">
        <div class="print-actions no-print">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Report
            </button>
            <button class="btn btn-secondary" onclick="window.close()">
                <i class="fas fa-times me-1"></i> Close Window
            </button>
        </div>

        <div class="report-header">
            <div class="report-title">RESERVIFY</div>
            <div style="font-size: 18px; color: #495057; margin-bottom: 10px;">Booking Management Report</div>
            <div class="report-subtitle">Generated on <?= date('F d, Y \a\t g:i A') ?></div>
            <div class="report-subtitle">Administrator: <?= htmlspecialchars($admin_username) ?></div>
        </div>

        <div class="report-filters">
            <div class="filter-title">Report Configuration</div>
            <div class="filter-item">
                <strong>Date Range:</strong>
                <?php if (!empty($printDateFrom) || !empty($printDateTo)): ?>
                    <?= !empty($printDateFrom) ? date('M d, Y', strtotime($printDateFrom)) : 'Beginning' ?> -
                    <?= !empty($printDateTo) ? date('M d, Y', strtotime($printDateTo)) : 'Present' ?>
                <?php else: ?>
                    All Records
                <?php endif; ?>
            </div>
            <div class="filter-item">
                <strong>Status Filter:</strong>
                <?php
                $statusFilters = [];
                if ($includePending)
                    $statusFilters[] = 'Pending';
                if ($includeApproved)
                    $statusFilters[] = 'Approved';
                if ($includeHistory)
                    $statusFilters[] = 'Completed/Cancelled/No Show';
                echo !empty($statusFilters) ? implode(', ', $statusFilters) : 'All Status';
                ?>
            </div>
        </div>

        <?php if (empty($reportBookings)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>No bookings found</h3>
                <p>No bookings match the selected criteria for this report.</p>
            </div>
        <?php else: ?>
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Booking ID</th>
                        <th style="width: 100px;">Date</th>
                        <th style="width: 120px;">Time</th>
                        <th style="width: 150px;">Customer</th>
                        <th style="width: 120px;">Contact</th>
                        <th style="width: 100px;">Event Type</th>
                        <th style="width: 100px;">Venue</th>
                        <th style="width: 60px;">Guests</th>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 100px;">Amount</th>
                        <th style="width: 100px;">Payment</th>
                        <th style="width: 120px;">Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportBookings as $booking): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($booking['id']) ?></strong></td>
                            <td><?= date('M d, Y', strtotime($booking['reservation_date'])) ?></td>
                            <td>
                                <?= date('g:i A', strtotime($booking['start_time'])) ?><br>
                                <small style="color: #6c757d;">to <?= date('g:i A', strtotime($booking['end_time'])) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></strong>
                            </td>
                            <td>
                                <?= htmlspecialchars($booking['phone']) ?><br>
                                <small style="color: #6c757d;"><?= htmlspecialchars($booking['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($booking['event_type']) ?></td>
                            <td><?= htmlspecialchars($booking['venue']) ?></td>
                            <td style="text-align: center;"><?= htmlspecialchars($booking['number_of_guests']) ?></td>
                            <td>
                                <span class="status-badge status-<?= $booking['status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <strong>₱<?= number_format($booking['total_amount'], 2) ?></strong>
                                <?php if ($booking['balance'] > 0): ?>
                                    <br><small style="color: #dc3545;">Bal: ₱<?= number_format($booking['balance'], 2) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($booking['payment_status']): ?>
                                    <span style="color: #28a745;"><?= ucfirst($booking['payment_status']) ?></span><br>
                                    <small><?= htmlspecialchars($booking['payment_method']) ?></small>
                                <?php else: ?>
                                    <span style="color: #dc3545;">No Payment</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($booking['created_at'])) ?><br>
                                <small style="color: #6c757d;"><?= date('g:i A', strtotime($booking['created_at'])) ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="report-summary">
                <div class="summary-title">Report Summary</div>
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-label">Total Bookings</div>
                        <div class="summary-value"><?= count($reportBookings) ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Total Revenue</div>
                        <div class="summary-value">
                            ₱<?= number_format(array_sum(array_column($reportBookings, 'total_amount')), 2) ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Pending</div>
                        <div class="summary-value">
                            <?= count(array_filter($reportBookings, fn($b) => $b['status'] === 'pending')) ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Approved</div>
                        <div class="summary-value">
                            <?= count(array_filter($reportBookings, fn($b) => $b['status'] === 'approved')) ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Completed</div>
                        <div class="summary-value">
                            <?= count(array_filter($reportBookings, fn($b) => $b['status'] === 'completed')) ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Cancelled</div>
                        <div class="summary-value">
                            <?= count(array_filter($reportBookings, fn($b) => $b['status'] === 'cancelled')) ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-focus window for better printing experience
        window.focus();

        // Optional: Auto-print after a short delay
        // Uncomment the following lines if you want auto-print
        /*
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        });
        */

        // Handle print button click
        document.addEventListener('keydown', function (e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>

</html>