<?php
session_start();

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
    die('Database connection failed: ' . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // reject non-POST
    header('Location: index.php');
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Please enter both username and password.';
    header('Location: index.php');
    exit();
}

$sql = "SELECT id, username, password, is_active FROM tbl_admin WHERE username = :username";
$stmt = $pdo->prepare($sql);
$stmt->execute([':username' => $username]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($password, $admin['password'])) {
    // generic message so we don't reveal which part failed
    $_SESSION['login_error'] = 'Invalid ID/Password.';
    header('Location: index.php');
    exit();
}

if (!$admin['is_active']) {
    $_SESSION['login_error'] = 'Your account is inactive. Please contact support.';
    header('Location: index.php');
    exit();
}

// success!
$_SESSION['admin_id'] = $admin['id'];
$_SESSION['admin_username'] = $admin['username'];
header('Location: /NEW-PM-JI-RESERVIFY/pages/admin/dashboard/index.php');
exit();

?>