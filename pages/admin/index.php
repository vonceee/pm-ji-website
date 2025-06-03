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
  <title>Admin Login - Reservify</title>
  
  <!-- Preload Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  
  <!-- Boxicons for icons -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
  <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/index.css">

  </head>

<body>
  <main class="admin-login-wrapper" role="main">
    <form id="adminLoginForm" action="process_admin_login.php" method="POST" novalidate>
      <h1>Admin Login</h1>

      <?php if ($error): ?>
        <div id="adminLoginError" class="alert alert-danger" role="alert" aria-live="polite">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="admin-input-box">
        <label for="admin-username">Admin ID</label>
        <input 
          type="text" 
          id="admin-username" 
          name="username" 
          placeholder="Enter your Admin ID" 
          required 
          autocomplete="username"
          aria-describedby="username-help"
          maxlength="50"
        >
      </div>

      <div class="admin-input-box password-box">
        <label for="admin-password">Password</label>
        <input 
          type="password" 
          id="admin-password" 
          name="password" 
          placeholder="Enter your Password" 
          required 
          autocomplete="current-password"
          aria-describedby="password-help"
          minlength="6"
        >
      </div>

      <button type="submit" class="admin-btn btn-primary" id="loginBtn">
        <span class="btn-text">Sign In</span>
      </button>
    </form>
  </main>

  <!-- JavaScript for enhanced UX -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('adminLoginForm');
      const loginBtn = document.getElementById('loginBtn');
      const btnText = loginBtn.querySelector('.btn-text');
      
      // add loading state on form submit
      form.addEventListener('submit', function() {
        loginBtn.disabled = true;
        btnText.textContent = 'Signing in...';
      });
      
      // auto-focus first input if no error
      <?php if (!$error): ?>
      document.getElementById('admin-username').focus();
      <?php endif; ?>
      
      // remove error message on input focus
      const errorDiv = document.getElementById('adminLoginError');
      if (errorDiv) {
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => {
          input.addEventListener('focus', function() {
            errorDiv.style.opacity = '0.5';
          });
        });
      }
    });
  </script>
</body>

</html>