<?php

namespace models;

use config\database;
use PDO;

class CustomerBookingModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function getUserIdByEmail($email)
    {
        $stmt = $this->pdo->prepare('SELECT id FROM tbl_users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn();
    }

    public function countBookings($userId, $search = '', $filter_date = '', $filter_status = '')
    {
        $where = 'b.user_id = :user_id';
        $params = [':user_id' => $userId];

        if ($search !== '') {
            $where .= ' AND (b.event_type LIKE :search1 OR b.reference_number LIKE :search2)';
            $params[':search1'] = "%{$search}%";
            $params[':search2'] = "%{$search}%";
        }
        if ($filter_date !== '') {
            $where .= ' AND b.reservation_date = :filter_date';
            $params[':filter_date'] = $filter_date;
        }
        if ($filter_status !== '') {
            $where .= ' AND b.status = :filter_status';
            $params[':filter_status'] = $filter_status;
        }

        $sql = "SELECT COUNT(*) FROM tbl_bookings b WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getBookings($userId, $limit, $offset, $search = '', $filter_date = '', $filter_status = '')
    {
        $where = 'b.user_id = :user_id';
        $params = [':user_id' => $userId];

        if ($search !== '') {
            $where .= ' AND (b.event_type LIKE :search1 OR b.reference_number LIKE :search2)';
            $params[':search1'] = "%{$search}%";
            $params[':search2'] = "%{$search}%";
        }
        if ($filter_date !== '') {
            $where .= ' AND b.reservation_date = :filter_date';
            $params[':filter_date'] = $filter_date;
        }
        if ($filter_status !== '') {
            $where .= ' AND b.status = :filter_status';
            $params[':filter_status'] = $filter_status;
        }

        $sql = <<<SQL
SELECT
    b.id, b.event_type, b.duration, b.reservation_date, b.start_time, b.end_time,
    b.street_address, b.barangay, b.city, b.reference_number, b.reference_id, b.status,
    b.created_at, b.full_address,
    p.payment_method, p.payment_type, p.status AS payment_status, p.amount_paid, p.balance,
    p.payment_date, p.payment_screenshot_path, p.payment_screenshot_thumbnail,
    c.reason AS cancellation_reason, c.cancelled_at, c.refund_status, c.refund_amount, c.admin_notes AS cancellation_admin_notes
FROM tbl_bookings b
LEFT JOIN tbl_payments p ON b.id = p.booking_id
LEFT JOIN tbl_cancellations c ON b.id = c.booking_id
WHERE {$where}
ORDER BY b.created_at DESC
LIMIT :limit OFFSET :offset
SQL;

        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            if (in_array($key, [':limit', ':offset'], true))
                continue;
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}