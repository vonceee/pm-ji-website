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
</head>

<body>
    <div class="main-container">
        <!-- Calendar Section -->
        <div class="calendar-section">
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
        </div>

        <!-- Schedule Section -->
        <div class="schedule-section">
            <div class="schedule-container">
                <div class="schedule-header">
                    <h2>📋 Daily Schedule</h2>
                    <div class="schedule-date-selector">
                        <span>📅</span>
                        <input type="date" id="scheduleDate" onchange="updateSchedule()">
                    </div>
                </div>

                <div class="schedule-content">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Booking Details</th>
                            </tr>
                        </thead>
                        <tbody id="scheduleTableBody">
                            <tr>
                                <td colspan="2" class="no-bookings">
                                    <div class="no-bookings-icon">📅</div>
                                    <div>Select a date to view bookings</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
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
    </script>
    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/calendar-tab/calendar.js"></script>
</body>

</html>