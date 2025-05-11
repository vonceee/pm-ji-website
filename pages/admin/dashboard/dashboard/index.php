<header>
    <h4>Dashboard</h4>
</header>

<!-- At-a-Glance Overview -->
<div class="dashboard-cards">
    <div class="dashboard-card">
        <h2><?= $upcomingCount ?></h2>
        <div class="desc">Upcoming (7 days)</div>
    </div>
    <div class="dashboard-card">
        <h2><?= $pendingCount ?></h2>
        <div class="desc">Pending Approvals</div>
    </div>
    <div class="dashboard-card">
        <h2>₱<?= number_format($thisMonthRevenue, 2) ?></h2>
        <div class="desc">Revenue (This Month)</div>
    </div>
    <div class="dashboard-card">
        <h2>₱<?= number_format($lastMonthRevenue, 2) ?></h2>
        <div class="desc">Revenue (Last Month)</div>
    </div>
</div>

<!-- Revenue Snapshot -->
<div class="dashboard-section">
    <h4>Revenue Snapshot</h4>
    <ul>
        <li><strong>This Month:</strong> ₱<?php echo number_format($thisMonthRevenue, 2); ?></li>
        <li><strong>Last Month:</strong> ₱<?php echo number_format($lastMonthRevenue, 2); ?></li>
        <li><strong>Year-to-date:</strong> ₱<?php echo number_format($ytdRevenue, 2); ?></li>
        <li><strong>Refunds/Cancellations (This Month):</strong> <?php echo $refundCount; ?></li>
    </ul>
</div>

<!-- Alerts & Tasks -->
<div class="dashboard-section dashboard-alerts">
    <h4>Alerts & Tasks</h4>
    <ul>
        <li><strong>Late Payments:</strong> <?php echo $latePayments; ?></li>
        <li><strong>Contracts Pending Signature:</strong> <?php echo $contractsPending; ?></li>
        <li><strong>Photographer Assignments Unconfirmed:</strong>
            <?php echo $assignmentsUnconfirmed; ?></li>
    </ul>
</div>