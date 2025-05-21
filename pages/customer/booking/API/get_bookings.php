<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "db_pmji";
$conn = new mysqli($host, $user, $password, $database);

$result = $conn->query("SELECT reservation_date, start_time, end_time FROM tbl_bookings");
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $date = $row['reservation_date'];
    $start = $row['start_time'];
    $end = $row['end_time'];
    if (!isset($bookings[$date])) $bookings[$date] = [];
    $bookings[$date][] = ['start' => $start, 'end' => $end];
}
header('Content-Type: application/json');
echo json_encode($bookings);

?>