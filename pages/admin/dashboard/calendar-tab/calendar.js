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

    // Show maximum 3 bookings, then show "X more"
    const maxVisible = 3;
    dayBookings.slice(0, maxVisible).forEach(booking => {
        const bookingItem = document.createElement('div');
        bookingItem.className = `booking-item ${booking.status}`;
        bookingItem.textContent = `${booking.time} - ${booking.customer}`;
        bookingItem.onclick = () => showBookingModal(booking);
        bookingContainer.appendChild(bookingItem);
    });

    // Show overflow indicator
    if (dayBookings.length > maxVisible) {
        const overflow = document.createElement('div');
        overflow.className = 'booking-overflow';
        overflow.textContent = `+${dayBookings.length - maxVisible} more`;
        overflow.onclick = () => showDayBookings(date, dayBookings);
        bookingContainer.appendChild(overflow);
    }

    dayCell.appendChild(bookingContainer);

    return dayCell;
}

function showBookingModal(booking) {
    const modal = document.getElementById('bookingModal');
    const details = document.getElementById('bookingDetails');

    // Format the booking data for display
    const formattedDate = new Date(booking.date).toLocaleDateString();
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

    let bookingsList = `<h3 style="margin-bottom: 16px; color: #111827;">Bookings for ${dateStr}</h3>`;

    bookings.forEach(booking => {
        const customerName = booking.customer || 'N/A';
        const service = booking.service || 'N/A';
        const time = booking.time || 'N/A';
        const endTime = booking.end_time || 'N/A';
        const referenceNumber = booking.reference_number || 'N/A';

        bookingsList += `
            <div style="margin-bottom: 12px; padding: 12px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb; cursor: pointer;" onclick="showBookingModal(${JSON.stringify(booking).replace(/"/g, '&quot;')})">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <strong>${time} - ${endTime}</strong>
                    <span class="status-badge status-${booking.status}">${booking.status}</span>
                </div>
                <div style="color: #6b7280; font-size: 14px;">${customerName} - ${service}</div>
                <div style="color: #374151; font-weight: 500; margin-top: 4px;">Ref: ${referenceNumber}</div>
            </div>
        `;
    });

    details.innerHTML = bookingsList;
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('bookingModal').style.display = 'none';
}

function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
}

// Close modal when clicking outside
window.onclick = function (event) {
    const modal = document.getElementById('bookingModal');
    if (event.target === modal) {
        closeModal();
    }
}

// Initialize calendar when page loads
document.addEventListener('DOMContentLoaded', initCalendar);