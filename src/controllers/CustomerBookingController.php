<?php
namespace Controllers;

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/src/models/CustomerBookingModel.php';
use models\CustomerBookingModel;

class CustomerBookingController
{
    private $model;

    public function __construct()
    {
        $this->model = new CustomerBookingModel();
    }

    public function getUserBookings($userEmail, $page = 1, $limit = 3, $search = '', $filter_date = '', $filter_status = '')
    {
        $userId = $this->model->getUserIdByEmail($userEmail);
        if (!$userId) {
            throw new \Exception('User not found.');
        }
        $offset = ($page - 1) * $limit;
        $total = $this->model->countBookings($userId, $search, $filter_date, $filter_status);
        $bookings = $this->model->getBookings($userId, $limit, $offset, $search, $filter_date, $filter_status);
        return [
            'bookings' => $bookings,
            'total' => $total,
            'limit' => $limit,
            'page' => $page
        ];
    }
}