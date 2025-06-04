<?php
header('Content-Type: application/json');

$host = 'localhost';
$db = 'db_pmji';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

if (isset($_GET['email'])) {
    $email = trim($_GET['email']);

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo json_encode(['status' => 'exists']);
        } else {
            echo json_encode(['status' => 'available']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Email parameter missing.']);
}
?>