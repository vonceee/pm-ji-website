<?php
/**
 * BookingController - handles business logic for booking management
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/pages/admin/dashboard/bookings/classes/BookingService.php';

class BookingController
{
    private $bookingService;
    private $data;

    public function __construct()
    {
        $this->bookingService = new BookingService();
        $this->data = [];
    }

    /**
     * initialize all booking data for the dashboard
     * @param array $filters
     * @return array
     */
    public function initializeBookingData(array $filters = []): array
    {
        try {
            // fetch all booking data
            if (empty($filters)) {
                $this->data['pending_bookings'] = $this->bookingService->getPendingBookings();
                $this->data['approved_bookings'] = $this->bookingService->getApprovedBookings();
                $this->data['history_bookings'] = $this->bookingService->getBookingHistory();
            } else {
                // apply filters if provided
                $this->data['pending_bookings'] = $this->bookingService->getBookingsByStatus('pending', $filters);
                $this->data['approved_bookings'] = $this->bookingService->getBookingsByStatus('approved', $filters);
                $historyFilters = $filters;
                $this->data['history_bookings'] = $this->getFilteredHistoryBookings($historyFilters);
            }

            // calculate counts
            $this->data['counts'] = [
                'pending' => count($this->data['pending_bookings']),
                'approved' => count($this->data['approved_bookings']),
                'history' => count($this->data['history_bookings'])
            ];

            // get overall statistics
            $this->data['stats'] = $this->bookingService->getBookingStats();

            return $this->data;

        } catch (Exception $e) {
            error_log("Error initializing booking data: " . $e->getMessage());

            // return empty data structure on error
            return [
                'pending_bookings' => [],
                'approved_bookings' => [],
                'history_bookings' => [],
                'counts' => ['pending' => 0, 'approved' => 0, 'history' => 0],
                'stats' => [],
                'error' => 'Failed to load booking data'
            ];
        }
    }

    /**
     * get filtered history bookings (completed and cancelled)
     * @param array $filters
     * @return array
     */
    private function getFilteredHistoryBookings(array $filters): array
    {
        $completed = $this->bookingService->getBookingsByStatus('completed', $filters);
        $cancelled = $this->bookingService->getBookingsByStatus('cancelled', $filters);

        // merge and sort by updated_at DESC
        $history = array_merge($completed, $cancelled);
        usort($history, function ($a, $b) {
            return strtotime($b['updated_at']) - strtotime($a['updated_at']);
        });

        return $history;
    }

    /**
     * get booking data for a specific tab
     * @param string $tab
     * @param array $filters
     * @return array
     */
    public function getBookingDataForTab(string $tab, array $filters = []): array
    {
        switch ($tab) {
            case 'pending':
                return $this->bookingService->getBookingsByStatus('pending', $filters);
            case 'approved':
                return $this->bookingService->getBookingsByStatus('approved', $filters);
            case 'history':
                return $this->getFilteredHistoryBookings($filters);
            default:
                return [];
        }
    }

    /**
     * process booking action (approve, reject, complete, etc.)
     * @param int $bookingId
     * @param string $action
     * @param array $additionalData
     * @return array
     */
    public function processBookingAction(int $bookingId, string $action, array $additionalData = []): array
    {
        try {
            // validate booking exists
            $booking = $this->bookingService->getBookingById($bookingId);
            if (!$booking) {
                return [
                    'success' => false,
                    'message' => 'Booking not found'
                ];
            }

            // process action based on type
            switch ($action) {
                case 'approve':
                    return $this->approveBooking($bookingId, $booking);
                case 'reject':
                    return $this->rejectBooking($bookingId, $booking);
                case 'complete':
                    return $this->completeBooking($bookingId, $booking);
                case 'cancel':
                    return $this->cancelBooking($bookingId, $booking, $additionalData);
                default:
                    return [
                        'success' => false,
                        'message' => 'Invalid action specified'
                    ];
            }

        } catch (Exception $e) {
            error_log("Error processing booking action: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while processing the booking'
            ];
        }
    }

    /**
     * approve a booking
     * @param int $bookingId
     * @param array $booking
     * @return array
     */
    private function approveBooking(int $bookingId, array $booking): array
    {
        if ($booking['status'] !== 'pending') {
            return [
                'success' => false,
                'message' => 'Only pending bookings can be approved'
            ];
        }

        $success = $this->bookingService->updateBookingStatus($bookingId, 'approved');

        return [
            'success' => $success,
            'message' => $success ? 'Booking approved successfully' : 'Failed to approve booking'
        ];
    }

    /**
     * reject a booking
     * @param int $bookingId
     * @param array $booking
     * @return array
     */
    private function rejectBooking(int $bookingId, array $booking): array
    {
        if ($booking['status'] !== 'pending') {
            return [
                'success' => false,
                'message' => 'Only pending bookings can be rejected'
            ];
        }

        $success = $this->bookingService->updateBookingStatus($bookingId, 'cancelled');

        return [
            'success' => $success,
            'message' => $success ? 'Booking rejected successfully' : 'Failed to reject booking'
        ];
    }

    /**
     * complete a booking
     * @param int $bookingId
     * @param array $booking
     * @return array
     */
    private function completeBooking(int $bookingId, array $booking): array
    {
        if ($booking['status'] !== 'approved') {
            return [
                'success' => false,
                'message' => 'Only approved bookings can be completed'
            ];
        }

        $success = $this->bookingService->updateBookingStatus($bookingId, 'completed');

        return [
            'success' => $success,
            'message' => $success ? 'Booking completed successfully' : 'Failed to complete booking'
        ];
    }

    /**
     * cancel a booking
     * @param int $bookingId
     * @param array $booking
     * @param array $additionalData
     * @return array
     */
    private function cancelBooking(int $bookingId, array $booking, array $additionalData): array
    {
        if (in_array($booking['status'], ['completed', 'cancelled'])) {
            return [
                'success' => false,
                'message' => 'Cannot cancel a booking that is already completed or cancelled'
            ];
        }

        $success = $this->bookingService->updateBookingStatus($bookingId, 'cancelled');

        return [
            'success' => $success,
            'message' => $success ? 'Booking cancelled successfully' : 'Failed to cancel booking'
        ];
    }

    /**
     * get formatted booking data for display
     * @param array $booking
     * @return array
     */
    public function formatBookingForDisplay(array $booking): array
    {
        return [
            'id' => $booking['id'],
            'reference_id' => $booking['reference_id'],
            'customer_name' => trim($booking['first_name'] . ' ' . $booking['last_name']),
            'customer_email' => $booking['email'],
            'customer_phone' => $booking['phone'],
            'event_type' => $booking['event_type'],
            'reservation_date' => $booking['reservation_date'],
            'formatted_date' => date('M d, Y', strtotime($booking['reservation_date'])),
            'start_time' => $booking['start_time'],
            'end_time' => $booking['end_time'],
            'duration' => $booking['duration'],
            'city' => $booking['city'],
            'status' => $booking['status'],
            'payment_status' => $booking['payment_status'] ?? 'unpaid',
            'amount_paid' => $booking['amount_paid'] ?? 0,
            'balance' => $booking['balance'] ?? 0,
            'payment_method' => $booking['payment_method'],
            'payment_type' => $booking['payment_type'],
            'refund_amount' => $booking['refund_amount'] ?? 0,
            'created_at' => $booking['created_at'],
            'updated_at' => $booking['updated_at']
        ];
    }

    /**
     * get all data for the view
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}