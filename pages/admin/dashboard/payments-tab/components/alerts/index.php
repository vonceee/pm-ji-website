<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo $_SESSION['success_message'];
        unset($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?php echo $_SESSION['error_message'];
        unset($_SESSION['error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>