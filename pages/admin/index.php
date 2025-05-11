<?php
session_start();
// grab and then clear any login‐error flash message
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- End Bootstrap CSS -->

  <!-- Custom CSS -->
  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/index.css">
  <!-- End Custom CSS -->
</head>
s
<body>
  <div class="admin-login-wrapper">
    <form id="adminLoginForm" action="process_admin_login.php" method="POST">
      <h1>Admin Login</h1>

      <?php if ($error): ?>
        <div id="adminLoginError" class="alert alert-danger">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="admin-input-box">
        <label for="admin-username">ID</label>
        <input type="text" id="admin-username" name="username" placeholder="ID" required>
        <i class='bx bxs-user'></i>
      </div>
      <div class="admin-input-box password-box">
        <label for="admin-password">Password</label>
        <input type="password" id="admin-password" name="password" placeholder="Password" required>
        <i class='bx bxs-lock-alt'></i>
      </div>
      <button type="submit" class="admin-btn">Login</button>
    </form>
  </div>
</body>

</html>