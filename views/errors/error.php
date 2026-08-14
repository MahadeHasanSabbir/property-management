<?php
/**
 * Shared error page for every HTTP status the app raises.
 *
 * @var int    $status
 * @var string $title
 * @var string $message
 */

defined('APP_BOOTSTRAPPED') || exit;
?>
<div class="empty-state">
    <i class="bi bi-<?= $status === 404 ? 'compass' : ($status === 403 ? 'shield-lock' : 'exclamation-triangle') ?>"></i>
    <h1 class="h3 mt-3"><?= e($title) ?></h1>
    <p class="text-body-secondary"><?= e($message) ?></p>
    <p class="text-body-secondary small">HTTP <?= (int) $status ?></p>
    <a class="btn btn-primary mt-2" href="<?= e(url('')) ?>">
        <i class="bi bi-house me-1"></i><?= te('error.go_home') ?>
    </a>
</div>
