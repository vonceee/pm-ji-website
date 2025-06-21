document.addEventListener('DOMContentLoaded', function () {
    let bookingToReschedule = null;

    // Handle reschedule booking button clicks
    document.querySelectorAll('.reschedule-booking').forEach(button => {
        button.addEventListener('click', function () {
            bookingToReschedule = {
                id: this.dataset.bookingId,
                referenceId: this.dataset.referenceId,
                eventType: this.dataset.eventType,
                currentDate: this.dataset.currentDate,
                currentStartTime: this.dataset.currentStartTime,
                currentEndTime: this.dataset.currentEndTime,
                duration: this.dataset.duration
            };

            // Populate modal with booking details
            document.getElementById('reschedule-event-type').textContent = bookingToReschedule.eventType;
            document.getElementById('reschedule-reference-id').textContent = bookingToReschedule.referenceId;
            document.getElementById('reschedule-current-date').textContent = formatDate(bookingToReschedule.currentDate);
            document.getElementById('reschedule-current-time').textContent =
                formatTime(bookingToReschedule.currentStartTime) + ' - ' + formatTime(bookingToReschedule.currentEndTime);

            // Set minimum date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const minDate = tomorrow.toISOString().split('T')[0];
            document.getElementById('new-event-date').min = minDate;

            // Clear previous selections and errors
            document.getElementById('new-event-date').value = '';
            document.getElementById('new-start-time').value = '';
            document.getElementById('new-end-time').value = '';
            document.getElementById('reschedule-reason').value = '';

            // Remove validation classes
            document.querySelectorAll('#rescheduleBookingModal .is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });

            // Reset time slots
            document.getElementById('time-slots-container').innerHTML = '';
            document.getElementById('time-slots-section').style.display = 'none';

            // Show modal
            $('#rescheduleBookingModal').modal('show');
        });
    });

    // Handle date change for time slot availability
    document.getElementById('new-event-date').addEventListener('change', function () {
        const selectedDate = this.value;
        if (selectedDate && bookingToReschedule) {
            loadAvailableTimeSlots(selectedDate, bookingToReschedule.duration);
        } else {
            document.getElementById('time-slots-section').style.display = 'none';
        }
    });

    // Handle time slot selection
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('time-slot-btn')) {
            // Remove active class from all time slot buttons
            document.querySelectorAll('.time-slot-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Add active class to clicked button
            e.target.classList.add('active');

            // Set the hidden time inputs
            const startTime = e.target.dataset.startTime;
            const endTime = e.target.dataset.endTime;

            document.getElementById('new-start-time').value = startTime;
            document.getElementById('new-end-time').value = endTime;

            // Remove validation classes
            document.getElementById('new-event-date').classList.remove('is-invalid');
        }
    });

    // Handle confirm reschedule
    document.getElementById('confirm-reschedule-booking').addEventListener('click', function () {
        if (!bookingToReschedule) return;

        const newDate = document.getElementById('new-event-date').value;
        const newStartTime = document.getElementById('new-start-time').value;
        const newEndTime = document.getElementById('new-end-time').value;
        const reason = document.getElementById('reschedule-reason').value.trim();

        // Validate inputs
        let hasErrors = false;

        if (!newDate) {
            document.getElementById('new-event-date').classList.add('is-invalid');
            hasErrors = true;
        }

        if (!newStartTime || !newEndTime) {
            document.getElementById('new-event-date').classList.add('is-invalid');
            showAlert('danger', 'Please select a time slot');
            hasErrors = true;
        }

        if (!reason) {
            document.getElementById('reschedule-reason').classList.add('is-invalid');
            hasErrors = true;
        }

        if (hasErrors) {
            showAlert('danger', 'Please fill in all required fields');
            return;
        }

        const confirmButton = this;
        const originalText = confirmButton.innerHTML;

        // Show loading state
        confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Rescheduling...';
        confirmButton.disabled = true;

        // Send reschedule request
        fetch('/NEW-PM-JI-RESERVIFY/pages/customer/actions/reschedule-booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                booking_id: bookingToReschedule.id,
                new_date: newDate,
                new_start_time: newStartTime,
                new_end_time: newEndTime,
                reason: reason
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', `
                    <strong>Booking Rescheduled Successfully!</strong><br>
                    Reference: ${data.data.reference_number}<br>
                    New Date: ${formatDate(data.data.new_date)}<br>
                    New Time: ${formatTime(data.data.new_start_time)} - ${formatTime(data.data.new_end_time)}
                `);
                    $('#rescheduleBookingModal').modal('hide');
                    // Reload page to reflect changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert('danger', data.message || 'Failed to reschedule booking. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'An error occurred while rescheduling the booking. Please try again.');
            })
            .finally(() => {
                // Reset button state
                confirmButton.innerHTML = originalText;
                confirmButton.disabled = false;
            });
    });

    // Validate inputs on change
    document.getElementById('new-event-date').addEventListener('change', function () {
        if (this.value) {
            this.classList.remove('is-invalid');
        }
    });

    document.getElementById('reschedule-reason').addEventListener('input', function () {
        if (this.value.trim()) {
            this.classList.remove('is-invalid');
        }
    });

    // Load available time slots
    function loadAvailableTimeSlots(date, duration) {
        const container = document.getElementById('time-slots-container');
        const section = document.getElementById('time-slots-section');

        // Show loading
        container.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading available time slots...</div>';
        section.style.display = 'block';

        fetch('/NEW-PM-JI-RESERVIFY/pages/customer/actions/get-available-slots.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                date: date,
                duration: duration,
                exclude_booking_id: bookingToReschedule.id
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    let slotsHtml = '<div class="row">';
                    data.data.forEach(slot => {
                        slotsHtml += `
                        <div class="col-md-4 col-sm-6 mb-2">
                            <button type="button" class="btn btn-outline-primary btn-block time-slot-btn" 
                                    data-start-time="${slot.start_time}" 
                                    data-end-time="${slot.end_time}">
                                ${formatTime(slot.start_time)} - ${formatTime(slot.end_time)}
                            </button>
                        </div>
                    `;
                    });
                    slotsHtml += '</div>';
                    container.innerHTML = slotsHtml;
                } else {
                    container.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No available time slots for the selected date. Please choose a different date.
                    </div>
                `;
                }
            })
            .catch(error => {
                console.error('Error loading time slots:', error);
                container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Error loading time slots. Please try again.
                </div>
            `;
            });
    }

    // Helper functions
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    function formatTime(timeString) {
        const [hours, minutes] = timeString.split(':');
        const date = new Date();
        date.setHours(parseInt(hours), parseInt(minutes));
        return date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    // Function to show alerts (reuse from cancel-booking.js)
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alert-container');
        const alertId = 'alert-' + Date.now();

        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;

        alertContainer.insertAdjacentHTML('beforeend', alertHtml);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alertElement = document.getElementById(alertId);
            if (alertElement) {
                $(alertElement).alert('close');
            }
        }, 5000);
    }
});