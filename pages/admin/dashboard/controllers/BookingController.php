<?php

// controllers/BookingController.php
namespace Controllers;

use Models\BookingModel;

class BookingController {
    private $model;

    public function __construct() {
        $this->model = new BookingModel();
    }

    public function getFilteredBookings($dateFrom, $dateTo) {
        $filters = [];
        if (!empty($dateFrom)) $filters['date_from'] = $dateFrom;
        if (!empty($dateTo)) $filters['date_to'] = $dateTo;

        return [
            'pending' => $this->model->getBookingsByStatus("= 'pending'", $filters),
            'approved' => $this->model->getBookingsByStatus("= 'approved'", $filters),
            'history' => $this->model->getBookingsByStatus("NOT IN ('pending', 'approved')", $filters),
        ];
    }
}

?>