<?php
// src/controllers/CustomerBookingController.php
namespace Controllers;

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/src/models/CustomerBookingModel.php';
use Models\CustomerBookingModel;

class CustomerBookingController
{
    private $model;

    public function __construct()
    {
        $this->model = new CustomerBookingModel();
    }

    /**
     * Get user bookings with pagination and filters
     */
    public function getUserBookings($userEmail, $filters = [])
    {
        try {
            // Get user ID
            $userId = $this->model->getUserIdByEmail($userEmail);
            if (!$userId) {
                throw new \Exception('User not found.');
            }

            // Set default values
            $page = $filters['page'] ?? 1;
            $limit = $filters['limit'] ?? 3;
            $search = $filters['search'] ?? '';
            $filter_date = $filters['filter_date'] ?? '';
            $filter_status = $filters['filter_status'] ?? '';

            $offset = ($page - 1) * $limit;

            // Get data from model
            $total = $this->model->countBookings($userId, $search, $filter_date, $filter_status);
            $bookings = $this->model->getBookings($userId, $limit, $offset, $search, $filter_date, $filter_status);

            // Process bookings data for view
            $processedBookings = $this->processBookingsForView($bookings);

            return [
                'success' => true,
                'data' => [
                    'bookings' => $processedBookings,
                    'pagination' => [
                        'total' => $total,
                        'limit' => $limit,
                        'page' => $page,
                        'total_pages' => ceil($total / $limit),
                        'offset' => $offset
                    ],
                    'filters' => [
                        'search' => $search,
                        'filter_date' => $filter_date,
                        'filter_status' => $filter_status
                    ]
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process bookings data for view consumption
     */
    private function processBookingsForView($bookings)
    {
        $processed = [];

        foreach ($bookings as $booking) {
            $processed[] = [
                'id' => $booking['id'],
                'event_type' => $booking['event_type'],
                'reference_id' => $booking['reference_id'],
                'reservation_date' => $booking['reservation_date'],
                'start_time' => $booking['start_time'],
                'end_time' => $booking['end_time'],
                'status' => $booking['status'],
                'duration' => $booking['duration'],
                'full_address' => $booking['full_address'],

                // Time formatting
                'formatted_date' => $booking['reservation_date'],
                'formatted_start_time' => date('g:i A', strtotime($booking['start_time'])),
                'formatted_end_time' => date('g:i A', strtotime($booking['end_time'])),
                'time_difference_text' => $this->getTimeDifferenceText($booking['reservation_date']),

                // Status information
                'status_badge_class' => $this->getStatusBadgeClass($booking['status']),
                'status_display' => $this->getStatusDisplay($booking['status']),
                'is_cancelled' => $this->isCancelledStatus($booking['status']),
                'can_cancel' => $this->canCancelBooking($booking['status'], $booking['reservation_date']),

                // Payment information
                'payment' => [
                    'method' => $booking['payment_method'],
                    'type' => $booking['payment_type'],
                    'status' => $booking['payment_status'],
                    'amount_paid' => $booking['amount_paid'],
                    'balance' => $booking['balance'],
                    'payment_date' => $booking['payment_date'],
                    'screenshot_path' => $booking['payment_screenshot_path'],
                    'screenshot_thumbnail' => $booking['payment_screenshot_thumbnail']
                ],

                // Cancellation information
                'cancellation' => [
                    'reason' => $booking['cancellation_reason'],
                    'cancelled_at' => $booking['cancelled_at'],
                    'refund_status' => $booking['refund_status'],
                    'refund_amount' => $booking['refund_amount'],
                    'admin_notes' => $booking['cancellation_admin_notes'],
                    'formatted_cancelled_at' => $booking['cancelled_at'] ?
                        date('M j, Y g:i A', strtotime($booking['cancelled_at'])) : null
                ]
            ];
        }

        return $processed;
    }

    /**
     * Business logic methods (moved from view)
     */
    private function canCancelBooking($status, $reservationDate)
    {
        $cancelableStatuses = ['approved', 'confirmed', 'pending'];
        if (!in_array(strtolower($status), $cancelableStatuses)) {
            return false;
        }

        $bookingDateTime = strtotime($reservationDate);
        $currentTime = time();
        $timeDifference = $bookingDateTime - $currentTime;

        return $timeDifference >= 86400; // 24 hours in seconds
    }

    private function getStatusBadgeClass($status)
    {
        $statusClasses = [
            'pending' => 'warning',
            'confirmed' => 'success',
            'completed' => 'primary',
            'cancelled_by_user' => 'danger',
            'cancelled_by_admin' => 'danger',
            'no_show' => 'dark'
        ];

        return $statusClasses[strtolower($status)] ?? 'secondary';
    }

    private function getStatusDisplay($status)
    {
        $statusDisplays = [
            'cancelled_by_user' => 'Cancelled',
            'cancelled_by_admin' => 'Cancelled by Admin',
            'no_show' => 'No Show'
        ];

        return $statusDisplays[$status] ?? ucfirst($status);
    }

    private function getTimeDifferenceText($reservationDate)
    {
        $bookingDateTime = strtotime($reservationDate);
        $currentTime = time();
        $timeDifference = $bookingDateTime - $currentTime;

        if ($timeDifference < 0) {
            return 'Past event';
        }

        $hours = floor($timeDifference / 3600);
        $days = floor($hours / 24);

        if ($days > 0) {
            return $days . ' day' . ($days > 1 ? 's' : '') . ' away';
        } elseif ($hours > 0) {
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' away';
        } else {
            return 'Less than 1 hour away';
        }
    }

    private function isCancelledStatus($status)
    {
        return in_array(strtolower($status), ['cancelled_by_user', 'cancelled_by_admin']);
    }

    /**
     * Get available status options for filter
     */
    public function getStatusOptions()
    {
        return [
            '' => 'All',
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'completed' => 'Completed',
            'cancelled_by_user' => 'Cancelled'
        ];
    }

    /**
     * Reschedule an existing booking
     * 
     * @param int $booking_id
     * @param string $user_email
     * @param string $new_date
     * @param string $new_start_time
     * @param string $new_end_time
     * @param string $reason
     * @return array
     */
    public function rescheduleBooking($booking_id, $user_email, $new_date, $new_start_time, $new_end_time, $reason)
    {
        try {
            $this->db->beginTransaction();

            // First, verify the booking belongs to the user and can be rescheduled
            $stmt = $this->db->prepare("
            SELECT b.*, u.email 
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            WHERE b.id = ? AND u.email = ? AND b.status = 'approved'
        ");
            $stmt->execute([$booking_id, $user_email]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception('Booking not found or cannot be rescheduled');
            }

            // Check if the original booking is at least 24 hours away
            $original_datetime = new DateTime($booking['reservation_date'] . ' ' . $booking['start_time']);
            $now = new DateTime();
            $now->add(new DateInterval('PT24H'));

            if ($original_datetime < $now) {
                throw new Exception('Cannot reschedule - original booking is less than 24 hours away');
            }

            // Check if the new time slot is available
            $availability_check = $this->checkTimeSlotAvailability($new_date, $new_start_time, $new_end_time, $booking_id);
            if (!$availability_check['available']) {
                throw new Exception('Selected time slot is not available');
            }

            // Update the booking with new details
            $stmt = $this->db->prepare("
            UPDATE bookings 
            SET reservation_date = ?, 
                start_time = ?, 
                end_time = ?, 
                updated_at = NOW(),
                reschedule_count = COALESCE(reschedule_count, 0) + 1
            WHERE id = ?
        ");

            if (!$stmt->execute([$new_date, $new_start_time, $new_end_time, $booking_id])) {
                throw new Exception('Failed to update booking');
            }

            // Log the reschedule activity
            $stmt = $this->db->prepare("
            INSERT INTO booking_activities (booking_id, activity_type, description, created_at)
            VALUES (?, 'rescheduled', ?, NOW())
        ");

            $description = "Booking rescheduled from {$booking['reservation_date']} {$booking['start_time']}-{$booking['end_time']} to {$new_date} {$new_start_time}-{$new_end_time}. Reason: {$reason}";
            $stmt->execute([$booking_id, $description]);

            // Get updated booking details
            $stmt = $this->db->prepare("SELECT * FROM bookings WHERE id = ?");
            $stmt->execute([$booking_id]);
            $updated_booking = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Booking rescheduled successfully',
                'data' => [
                    'reference_number' => $updated_booking['reference_id'],
                    'new_date' => $new_date,
                    'new_start_time' => $new_start_time,
                    'new_end_time' => $new_end_time,
                    'booking_id' => $booking_id
                ]
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get available time slots for a specific date and duration
     * 
     * @param string $date
     * @param int $duration_minutes
     * @param int|null $exclude_booking_id
     * @return array
     */
    public function getAvailableTimeSlots($date, $duration_minutes, $exclude_booking_id = null)
    {
        try {
            // Define business hours (you can make these configurable)
            $business_start = '08:00';
            $business_end = '18:00';
            $slot_interval = 60; // 60 minutes intervals

            // Get existing bookings for the date
            $sql = "
            SELECT start_time, end_time 
            FROM bookings 
            WHERE reservation_date = ? 
            AND status IN ('approved', 'confirmed', 'pending')
        ";
            $params = [$date];

            // Exclude the current booking if rescheduling
            if ($exclude_booking_id) {
                $sql .= " AND id != ?";
                $params[] = $exclude_booking_id;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $existing_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Generate all possible time slots
            $available_slots = [];
            $current_time = new DateTime($date . ' ' . $business_start);
            $end_time = new DateTime($date . ' ' . $business_end);

            while ($current_time < $end_time) {
                $slot_start = $current_time->format('H:i');
                $slot_end_time = clone $current_time;
                $slot_end_time->add(new DateInterval('PT' . $duration_minutes . 'M'));

                // Check if the slot end time exceeds business hours
                if ($slot_end_time > $end_time) {
                    break;
                }

                $slot_end = $slot_end_time->format('H:i');

                // Check if this slot conflicts with existing bookings
                $is_available = true;
                foreach ($existing_bookings as $booking) {
                    if ($this->timeSlotsOverlap($slot_start, $slot_end, $booking['start_time'], $booking['end_time'])) {
                        $is_available = false;
                        break;
                    }
                }

                if ($is_available) {
                    $available_slots[] = [
                        'start_time' => $slot_start,
                        'end_time' => $slot_end,
                        'duration' => $duration_minutes
                    ];
                }

                // Move to next slot
                $current_time->add(new DateInterval('PT' . $slot_interval . 'M'));
            }

            return [
                'success' => true,
                'data' => $available_slots
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if two time slots overlap
     * 
     * @param string $start1
     * @param string $end1
     * @param string $start2
     * @param string $end2
     * @return bool
     */
    private function timeSlotsOverlap($start1, $end1, $start2, $end2)
    {
        $start1_time = strtotime($start1);
        $end1_time = strtotime($end1);
        $start2_time = strtotime($start2);
        $end2_time = strtotime($end2);

        return ($start1_time < $end2_time) && ($end1_time > $start2_time);
    }

    /**
     * Check if a specific time slot is available
     * 
     * @param string $date
     * @param string $start_time
     * @param string $end_time
     * @param int|null $exclude_booking_id
     * @return array
     */
    private function checkTimeSlotAvailability($date, $start_time, $end_time, $exclude_booking_id = null)
    {
        try {
            $sql = "
            SELECT COUNT(*) as conflict_count
            FROM bookings 
            WHERE reservation_date = ? 
            AND status IN ('approved', 'confirmed', 'pending')
            AND (
                (start_time < ? AND end_time > ?) OR
                (start_time < ? AND end_time > ?) OR
                (start_time >= ? AND end_time <= ?)
            )
        ";
            $params = [$date, $end_time, $start_time, $start_time, $start_time, $end_time, $start_time, $end_time];

            if ($exclude_booking_id) {
                $sql .= " AND id != ?";
                $params[] = $exclude_booking_id;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'available' => $result['conflict_count'] == 0,
                'conflicts' => (int) $result['conflict_count']
            ];

        } catch (Exception $e) {
            return [
                'available' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get reschedule history for a booking
     * 
     * @param int $booking_id
     * @return array
     */
    public function getRescheduleHistory($booking_id)
    {
        try {
            $stmt = $this->db->prepare("
            SELECT description, created_at
            FROM booking_activities 
            WHERE booking_id = ? AND activity_type = 'rescheduled'
            ORDER BY created_at DESC
        ");
            $stmt->execute([$booking_id]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $history
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if a booking can be rescheduled
     * 
     * @param int $booking_id
     * @param string $user_email
     * @return array
     */
    public function canRescheduleBooking($booking_id, $user_email)
    {
        try {
            $stmt = $this->db->prepare("
            SELECT b.*, u.email,
                   COALESCE(b.reschedule_count, 0) as reschedule_count,
                   TIMESTAMPDIFF(HOUR, NOW(), CONCAT(b.reservation_date, ' ', b.start_time)) as hours_until_event
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            WHERE b.id = ? AND u.email = ?
        ");
            $stmt->execute([$booking_id, $user_email]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                return [
                    'can_reschedule' => false,
                    'reason' => 'Booking not found'
                ];
            }

            // Check if booking status allows rescheduling
            if (!in_array($booking['status'], ['approved', 'confirmed'])) {
                return [
                    'can_reschedule' => false,
                    'reason' => 'Booking status does not allow rescheduling'
                ];
            }

            // Check if booking is at least 24 hours away
            if ($booking['hours_until_event'] < 24) {
                return [
                    'can_reschedule' => false,
                    'reason' => 'Cannot reschedule less than 24 hours before the event'
                ];
            }

            // Check reschedule limit (optional - you can set a limit)
            $max_reschedules = 3; // Allow up to 3 reschedules
            if ($booking['reschedule_count'] >= $max_reschedules) {
                return [
                    'can_reschedule' => false,
                    'reason' => 'Maximum reschedule limit reached'
                ];
            }

            return [
                'can_reschedule' => true,
                'reschedule_count' => (int) $booking['reschedule_count'],
                'hours_until_event' => (int) $booking['hours_until_event']
            ];

        } catch (Exception $e) {
            return [
                'can_reschedule' => false,
                'reason' => $e->getMessage()
            ];
        }
    }
}