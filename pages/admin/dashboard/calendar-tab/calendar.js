// Calendar functionality with proper vertical layout fix
let currentDate = new Date();
let bookings = []; // This will be populated from your database

function initCalendar() {
    // Use the database bookings passed from PHP instead of sample data
    if (typeof databaseBookings !== 'undefined') {
        bookings = databaseBookings;
        console.log('Using database bookings:', bookings);
    } else {
        console.warn('Database bookings not found, using empty array');
        bookings = [];
    }
    renderCalendar();
}

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    // Update month display
    const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"];
    document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;

    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());

    const calendarGrid = document.querySelector('.calendar-grid');

    // Remove existing day cells (keep headers)
    const existingDays = calendarGrid.querySelectorAll('.day-cell');
    existingDays.forEach(day => day.remove());

    // Generate 42 days (6 weeks)
    for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);

        const dayCell = createDayCell(date, month);
        calendarGrid.appendChild(dayCell);
    }
}

function createDayCell(date, currentMonth) {
    const dayCell = document.createElement('div');
    dayCell.className = 'day-cell';

    // Check if date is in current month
    if (date.getMonth() !== currentMonth) {
        dayCell.classList.add('other-month');
    }

    // Check if date is today
    const today = new Date();
    if (date.toDateString() === today.toDateString()) {
        dayCell.classList.add('today');
    }

    // Add day number
    const dayNumber = document.createElement('div');
    dayNumber.className = 'day-number';
    dayNumber.textContent = date.getDate();
    dayCell.appendChild(dayNumber);

    // Create container for booking items
    const bookingContainer = document.createElement('div');
    bookingContainer.className = 'booking-items-container';

    // Add bookings for this date
    const dateStr = date.toISOString().split('T')[0];
    const dayBookings = bookings.filter(booking => booking.date === dateStr);

    console.log(`Checking date ${dateStr}, found ${dayBookings.length} bookings`);

    // Determine max visible bookings based on screen size
    const maxVisible = getMaxVisibleBookings();

    // Show visible bookings
    dayBookings.slice(0, maxVisible).forEach(booking => {
        const bookingItem = document.createElement('div');
        bookingItem.className = `booking-item ${booking.status}`;

        // Truncate text for better fit
        const displayText = truncateBookingText(`${booking.time} - ${booking.customer}`, 18);
        bookingItem.textContent = displayText;
        bookingItem.title = `${booking.time} - ${booking.customer} (${booking.service})`; // Full text on hover

        bookingItem.onclick = (e) => {
            e.stopPropagation();
            showBookingModal(booking);
        };
        bookingContainer.appendChild(bookingItem);
    });

    // Show overflow indicator if there are more bookings
    if (dayBookings.length > maxVisible) {
        const overflow = document.createElement('div');
        overflow.className = 'booking-overflow';
        overflow.textContent = `+${dayBookings.length - maxVisible} more`;
        overflow.title = `Click to see all ${dayBookings.length} bookings`;
        overflow.onclick = (e) => {
            e.stopPropagation();
            showDayBookings(date, dayBookings);
        };
        bookingContainer.appendChild(overflow);
    }

    dayCell.appendChild(bookingContainer);

    // Add click handler to day cell for creating new bookings (optional)
    dayCell.onclick = () => {
        if (dayBookings.length > 0) {
            showDayBookings(date, dayBookings);
        }
    };

    return dayCell;
}

function truncateBookingText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength - 3) + '...';
}

function getMaxVisibleBookings() {
    // Adjust max visible bookings based on screen size
    const screenWidth = window.innerWidth;
    if (screenWidth < 480) return 2;
    if (screenWidth < 768) return 2;
    return 3;
}

function showBookingModal(booking) {
    const modal = document.getElementById('bookingModal');
    const details = document.getElementById('bookingDetails');

    // Format the booking data for display
    const formattedDate = new Date(booking.date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    const customerName = booking.customer || 'N/A';
    const service = booking.service || 'N/A';
    const time = booking.time || 'N/A';
    const endTime = booking.end_time || 'N/A';
    const duration = booking.duration || 'N/A';
    const address = booking.address || 'N/A';
    const referenceNumber = booking.reference_number || 'N/A';
    const email = booking.email || 'N/A';
    const phone = booking.phone || 'N/A';

    details.innerHTML = `
        <div class="detail-item">
            <div class="detail-label">Reference Number:</div>
            <div class="detail-value">${referenceNumber}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Customer:</div>
            <div class="detail-value">${customerName}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Email:</div>
            <div class="detail-value">${email}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Phone:</div>
            <div class="detail-value">${phone}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Service:</div>
            <div class="detail-value">${service}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Date:</div>
            <div class="detail-value">${formattedDate}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Time:</div>
            <div class="detail-value">${time} - ${endTime}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Duration:</div>
            <div class="detail-value">${duration}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Address:</div>
            <div class="detail-value">${address}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Status:</div>
            <div class="detail-value">
                <span class="status-badge status-${booking.status}">${booking.status}</span>
            </div>
        </div>
    `;

    modal.style.display = 'flex';

    // Prevent body scrolling when modal is open
    document.body.style.overflow = 'hidden';
}

function showDayBookings(date, bookings) {
    const modal = document.getElementById('bookingModal');
    const details = document.getElementById('bookingDetails');

    const dateStr = date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    let bookingsList = `<h3 style="margin-bottom: 20px; color: #111827; font-size: 18px;">Bookings for ${dateStr}</h3>`;

    if (bookings.length === 0) {
        bookingsList += `<p style="color: #6b7280; text-align: center; padding: 20px;">No bookings for this day</p>`;
    } else {
        bookings.forEach((booking, index) => {
            const customerName = booking.customer || 'N/A';
            const service = booking.service || 'N/A';
            const time = booking.time || 'N/A';
            const endTime = booking.end_time || 'N/A';
            const referenceNumber = booking.reference_number || 'N/A';

            // Create a properly escaped booking object for onclick
            const bookingStr = JSON.stringify(booking).replace(/"/g, '&quot;');

            bookingsList += `
                <div style="margin-bottom: 16px; padding: 16px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb; cursor: pointer; transition: all 0.2s ease;" 
                     onclick="showBookingModal(${bookingStr})"
                     onmouseover="this.style.background='#f3f4f6'"
                     onmouseout="this.style.background='#f9fafb'">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <strong style="color: #111827; font-size: 15px;">${time} - ${endTime}</strong>
                        <span class="status-badge status-${booking.status}" style="margin-left: 12px;">${booking.status}</span>
                    </div>
                    <div style="color: #374151; font-size: 14px; margin-bottom: 4px;">${customerName}</div>
                    <div style="color: #6b7280; font-size: 13px; margin-bottom: 4px;">${service}</div>
                    <div style="color: #6b7280; font-weight: 500; font-size: 12px;">Ref: ${referenceNumber}</div>
                </div>
            `;
        });
    }

    details.innerHTML = bookingsList;
    modal.style.display = 'flex';

    // Prevent body scrolling when modal is open
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('bookingModal');
    modal.style.display = 'none';

    // Restore body scrolling
    document.body.style.overflow = 'auto';
}

function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
}

// Close modal when clicking outside or pressing Escape
window.onclick = function (event) {
    const modal = document.getElementById('bookingModal');
    if (event.target === modal) {
        closeModal();
    }
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

// Handle window resize to adjust booking display
window.addEventListener('resize', function () {
    renderCalendar();
});

// Initialize calendar when page loads
document.addEventListener('DOMContentLoaded', initCalendar);

// Additional JavaScript functions for Schedule Table
// Add these functions to your existing calendar.js file

// Initialize schedule with today's date
document.addEventListener('DOMContentLoaded', function () {
    const today = new Date();
    const todayString = today.toISOString().split('T')[0];
    document.getElementById('scheduleDate').value = todayString;
    updateSchedule();
});

// Update schedule table based on selected date
function updateSchedule() {
    const selectedDate = document.getElementById('scheduleDate').value;
    const scheduleTableBody = document.getElementById('scheduleTableBody');

    if (!selectedDate) {
        scheduleTableBody.innerHTML = `
            <tr>
                <td colspan="2" class="no-bookings">
                    <div class="no-bookings-icon">📅</div>
                    <div>Select a date to view bookings</div>
                </td>
            </tr>
        `;
        return;
    }

    // Filter bookings for the selected date
    const dayBookings = databaseBookings.filter(booking => booking.date === selectedDate);

    if (dayBookings.length === 0) {
        scheduleTableBody.innerHTML = `
            <tr>
                <td colspan="2" class="no-bookings">
                    <div class="no-bookings-icon">📭</div>
                    <div>No bookings for this date</div>
                </td>
            </tr>
        `;
        return;
    }

    // Sort bookings by time
    dayBookings.sort((a, b) => {
        return a.time.localeCompare(b.time);
    });

    // Generate table rows
    let tableHTML = '';
    dayBookings.forEach(booking => {
        const timeRange = formatTimeRange(booking.time, booking.end_time);
        tableHTML += `
            <tr class="schedule-booking" onclick="showBookingDetails(${booking.id})">
                <td class="schedule-time">${timeRange}</td>
                <td>
                    <div class="schedule-customer">${booking.customer || 'N/A'}</div>
                    <div class="schedule-service">${booking.service}</div>
                    <span class="schedule-status ${booking.status}">${capitalizeFirst(booking.status)}</span>
                </td>
            </tr>
        `;
    });

    scheduleTableBody.innerHTML = tableHTML;
}

// Format time range for display
function formatTimeRange(startTime, endTime) {
    if (!startTime) return 'N/A';

    const formatTime = (time) => {
        if (!time) return '';
        const [hours, minutes] = time.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? '' : '';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
    };

    const formattedStart = formatTime(startTime);
    const formattedEnd = endTime ? formatTime(endTime) : '';

    return formattedEnd ? `${formattedStart} - ${formattedEnd}` : formattedStart;
}

// Capitalize first letter
function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Update schedule when calendar date is clicked
function onDateClick(date) {
    // Update the schedule date selector
    const dateString = date.toISOString().split('T')[0];
    document.getElementById('scheduleDate').value = dateString;
    updateSchedule();

    // Highlight the selected date in calendar (existing functionality)
    // This should integrate with your existing calendar click functionality
}

// Enhanced booking details modal (extends existing function)
function showBookingDetails(bookingId) {
    const booking = databaseBookings.find(b => b.id == bookingId);
    if (!booking) return;

    const modal = document.getElementById('bookingModal');
    const detailsContainer = document.getElementById('bookingDetails');

    const timeRange = formatTimeRange(booking.time, booking.end_time);
    const statusClass = `status-${booking.status}`;

    detailsContainer.innerHTML = `
        <div class="detail-item">
            <span class="detail-label">Reference ID:</span>
            <span class="detail-value">${booking.reference_id || 'N/A'}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Customer:</span>
            <span class="detail-value">${booking.customer || 'N/A'}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Email:</span>
            <span class="detail-value">${booking.email || 'N/A'}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Phone:</span>
            <span class="detail-value">${booking.phone || 'N/A'}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Service:</span>
            <span class="detail-value">${booking.service}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Date:</span>
            <span class="detail-value">${formatDate(booking.date)}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Time:</span>
            <span class="detail-value">${timeRange}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Duration:</span>
            <span class="detail-value">${booking.duration || 'N/A'}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Address:</span>
            <span class="detail-value">${booking.address || 'N/A'}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Reference Number:</span>
            <span class="detail-value">${booking.reference_number || 'N/A'}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Status:</span>
            <span class="detail-value">
                <span class="status-badge ${statusClass}">${capitalizeFirst(booking.status)}</span>
            </span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Created:</span>
            <span class="detail-value">${formatDateTime(booking.created_at)}</span>
        </div>
    `;

    modal.style.display = 'flex';
}

// Format date for display
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Format date and time for display
function formatDateTime(dateTimeString) {
    if (!dateTimeString) return 'N/A';
    const date = new Date(dateTimeString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Close modal function
function closeModal() {
    document.getElementById('bookingModal').style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('click', function (event) {
    const modal = document.getElementById('bookingModal');
    if (event.target === modal) {
        closeModal();
    }
});

// Integration with existing calendar functions
// You'll need to modify your existing day cell click handler to include this:
function enhanceCalendarDayClick() {
    // This should be integrated into your existing calendar day click functionality
    // When a day is clicked in the calendar, update the schedule date
    document.querySelectorAll('.day-cell').forEach(cell => {
        cell.addEventListener('click', function () {
            const dayNumber = this.querySelector('.day-number');
            if (dayNumber && !this.classList.contains('other-month')) {
                // Get the current month and year from your calendar state
                const currentDate = getCurrentCalendarDate(); // You'll need to implement this
                const day = parseInt(dayNumber.textContent);

                const selectedDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
                const dateString = selectedDate.toISOString().split('T')[0];

                document.getElementById('scheduleDate').value = dateString;
                updateSchedule();
            }
        });
    });
}

// Utility function to get current calendar date
// This should return the currently displayed month/year in your calendar
function getCurrentCalendarDate() {
    // You'll need to implement this based on your existing calendar state
    // For now, returning current date as fallback
    return new Date();
}

// Auto-update schedule when calendar month changes
function onCalendarMonthChange() {
    // Clear schedule when month changes
    const scheduleDate = document.getElementById('scheduleDate');
    const currentValue = scheduleDate.value;

    // If the selected date is not in the current calendar view, clear it
    if (currentValue) {
        const selectedDate = new Date(currentValue);
        const calendarDate = getCurrentCalendarDate();

        if (selectedDate.getMonth() !== calendarDate.getMonth() ||
            selectedDate.getFullYear() !== calendarDate.getFullYear()) {
            scheduleDate.value = '';
            updateSchedule();
        }
    }
}