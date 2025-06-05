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

    <div class="booking-modal" id="bookingModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Booking Details</div>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="booking-details" id="bookingDetails"></div>
        </div>
    </div>

    <script src="/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/calendar-tab/calendar.js"></script>
</body>

</html>