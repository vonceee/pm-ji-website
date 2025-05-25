<?php

// get the logged-in user's email from session $userEmail=$_SESSION['user_email']; // fetch user information query
$stmt = $pdo->prepare("SELECT first_name, middle_name, last_name, contact_no, email FROM tbl_users WHERE email =
    :email");
$stmt->execute([':email' => $userEmail]);
$user = $stmt->fetch();

if (!$user) {
    die('user not found.');
}

// get the user's ID based on email using PDO
$userIdQuery = "SELECT id FROM tbl_users WHERE email = :email";
$stmt2 = $pdo->prepare($userIdQuery);
$stmt2->execute([':email' => $userEmail]);
$userRow = $stmt2->fetch();

if (!$userRow) {
    die("user not found.");
}
?>

<!-- Left: 4-column profile card -->
<div class="card profile-card shadow-sm">
    <div class="card-body text-center">
        <i class="fas fa-user-circle fa-2x" style="font-size: 4rem;"></i>
        <h4 class="card-title">
            <br>
            <?= htmlspecialchars($user['first_name']) ?>
            <?php if (!empty($user['middle_name'])): ?>
                <?= htmlspecialchars(strtoupper(substr($user['middle_name'], 0, 1))) ?>.
            <?php endif; ?>
            <?= htmlspecialchars($user['last_name']) ?>
        </h4>
        <p class="mb-1" style="font-size: 0.8rem;">
            <?= htmlspecialchars($user['email']) ?>
        </p>
        <p class="mb-1" style="font-size: 0.8rem;">
            <?= htmlspecialchars($user['contact_no']) ?>
        </p>
        <hr>
    </div>
</div>