<?php
/**
 * components/empty-state.php
 */

function renderEmptyState($icon, $title, $message) {
    ?>
    <div class="empty-state">
        <i class="fas fa-<?= htmlspecialchars($icon) ?>"></i>
        <h4><?= htmlspecialchars($title) ?></h4>
        <p><?= htmlspecialchars($message) ?></p>
    </div>
    <?php
}