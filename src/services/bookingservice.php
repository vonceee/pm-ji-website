<?php
// src/services/bookingservice.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/Booking.php';
require_once __DIR__ . '/../Utils/Pagination.php';

use Config\Database;

class BookingService
{
    private $pdo;
    private $bookingModel;
    private $pagination;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->bookingModel = new Booking();
        $this->pagination = new Pagination();
    }

    public function getUserBookings($userId, $filters)
    {
        try {
            // Build WHERE clause and parameters
            $whereConditions = ["b.user_id = :user_id"];
            $params = [':user_id' => $userId];

            if (!empty($filters['search'])) {
                $whereConditions[] = "(b.event_type LIKE :search1 OR b.reference_number LIKE :search2)";
                $params[':search1'] = "%{$filters['search']}%";
                $params[':search2'] = "%{$filters['search']}%";
            }

            if (!empty($filters['filter_date'])) {
                $whereConditions[] = "b.reservation_date = :filter_date";
                $params[':filter_date'] = $filters['filter_date'];
            }

            $whereClause = implode(' AND ', $whereConditions);

            // Get total count for pagination
            $totalBookings = $this->getTotalBookings($whereClause, $params);

            // Get bookings with pagination
            $bookings = $this->getBookingsWithPagination($whereClause, $params, $filters);

            return [
                'result' => $bookings,
                'totalBookings' => $totalBookings,
                'currentPage' => $filters['page'],
                'limit' => $filters['limit']
            ];

        } catch (Exception $e) {
            error_log("Error in getUserBookings: " . $e->getMessage());
            return [
                'result' => [],
                'totalBookings' => 0,
                'currentPage' => 1,
                'limit' => $filters['limit']
            ];
        }
    }

    private function getTotalBookings($whereClause, $params)
    {
        $countQuery = "SELECT COUNT(*) FROM tbl_bookings b WHERE $whereClause";
        $stmt = $this->pdo->prepare($countQuery);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    private function getBookingsWithPagination($whereClause, $params, $filters)
    {
        $offset = ($filters['page'] - 1) * $filters['limit'];

        $query = "SELECT 
            b.event_type, b.duration, b.reservation_date, b.start_time, b.end_time,
            b.street_address, b.barangay, b.city, b.reference_number, b.reference_id,
            b.status, b.created_at, b.full_address,
            p.payment_method, p.payment_type, p.status AS payment_status, 
            p.amount_paid, p.balance
        FROM tbl_bookings b
        LEFT JOIN tbl_payments p ON b.id = p.booking_id
        WHERE $whereClause
        ORDER BY b.created_at DESC
        LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($query);

        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        // Bind pagination parameters
        $stmt->bindValue(':limit', $filters['limit'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelBooking($referenceId)
    {
        try {
            $this->pdo->beginTransaction();

            // Check if booking exists and can be cancelled
            $booking = $this->bookingModel->getByReferenceId($referenceId);
            if (!$booking) {
                throw new Exception("Booking not found");
            }

            if ($booking['status'] === 'cancelled_by_user') {
                throw new Exception("Booking is already cancelled");
            }

            // Update booking status
            $result = $this->bookingModel->updateStatus($referenceId, 'cancelled_by_user');

            if (!$result) {
                throw new Exception("Failed to update booking status");
            }

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Booking cancelled successfully'];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error cancelling booking: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getBookingDetails($referenceId)
    {
        return $this->bookingModel->getBookingWithPayment($referenceId);
    }

    public function searchBookings($userId, $searchTerm)
    {
        return $this->bookingModel->searchUserBookings($userId, $searchTerm);
    }
}