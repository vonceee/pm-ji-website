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

// convert 24-hour format to 12-hour format
function militaryTo12Hour(militaryTime) {
    const [hour, minute] = militaryTime.split(':').map(Number);
    const period = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
    return `${displayHour}:${minute.toString().padStart(2, '0')} ${period}`;
}

// convert 12-hour format to 24-hour format
function twelveHourToMilitary(twelveHourTime) {
    const [time, period] = twelveHourTime.split(' ');
    const [hour, minute] = time.split(':').map(Number);
    let militaryHour = hour;
    
    if (period === 'PM' && hour !== 12) {
        militaryHour = hour + 12;
    } else if (period === 'AM' && hour === 12) {
        militaryHour = 0;
    }
    
    return `${militaryHour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
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
        const userStart = timeToInt(opt.value); // opt.value is still in military format
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
    if (firstAvailable) {
        startTimeSelect.value = firstAvailable.value;
        // Trigger change event to update the display and end time
        startTimeSelect.dispatchEvent(new Event('change'));
    } else {
        startTimeSelect.value = '';
    }

    // always update end time after setting start time
    updateEndTime();
}

// Add event listener to handle the display format when start time changes
document.addEventListener('DOMContentLoaded', function() {
    const startTimeSelect = document.getElementById('startTime');
    if (startTimeSelect) {
        startTimeSelect.addEventListener('change', function() {
            // Store the military time value for form submission
            const militaryValue = this.value;
            this.setAttribute('data-military', militaryValue);
            
            // Update the display to show 12-hour format
            if (militaryValue) {
                const displayTime = militaryTo12Hour(militaryValue);
                // Update the selected option's text temporarily for display
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption) {
                    selectedOption.textContent = displayTime;
                }
            }
            
            updateEndTime();
        });
    }
});

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

// end time calculation (displays in 12-hour format but stores military time in hidden field)
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
    
    const militaryTime = startTimeInput.getAttribute('data-military') || startTimeInput.value;
    const [startHour, startMin] = militaryTime.split(':').map(Number);
    const duration = parseInt(durationInput.value, 10);
    let endHour = startHour + duration;
    let endMin = startMin;

    // Store military time format in the actual form field
    const militaryEndTime = `${pad(endHour)}:${pad(endMin)}`;
    endTimeInput.value = militaryTo12Hour(militaryEndTime); // Display 12-hour format
    
    // Set the actual form value to military time (you might need a hidden field for this)
    endTimeInput.setAttribute('data-military', militaryEndTime);
}

// bind events and initialize end time
document.getElementById('startTime')?.addEventListener('input', updateEndTime);
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
        errorDiv.textContent = 'Select a Booking Date.';
        errorDiv.style.display = 'block';
        dateInput.focus();
        return false;
    }
    // check if start time is selected
    const startTime = document.getElementById('startTime');
    if (!startTime.value) {
        errorDiv.textContent = 'Select Photoshoot Start Time.';
        errorDiv.style.display = 'block';
        startTime.focus();
        return false;
    }
    return true;
}