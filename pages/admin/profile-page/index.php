<?php

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: /NEW-PM-JI-RESERVIFY/pages/admin/index.php");
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

$pdo = Database::getConnection();
$admin_id = $_SESSION['admin_id'];
$message = '';
$error = '';

// handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $new_username = trim($_POST['username']);

        // validate username
        if (empty($new_username)) {
            $error = 'username cannot be empty';
        } elseif (strlen($new_username) < 3) {
            $error = 'username must be at least 3 characters long';
        } else {
            // check if username already exists (excluding current admin)
            $check_stmt = $pdo->prepare("SELECT id FROM tbl_admin WHERE username = ? AND id != ?");
            $check_stmt->execute([$new_username, $admin_id]);

            if ($check_stmt->fetch()) {
                $error = 'Username already exists';
            } else {
                // update username
                $update_stmt = $pdo->prepare("UPDATE tbl_admin SET username = ? WHERE id = ?");
                if ($update_stmt->execute([$new_username, $admin_id])) {
                    $_SESSION['admin_username'] = $new_username;
                    $message = 'Profile Updated Successfully!';
                } else {
                    $error = 'Failed to Update Profile';
                }
            }
        }
    }

    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // validate passwords
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'All password fields are required';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters long';
        } else {
            // verify current password
            $verify_stmt = $pdo->prepare("SELECT password FROM tbl_admin WHERE id = ?");
            $verify_stmt->execute([$admin_id]);
            $admin_data = $verify_stmt->fetch();

            if (password_verify($current_password, $admin_data['password'])) {
                // update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("UPDATE tbl_admin SET password = ? WHERE id = ?");

                if ($update_stmt->execute([$hashed_password, $admin_id])) {
                    $message = 'Password changed successfully!';
                } else {
                    $error = 'Failed to change password';
                }
            } else {
                $error = 'Current password is incorrect';
            }
        }
    }
}

// fetch current admin data
$stmt = $pdo->prepare("SELECT username, is_active FROM tbl_admin WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

if (!$admin) {
    session_destroy();
    header('Location: /NEW-PM-JI-RESERVIFY/pages/admin/index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Dashboard</title>
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/pages/admin/profile-page/profile-page.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="profile-container">
        <!-- Header Section -->
        <div class="profile-header">
            <div class="header-content">
                <div class="profile-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="profile-info">
                    <h1>Admin Profile</h1>
                    <p class="username">@<?php echo htmlspecialchars($admin['username']); ?></p>
                    <div class="status-badge <?php echo $admin['is_active'] ? 'active' : 'inactive'; ?>">
                        <i class="fas fa-circle"></i>
                        <?php echo $admin['is_active'] ? 'Active' : 'Inactive'; ?>
                    </div>
                </div>
            </div>
            <div class="header-actions">
                <a href="../dashboard/" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="profile-content">
            <!-- Profile Information Card -->
            <div class="profile-card">
                <div class="card-header">
                    <h2><i class="fas fa-user-edit"></i> Profile Information</h2>
                </div>
                <div class="card-body">
                    <form method="POST" class="profile-form">
                        <div class="form-group">
                            <label for="username">
                                <i class="fas fa-user"></i>
                                Username
                            </label>
                            <input type="text" id="username" name="username"
                                value="<?php echo htmlspecialchars($admin['username']); ?>" required minlength="3"
                                maxlength="50">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-shield-alt"></i>
                                Account Status
                            </label>
                            <div class="status-display <?php echo $admin['is_active'] ? 'active' : 'inactive'; ?>">
                                <i class="fas fa-circle"></i>
                                <?php echo $admin['is_active'] ? 'Active Account' : 'Inactive Account'; ?>
                            </div>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Update Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- Password Change Card -->
            <div class="profile-card">
                <div class="card-header">
                    <h2><i class="fas fa-lock"></i> Change Password</h2>
                </div>
                <div class="card-body">
                    <form method="POST" class="password-form">
                        <div class="form-group">
                            <label for="current_password">
                                <i class="fas fa-key"></i>
                                Current Password
                            </label>
                            <div class="password-input">
                                <input type="password" id="current_password" name="current_password" required>
                                <button type="button" class="toggle-password"
                                    onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password">
                                <i class="fas fa-lock"></i>
                                New Password
                            </label>
                            <div class="password-input">
                                <input type="password" id="new_password" name="new_password" required minlength="6">
                                <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-hint">Password must be at least 6 characters long</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-lock"></i>
                                Confirm New Password
                            </label>
                            <div class="password-input">
                                <input type="password" id="confirm_password" name="confirm_password" required
                                    minlength="6">
                                <button type="button" class="toggle-password"
                                    onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-shield-alt"></i>
                            Change Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Account Information Card -->
            <div class="profile-card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> Account Information</h2>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-id-badge"></i>
                                Admin ID
                            </div>
                            <div class="info-value">#<?php echo $admin_id; ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-user-tag"></i>
                                Role
                            </div>
                            <div class="info-value">System Administrator</div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-calendar-alt"></i>
                                Session Started
                            </div>
                            <div class="info-value"><?php echo date('M d, Y - H:i:s'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });

        // password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function () {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;

            if (confirmPassword && newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not MATCH.');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>

</html>