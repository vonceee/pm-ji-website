// Enhanced date-range-picker.js with dashboard card updates
$(document).ready(function() {
    // Initialize date range picker
    $('#dateRange').daterangepicker({
        startDate: $('#dateRange').data('start'),
        endDate: $('#dateRange').data('end'),
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'This Quarter': [moment().startOf('quarter'), moment().endOf('quarter')],
            'Last Quarter': [moment().subtract(1, 'quarter').startOf('quarter'), moment().subtract(1, 'quarter').endOf('quarter')],
            'This Year': [moment().startOf('year'), moment().endOf('year')],
            'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
        },
        locale: {
            format: 'MMM DD, YYYY'
        },
        showDropdowns: true,
        showCustomRangeLabel: true,
        alwaysShowCalendars: true,
        opens: 'left'
    });

    // Handle date range change
    $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
        const startDate = picker.startDate.format('YYYY-MM-DD');
        const endDate = picker.endDate.format('YYYY-MM-DD');
        
        // Update hidden form fields
        $('input[name="start"]').val(startDate);
        $('input[name="end"]').val(endDate);
        
        // Show loading state
        showLoadingState();
        
        // Update dashboard cards with new data
        updateDashboardCards(startDate, endDate);
    });

    // Show loading overlay on dashboard cards
    function showLoadingState() {
        if ($('.dashboard-cards .loading-overlay').length === 0) {
            $('.dashboard-cards').css('position', 'relative').append(`
                <div class="loading-overlay">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
        }
    }

    // Hide loading overlay
    function hideLoadingState() {
        $('.dashboard-cards .loading-overlay').remove();
    }

    // Update dashboard cards with AJAX
    function updateDashboardCards(startDate, endDate) {
        $.ajax({
            url: window.location.pathname,
            method: 'GET',
            data: {
                start: startDate,
                end: endDate,
                ajax: '1', // Flag to indicate AJAX request
                action: 'update_dashboard'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update dashboard cards with new data
                    updateCardValues(response.data);
                    
                    // Update date range display
                    updateDateRangeDisplay(response.data.dateRangeDisplay);
                    
                    // Update revenue breakdown if exists
                    if (response.data.revenueByEventType) {
                        updateRevenueBreakdown(response.data.revenueByEventType, response.data.dateRangeDisplay);
                    }
                    
                    // Update recent bookings if exists
                    if (response.data.recentBookings) {
                        updateRecentBookings(response.data.recentBookings);
                    }
                    
                    // Show success notification
                    showNotification('Dashboard updated successfully', 'success');
                } else {
                    showNotification('Error updating dashboard: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                showNotification('Failed to update dashboard. Please try again.', 'error');
            },
            complete: function() {
                hideLoadingState();
            }
        });
    }

    // Update individual card values with animation
    function updateCardValues(data) {
        // Update total bookings
        animateCardValue($('.dashboard-card:eq(0) h2'), data.totalCount);
        
        // Update approved bookings
        animateCardValue($('.dashboard-card:eq(1) h2'), data.approvedCount);
        
        // Update pending approvals
        animateCardValue($('.dashboard-card:eq(2) h2'), data.pendingCount);
        
        // Update completed bookings
        animateCardValue($('.dashboard-card:eq(3) h2'), data.completedCount);
        
        // Update revenue with currency formatting
        animateCardValue($('.dashboard-card:eq(4) h2'), data.revenue, true);
        
        // Update revenue growth indicator
        updateRevenueGrowth(data.revenueGrowth);
        
        // Update upcoming bookings
        animateCardValue($('.dashboard-card:eq(5) h2'), data.upcomingBookings);
    }

    // Animate card value changes
    function animateCardValue($element, newValue, isCurrency = false) {
        const currentValue = parseInt($element.text().replace(/[₱,]/g, '')) || 0;
        const targetValue = parseInt(newValue) || 0;
        
        if (currentValue === targetValue) return;
        
        // Add updating class for visual feedback
        $element.closest('.dashboard-card').addClass('updating');
        
        // Animate the number
        $({ countNum: currentValue }).animate({ countNum: targetValue }, {
            duration: 1000,
            easing: 'swing',
            step: function() {
                const val = Math.floor(this.countNum);
                if (isCurrency) {
                    $element.text('₱' + val.toLocaleString());
                } else {
                    $element.text(val.toLocaleString());
                }
            },
            complete: function() {
                const val = targetValue;
                if (isCurrency) {
                    $element.text('₱' + val.toLocaleString());
                } else {
                    $element.text(val.toLocaleString());
                }
                $element.closest('.dashboard-card').removeClass('updating');
            }
        });
    }

    // Update revenue growth indicator
    function updateRevenueGrowth(growth) {
        const $growthElement = $('.dashboard-card:eq(4) small span');
        const growthValue = parseFloat(growth) || 0;
        
        if (growthValue >= 0) {
            $growthElement.removeClass('text-danger').addClass('text-success');
            $growthElement.html('↗ ' + Math.abs(growthValue).toFixed(1) + '%');
        } else {
            $growthElement.removeClass('text-success').addClass('text-danger');
            $growthElement.html('↘ ' + Math.abs(growthValue).toFixed(1) + '%');
        }
    }

    // Update date range display
    function updateDateRangeDisplay(dateRangeDisplay) {
        $('.date-range-display strong').text(dateRangeDisplay);
        $('.revenue-breakdown .text-muted, .reports-toggle .text-muted').each(function() {
            if ($(this).text().includes('For period:') || $(this).text().includes('Reports will be generated for:')) {
                $(this).text($(this).text().replace(/: .+$/, ': ' + dateRangeDisplay));
            }
        });
    }

    // Update revenue breakdown section
    function updateRevenueBreakdown(revenueData, dateRangeDisplay) {
        const $revenueCards = $('.revenue-cards');
        
        if (revenueData && revenueData.length > 0) {
            let html = '';
            revenueData.forEach(function(eventData) {
                html += `
                    <div class="revenue-card">
                        <h3>${escapeHtml(eventData.event_type)}</h3>
                        <div class="revenue-amount">₱${parseFloat(eventData.total_revenue).toLocaleString()}</div>
                        <div class="booking-count">${parseInt(eventData.booking_count).toLocaleString()} bookings</div>
                    </div>
                `;
            });
            $revenueCards.html(html);
            $('.revenue-breakdown .alert').hide();
            $revenueCards.show();
        } else {
            $revenueCards.hide();
            $('.revenue-breakdown .alert').show();
        }
    }

    // Update recent bookings section
    function updateRecentBookings(bookingsData) {
        const $bookingsTable = $('.bookings-table');
        
        if (bookingsData && bookingsData.length > 0) {
            let html = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference ID</th>
                            <th>Event Type</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            bookingsData.forEach(function(booking) {
                const reservationDate = new Date(booking.reservation_date);
                const formattedDate = reservationDate.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: '2-digit', 
                    year: 'numeric' 
                });
                
                html += `
                    <tr>
                        <td>${escapeHtml(booking.reference_id)}</td>
                        <td>${escapeHtml(booking.event_type)}</td>
                        <td>${formattedDate}</td>
                        <td>${escapeHtml(booking.start_time + ' - ' + booking.end_time)}</td>
                        <td>${escapeHtml(booking.city)}</td>
                        <td>
                            <span class="status-badge status-${booking.status.toLowerCase()}">
                                ${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}
                            </span>
                        </td>
                        <td>
                            ${booking.amount_paid ? '₱' + parseFloat(booking.amount_paid).toLocaleString() : '<span class="text-muted">-</span>'}
                        </td>
                        <td>
                            ${booking.payment_status ? 
                                `<span class="payment-badge payment-${booking.payment_status.toLowerCase()}">
                                    ${booking.payment_status.charAt(0).toUpperCase() + booking.payment_status.slice(1)}
                                </span>` :
                                '<span class="text-muted">No payment</span>'
                            }
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            $bookingsTable.html(html);
        } else {
            $bookingsTable.html(`
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No recent bookings found.
                </div>
            `);
        }
    }

    // Show notification
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        $('.notification-toast').remove();
        
        const bgClass = type === 'success' ? 'bg-success' : 
                       type === 'error' ? 'bg-danger' : 'bg-info';
        
        const toast = $(`
            <div class="notification-toast position-fixed top-0 end-0 m-3" style="z-index: 9999;">
                <div class="toast show ${bgClass} text-white" role="alert">
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(toast);
        
        // Auto remove after 3 seconds
        setTimeout(function() {
            toast.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Utility function to escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
    }

    // Add CSS for updating animation
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .dashboard-card.updating {
                transform: scale(1.02);
                transition: transform 0.3s ease;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            
            .notification-toast .toast {
                min-width: 300px;
            }
            
            .loading-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.8);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
                border-radius: 8px;
            }
        `)
        .appendTo('head');
});