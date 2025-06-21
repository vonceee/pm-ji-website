<?php

// controllers/BookingController.php
namespace controllers;

use Models\BookingModel;

class BookingController
{
    private $model;
    private $itemsPerPage = 6; // define number of items per page for pagination

    public function __construct()
    {
        $this->model = new BookingModel();
    }

    /**
     * get filtered bookings for all statuses with pagination, date-filtering
     *
     * @param string|null $dateFrom define start date for filtering (optional)
     * @param string|null $dateTo define end date for filtering (optional)
     * @param int $page track current page number for pagination (default: 1)
     * @return array return associative array of bookings grouped by status
     */
    public function getFilteredBookings($dateFrom, $dateTo, $page = 1)
    {
        $filters = [];

        // add date_from filter if provided (else if no date filtering is set, will return all bookings)
        if (!empty($dateFrom))
            $filters['date_from'] = $dateFrom;
        if (!empty($dateTo))
            $filters['date_to'] = $dateTo;

        // fetch paginated bookings for each status
        $pendingBookings = $this->model->getBookingsByStatus("= 'pending'", $filters, $page, $this->itemsPerPage);
        $approvedBookings = $this->model->getBookingsByStatus("= 'approved'", $filters, $page, $this->itemsPerPage);
        $historyBookings = $this->model->getBookingsByStatus("NOT IN ('pending', 'approved')", $filters, $page, $this->itemsPerPage);

        // return bookings grouped by status
        return [
            'pending' => $pendingBookings,
            'approved' => $approvedBookings,
            'history' => $historyBookings,
        ];
    }

    /**
     * get total counts of bookings for each status, including date-filtering.
     *
     * @param string|null $dateFrom define start date for date-filtering (optional)
     * @param string|null $dateTo define end date for date-filtering (optional)
     * @return array return associative array of counts grouped by status
     */
    public function getBookingsCounts($dateFrom, $dateTo)
    {
        $filters = [];

        // add date_from filter if provided (else if no date filtering is set, will return all bookings)
        if (!empty($dateFrom))
            $filters['date_from'] = $dateFrom;
        if (!empty($dateTo))
            $filters['date_to'] = $dateTo;

        return [
            'pending' => $this->model->getBookingsCountByStatus("= 'pending'", $filters),
            'approved' => $this->model->getBookingsCountByStatus("= 'approved'", $filters),
            'history' => $this->model->getBookingsCountByStatus("NOT IN ('pending', 'approved')", $filters),
        ];
    }

    /**
     * prepare pagination data for the view.
     *
     * @param int $total define total number of items
     * @param int $currentPage track current page number
     * @param int|null $itemsPerPage define number of items per page (optional, defaults to class property)
     * @return array return pagination data including; page numbers, navigation flags
     */
    public function getPaginationData($total, $currentPage, $itemsPerPage = null)
    {
        $itemsPerPage = $itemsPerPage ?? $this->itemsPerPage;
        $totalPages = ceil($total / $itemsPerPage); // calculate total number of pages

        return [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'items_per_page' => $itemsPerPage,
            'total_items' => $total,
            'has_previous' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages,
            'previous_page' => $currentPage > 1 ? $currentPage - 1 : null,
            'next_page' => $currentPage < $totalPages ? $currentPage + 1 : null
        ];
    }
}