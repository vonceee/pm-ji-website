<header>
    <h4>Dashboard</h4>
</header>

<!-- At-a-Glance Overview -->
<div class="dashboard-cards">
    <div class="dashboard-card">
        <h2><?php echo $upcomingCount; ?></h2>
        <div class="desc">Upcoming Bookings <br>(7 days)</div>
        <a href="/NEW-PM-JI-RESERVIFY/pages/admin/calendar.php">View Calendar</a>
    </div>
    <div class="dashboard-card">
        <h2><?php echo $pendingCount; ?></h2>
        <div class="desc">Pending Approvals</div>
        <a href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/">Go to Bookings</a>
    </div>
    <div class="dashboard-card">
        <h2>₱<?php echo number_format($thisMonthRevenue, 2); ?></h2>
        <div class="desc">Revenue (This Month)</div>
    </div>
    <div class="dashboard-card">
        <h2>₱<?php echo number_format($lastMonthRevenue, 2); ?></h2>
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