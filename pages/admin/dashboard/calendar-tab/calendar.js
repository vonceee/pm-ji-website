// Calendar functionality with proper vertical layout fix
let currentDate = new Date();
let bookings = []; // This will be populated from your database

// Sample booking data - replace with your actual data source
const sampleBookings = [
    {
        id: 1,
        date: '2024-06-15',
        customer: 'John Doe',
        service: 'Wedding Photography',
        status: 'confirmed',
        time: '10:00 AM',
        amount: '$1,500'
    },
    {
        id: 2,
        date: '2024-06-15',
        customer: 'Jane Smith',
        service: 'Portrait Session',
        status: 'pending',
        time: '2:00 PM',
        amount: '$300'
    },
    {
        id: 3,
        date: '2024-06-20',
        customer: 'Bob Johnson',
        service: 'Event Coverage',
        status: 'completed',
        time: '6:00 PM',
        amount: '$800'
    }
];

function initCalendar() {
    bookings = sampleBookings; // Replace with actual data fetch
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

    details.innerHTML = `
        <div class="detail-item">
            <div class="detail-label">Customer:</div>
            <div class="detail-value">${booking.customer}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Service:</div>
            <div class="detail-value">${booking.service}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Date:</div>
            <div class="detail-value">${new Date(booking.date).toLocaleDateString()}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Time:</div>
            <div class="detail-value">${booking.time}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Amount:</div>
            <div class="detail-value">${booking.amount}</div>
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
        bookingsList += `
            <div style="margin-bottom: 12px; padding: 12px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <strong>${booking.time}</strong>
                    <span class="status-badge status-${booking.status}">${booking.status}</span>
                </div>
                <div style="color: #6b7280; font-size: 14px;">${booking.customer} - ${booking.service}</div>
                <div style="color: #374151; font-weight: 500; margin-top: 4px;">${booking.amount}</div>
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