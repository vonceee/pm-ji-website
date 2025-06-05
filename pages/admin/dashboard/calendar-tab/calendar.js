// Sample booking data - replace with your actual data from PHP
const bookings = [
    {
        id: 128,
        reference_id: 'REF-68410B0E11F84',
        user_id: 36,
        event_type: 'Wedding',
        duration: 3,
        reservation_date: '2025-06-27',
        start_time: '8:00 AM',
        end_time: '11:00 AM',
        street_address: 'Kamagong Street Covered Court Blk 1 Lot 6',
        barangay: 'Barangay 173',
        city: 'City of Caloocan',
        full_address: 'Kamagong Street Covered Court Blk 1 Lot 6, Barangay 173, City of Caloocan, NCR',
        reference_number: '9029072722597',
        status: 'cancelled',
        created_at: '2025-06-05 03:12:14'
    },
    // Add more sample bookings for demonstration
    {
        id: 129,
        reference_id: 'REF-68410B0E11F85',
        user_id: 37,
        event_type: 'Birthday',
        duration: 4,
        reservation_date: '2025-06-15',
        start_time: '2:00 PM',
        end_time: '6:00 PM',
        street_address: 'Sample Street 123',
        barangay: 'Sample Barangay',
        city: 'Sample City',
        full_address: 'Sample Street 123, Sample Barangay, Sample City',
        reference_number: '9029072722598',
        status: 'confirmed',
        created_at: '2025-06-05 04:00:00'
    },
    {
        id: 130,
        reference_id: 'REF-68410B0E11F86',
        user_id: 38,
        event_type: 'Corporate Event',
        duration: 6,
        reservation_date: '2025-06-20',
        start_time: '9:00 AM',
        end_time: '3:00 PM',
        street_address: 'Business District Ave',
        barangay: 'Business Barangay',
        city: 'Metro Manila',
        full_address: 'Business District Ave, Business Barangay, Metro Manila',
        reference_number: '9029072722599',
        status: 'pending',
        created_at: '2025-06-05 05:00:00'
    }
];

let currentDate = new Date();
let today = new Date();

function initializeCalendar() {
    renderCalendar();
}

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    // Update month display
    document.getElementById('currentMonth').textContent =
        new Date(year, month).toLocaleDateString('en-US', {
            month: 'long',
            year: 'numeric'
        });

    // Clear previous days
    document.getElementById('calendarDays').innerHTML = '';

    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());

    // Generate 42 days (6 weeks)
    for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);

        const dayCell = createDayCell(date, month);
        document.getElementById('calendarDays').appendChild(dayCell);
    }
}

function createDayCell(date, currentMonth) {
    const dayCell = document.createElement('div');
    dayCell.className = 'day-cell';

    // Add classes for styling
    if (date.getMonth() !== currentMonth) {
        dayCell.classList.add('other-month');
    }

    if (date.toDateString() === today.toDateString()) {
        dayCell.classList.add('today');
    }

    // Add day number
    const dayNumber = document.createElement('div');
    dayNumber.className = 'day-number';
    dayNumber.textContent = date.getDate();
    dayCell.appendChild(dayNumber);

    // Add bookings for this date
    const dateString = date.toISOString().split('T')[0];
    const dayBookings = bookings.filter(booking => booking.reservation_date === dateString);

    dayBookings.forEach(booking => {
        const bookingItem = document.createElement('div');
        bookingItem.className = `booking-item ${booking.status}`;
        bookingItem.textContent = `${booking.event_type} - ${booking.start_time}`;
        bookingItem.onclick = () => showBookingDetails(booking);
        dayCell.appendChild(bookingItem);
    });

    return dayCell;
}

function showBookingDetails(booking) {
    const modal = document.getElementById('bookingModal');
    const detailsContainer = document.getElementById('bookingDetails');

    detailsContainer.innerHTML = `
                <div class="detail-item">
                    <span class="detail-label">Reference ID:</span>
                    <span class="detail-value">${booking.reference_id}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Event Type:</span>
                    <span class="detail-value">${booking.event_type}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Date & Time:</span>
                    <span class="detail-value">${booking.reservation_date}<br>${booking.start_time} - ${booking.end_time}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Duration:</span>
                    <span class="detail-value">${booking.duration} hours</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-${booking.status}">${booking.status}</span>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value">${booking.full_address}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Reference Number:</span>
                    <span class="detail-value">${booking.reference_number}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Created:</span>
                    <span class="detail-value">${new Date(booking.created_at).toLocaleString()}</span>
                </div>
            `;

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
document.addEventListener('DOMContentLoaded', initializeCalendar);