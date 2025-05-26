<?php
// pages/admin/reports/index.php

// database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
use Config\Database;
$pdo = Database::getConnection();

// report service class
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/models/ReportGenerator.php';
$reportGenerator = new \Models\ReportGenerator($pdo);

// get parameters
$reportType = $_GET['type'] ?? 'booking_summary';
$startDate = $_GET['start'] ?? date('Y-m-01');
$endDate = $_GET['end'] ?? date('Y-m-t');
$status = $_GET['status'] ?? '';
$eventType = $_GET['event_type'] ?? '';
$paymentStatus = $_GET['payment_status'] ?? '';

// generate report data based on type
$reportData = [];
$reportTitle = '';
$reportDescription = '';

switch ($reportType) {
    case 'booking_summary':
        $reportData = $reportGenerator->getBookingSummaryReport($startDate, $endDate, $status, $eventType);
        $reportTitle = 'Booking Summary Report';
        $reportDescription = 'Overview of all bookings for the selected period';
        break;
    case 'revenue_report':
        $reportData = $reportGenerator->getRevenueReport($startDate, $endDate, $eventType);
        $reportTitle = 'Revenue Report';
        $reportDescription = 'Financial overview and revenue breakdown';
        break;
    case 'payment_report':
        $reportData = $reportGenerator->getPaymentReport($startDate, $endDate, $paymentStatus);
        $reportTitle = 'Payment Status Report';
        $reportDescription = 'Detailed payment tracking and outstanding amounts';
        break;
    case 'event_analysis':
        $reportData = $reportGenerator->getEventAnalysisReport($startDate, $endDate);
        $reportTitle = 'Event Type Analysis';
        $reportDescription = 'Performance analysis by event type';
        break;
}

// get filter options
$eventTypes = $reportGenerator->getEventTypes();
$statuses = ['pending', 'approved', 'confirmed', 'cancelled', 'completed'];
$paymentStatuses = ['pending', 'paid', 'partial', 'refunded'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Reports - Admin Panel</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/reports/reports.css">
</head>

<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
            <div>
                <h2 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h2>
                <small class="text-muted">Generate and print comprehensive booking reports</small>
            </div>
            <div>
                <button type="button" class="btn btn-primary" onclick="printTableOnly()">
                    <i class="fas fa-print me-2"></i>Print Report
                </button>
                <button type="button" class="btn btn-success" onclick="exportToPDF()">
                    <i class="fas fa-file-pdf me-2"></i>Export PDF
                </button>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="report-filters mt-4 mb-4 no-print">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Report Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" id="reportForm" action="?view=reports">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Report Type</label>
                                <select name="type" class="form-select" onchange="updateReportType()">
                                    <option value="booking_summary" <?= $reportType === 'booking_summary' ? 'selected' : '' ?>>Booking Summary</option>
                                    <option value="revenue_report" <?= $reportType === 'revenue_report' ? 'selected' : '' ?>>Revenue Report</option>
                                    <option value="payment_report" <?= $reportType === 'payment_report' ? 'selected' : '' ?>>Payment Report</option>
                                    <option value="event_analysis" <?= $reportType === 'event_analysis' ? 'selected' : '' ?>>Event Analysis</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date Range</label>
                                <input type="text" id="dateRange" name="dateRange" class="form-control"
                                    data-start="<?= $startDate ?>" data-end="<?= $endDate ?>">
                                <input type="hidden" name="start" value="<?= $startDate ?>">
                                <input type="hidden" name="end" value="<?= $endDate ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <?php foreach ($statuses as $statusOption): ?>
                                        <option value="<?= $statusOption ?>" <?= $status === $statusOption ? 'selected' : '' ?>>
                                            <?= ucfirst($statusOption) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Event Type</label>
                                <select name="event_type" class="form-select">
                                    <option value="">All Events</option>
                                    <?php foreach ($eventTypes as $type): ?>
                                        <option value="<?= $type['event_type'] ?>" <?= $eventType === $type['event_type'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($type['event_type']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status" class="form-select">
                                    <option value="">All Payments</option>
                                    <?php foreach ($paymentStatuses as $paymentOption): ?>
                                        <option value="<?= $paymentOption ?>" <?= $paymentStatus === $paymentOption ? 'selected' : '' ?>>
                                            <?= ucfirst($paymentOption) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Generate Report
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                    <i class="fas fa-undo me-2"></i>Reset Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Report Header -->
        <div class="report-header">
            <div class="text-center mb-4">
                <h1 class="report-title"><?= $reportTitle ?></h1>
                <p class="report-description"><?= $reportDescription ?></p>
                <div class="report-meta">
                    <strong>Period:</strong> <?= date('M d, Y', strtotime($startDate)) ?> -
                    <?= date('M d, Y', strtotime($endDate)) ?> |
                    <strong>Generated:</strong> <?= date('M d, Y g:i A') ?>
                </div>
            </div>
        </div>

        <!-- Report Content -->
        <div class="report-content">
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/reports/report-templates/' . $reportType . '.php'; ?>
        </div>

        <!-- Report Footer -->
        <div class="report-footer mt-5 pt-3 border-top">
            <div class="row">
                <div class="col-6">
                    <small class="text-muted">
                        Generated by: Admin Panel<br>
                        Report ID: RPT-<?= date('Ymd-His') ?>
                    </small>
                </div>
                <div class="col-6 text-end">
                    <small class="text-muted">
                        Page 1 of 1<br>
                        Confidential - Internal Use Only
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/reports/reports.js"></script>
</body>

</html>