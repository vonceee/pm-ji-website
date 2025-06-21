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
}