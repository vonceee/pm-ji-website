<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

// Get database connection using the existing config
try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch bookings with user information
$sql = "SELECT 
            b.id,
            b.reference_id,
            b.event_type,
            b.duration,
            b.reservation_date,
            b.start_time,
            b.end_time,
            b.full_address,
            b.reference_number,
            b.status,
            b.created_at,
            u.first_name,
            u.last_name,
            u.email,
            u.contact_no
        FROM tbl_bookings b
        LEFT JOIN tbl_users u ON b.user_id = u.id
        ORDER BY b.reservation_date ASC, b.start_time ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get upcoming bookings (today and future)
$today = date('Y-m-d');
$upcomingBookings = array_filter($bookings, function ($booking) use ($today) {
    return $booking['reservation_date'] >= $today;
});

// Convert bookings to JavaScript format
$bookingsJson = json_encode(array_map(function ($booking) {
    return [
        'id' => $booking['id'],
        'reference_id' => $booking['reference_id'],
        'date' => $booking['reservation_date'],
        'customer' => trim(($booking['first_name'] ?? '') . ' ' . ($booking['last_name'] ?? '')),
        'service' => $booking['event_type'],
        'status' => $booking['status'],
        'time' => $booking['start_time'],
        'end_time' => $booking['end_time'],
        'duration' => $booking['duration'],
        'address' => $booking['full_address'],
        'reference_number' => $booking['reference_number'],
        'email' => $booking['email'] ?? '',
        'phone' => $booking['contact_no'] ?? '',
        'created_at' => $booking['created_at']
    ];
}, $bookings));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Calendar</title>
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/calendar-tab/calendar.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
</head>

<body>
    <div class="calendar-container">
        <div class="calendar-header">
            <h1>📅 Booking Calendar</h1>
            <div class="calendar-controls">
                <button class="nav-btn" onclick="previousMonth()">‹</button>
                <div class="current-month" id="currentMonth"></div>
                <button class="nav-btn" onclick="nextMonth()">›</button>
            </div>
        </div>

        <div class="calendar-grid">
            <div class="day-header">Sun</div>
            <div class="day-header">Mon</div>
            <div class="day-header">Tue</div>
            <div class="day-header">Wed</div>
            <div class="day-header">Thu</div>
            <div class="day-header">Fri</div>
            <div class="day-header">Sat</div>
            <div id="calendarDays"></div>
        </div>

        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #ff9800;"></div>
                <span>Pending</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #4caf50;"></div>
                <span>Confirmed</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #f44336;"></div>
                <span>Cancelled</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #2196f3;"></div>
                <span>Completed</span>
            </div>
        </div>
    </div>

    <!-- Upcoming Bookings Table -->
    <div class="upcoming-bookings-container">
        <div class="upcoming-header">
            <h2>📋 Upcoming Bookings</h2>
            <button class="download-btn" onclick="downloadPDF()">📄 Download PDF</button>
        </div>

        <div class="table-container">
            <table class="upcoming-table" id="upcomingTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Reference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($upcomingBookings)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #6b7280; padding: 20px;">
                                No upcoming bookings
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($upcomingBookings as $booking): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($booking['reservation_date'])); ?></td>
                                <td><?php echo $booking['start_time'] . ' - ' . $booking['end_time']; ?></td>
                                <td><?php echo trim(($booking['first_name'] ?? '') . ' ' . ($booking['last_name'] ?? '')); ?>
                                </td>
                                <td><?php echo $booking['event_type']; ?></td>
                                <td><?php echo $booking['reference_number']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="booking-modal" id="bookingModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Booking Details</div>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="booking-details" id="bookingDetails"></div>
        </div>
    </div>

    <script>
        // Pass PHP data to JavaScript
        const databaseBookings = <?php echo $bookingsJson; ?>;
        console.log('Processed bookings for calendar:', databaseBookings);

        // PDF Download function
        function downloadPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Add title
            doc.setFontSize(20);
            doc.text('Upcoming Bookings Report', 20, 20);

            // Add generated date
            doc.setFontSize(12);
            doc.text('Generated on: ' + new Date().toLocaleDateString(), 20, 35);

            // Get table data
            const table = document.getElementById('upcomingTable');
            const rows = [];

            // Get table rows (skip if no data message)
            const tableRows = table.querySelectorAll('tbody tr');
            if (tableRows.length === 1 && tableRows[0].cells.length === 1) {
                // No bookings message
                doc.setFontSize(14);
                doc.text('No upcoming bookings found.', 20, 60);
            } else {
                // Extract table data
                tableRows.forEach(row => {
                    const rowData = [];
                    row.querySelectorAll('td').forEach(cell => {
                        // Get text content, handling status badges
                        const statusBadge = cell.querySelector('.status-badge');
                        if (statusBadge) {
                            rowData.push(statusBadge.textContent.trim());
                        } else {
                            rowData.push(cell.textContent.trim());
                        }
                    });
                    if (rowData.length > 0) rows.push(rowData);
                });

                // Create PDF table
                doc.autoTable({
                    head: [['Date', 'Time', 'Customer', 'Service', 'Reference', 'Status']],
                    body: rows,
                    startY: 50,
                    styles: {
                        fontSize: 10,
                        cellPadding: 3,
                    },
                    headStyles: {
                        fillColor: [59, 130, 246],
                        textColor: 255,
                        fontStyle: 'bold'
                    },
                    alternateRowStyles: {
                        fillColor: [249, 250, 251]
                    }
                });
            }

            // Save the PDF
            doc.save('upcoming-bookings-' + new Date().toISOString().split('T')[0] + '.pdf');
        }
    </script>
    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/calendar-tab/calendar.js"></script>
</body>

</html>