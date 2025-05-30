let bookings = {};

function fetchBookings() {
    return fetch('/NEW-PM-JI-RESERVIFY/pages/customer/booking/API/get_bookings.php')
        .then(res => res.json())
        .then(data => { bookings = data; });
}

function isFullyBooked(dateStr) {
    // if all time slots are booked (from 08:00 to 18:00)
    return bookings[dateStr] && bookings[dateStr].length >= 11;
}

function isPartiallyBooked(dateStr) {
    return bookings[dateStr] && bookings[dateStr].length > 0 && bookings[dateStr].length < 11;
}

function timeToInt(timeStr) {
    // "08:00" => 8, "18:00" => 18
    return parseInt(timeStr.split(':')[0], 10);
}

function hasAvailableSlot(dateStr) {
    const minDuration = 3; // or get from your duration input if needed
    const bookingsForDay = bookings[dateStr] || [];
    for (let h = 8; h <= 18; h++) {
        let userStart = h;
        let userEnd = userStart + minDuration;
        let overlaps = bookingsForDay.some(b => {
            let bookedStart = timeToInt(b.start);
            let bookedEnd = timeToInt(b.end) + 1; // buffer 1 hour after end
            return userStart < bookedEnd && userEnd > bookedStart;
        });
        if (!overlaps && userEnd <= 19) { // 19:00 is outside the last slot
            return true;
        }
    }
    return false;
}

function updateStartTimes(dateStr) {
    const startTimeSelect = document.getElementById('startTime');
    const durationInput = document.querySelector('input[name="duration"]:checked');
    const minDuration = durationInput ? parseInt(durationInput.value, 10) : 3; // fallback to 3 if not selected

    // reset all options
    Array.from(startTimeSelect.options).forEach(opt => {
        opt.disabled = false;
        opt.style.color = '';
    });

    const bookingsForDay = bookings[dateStr] || [];

    // for each possible start time, check if it would overlap with any booking
    Array.from(startTimeSelect.options).forEach(opt => {
        const userStart = timeToInt(opt.value);
        const userEnd = userStart + minDuration;

        // check overlap with each booking (with 1 hour buffer after booking)
        const overlaps = bookingsForDay.some(b => {
            let bookedStart = timeToInt(b.start);
            let bookedEnd = timeToInt(b.end) + 1; // buffer 1 hour after end
            // overlap if userStart < bookedEnd and userEnd > bookedStart
            return userStart < bookedEnd && userEnd > bookedStart;
        });

        if (overlaps) {
            opt.disabled = true;
            opt.style.color = '#ccc';
        }
    });

    // select first available time if current is disabled
    let firstAvailable = Array.from(startTimeSelect.options).find(opt => !opt.disabled);
    if (firstAvailable) startTimeSelect.value = firstAvailable.value;
    else startTimeSelect.value = '';

    // always update end time after setting start time
    updateEndTime();
}

// also update available times when duration changes
document.querySelectorAll('input[name="duration"]').forEach(input => {
    input.addEventListener('change', function () {
        const dateInput = document.getElementById('reservationDate');
        if (dateInput.value) updateStartTimes(dateInput.value);
    });
});

$(function () {
    fetchBookings().then(() => {
        $("#reservationDate").datepicker({
            dateFormat: "yy-mm-dd",
            minDate: 1,
            beforeShowDay: function (date) {
                var dateString = $.datepicker.formatDate("yy-mm-dd", date);
                if (!hasAvailableSlot(dateString)) {
                    return [false, "gray", "Unavailable"];
                }
                if (isFullyBooked(dateString)) {
                    return [false, "gray", "Unavailable"];
                }
                if (isPartiallyBooked(dateString)) {
                    return [true, "yellow", "Partially Booked"];
                }
                return [true, "green", "Available"];
            },
            onSelect: function (dateText) {
                updateStartTimes(dateText);
            }
        });
    });

    // when date changes, update available times
    $('#reservationDate').on('change', function () {
        updateStartTimes(this.value);
    });
});

// end time calculation (always outputs military time format HH:mm)
function pad(num) { return num.toString().padStart(2, '0'); }
function updateEndTime() {
    const startTimeInput = document.getElementById('startTime');
    const endTimeInput = document.getElementById('endTime');
    const durationInput = document.querySelector('input[name="duration"]:checked');
    if (!startTimeInput || !endTimeInput || !durationInput) {
        if (endTimeInput) endTimeInput.value = '';
        return;
    }
    if (!startTimeInput.value || !durationInput.value) {
        endTimeInput.value = '';
        return;
    }
    const [startHour, startMin] = startTimeInput.value.split(':').map(Number);
    const duration = parseInt(durationInput.value, 10);
    let endHour = startHour + duration;
    let endMin = startMin;

    endTimeInput.value = `${pad(endHour)}:${pad(endMin)}`;
}

// bind events and initialize end time
document.getElementById('startTime').addEventListener('input', updateEndTime);
document.querySelectorAll('input[name="duration"]').forEach(input => {
    input.addEventListener('change', updateEndTime);
});
updateEndTime(); // initialize on page load

// step 2 validation function
function validateStep2() {
    const errorDiv = document.getElementById('step2-error');
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';

    // check if date is selected
    const dateInput = document.getElementById('reservationDate');
    if (!dateInput.value) {
        errorDiv.textContent = 'select a reservation date.';
        errorDiv.style.display = 'block';
        dateInput.focus();
        return false;
    }
    // check if start time is selected
    const startTime = document.getElementById('startTime');
    if (!startTime.value) {
        errorDiv.textContent = 'select a start time.';
        errorDiv.style.display = 'block';
        startTime.focus();
        return false;
    }
    return true;
}