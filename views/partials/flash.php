<?php
/**
 * One-shot notices. Rendered once per page by the layout.
 *
 * Legacy note: flash handling used to be tangled up with session teardown —
 * logout wrote $_SESSION['success'] and the login page's rendering of that
 * message was what actually destroyed the session. Here a flash is only a
 * message.
 */

defined('APP_BOOTSTRAPPED') || exit;

$flashes = take_flashes();
if (!$flashes) {
    return;
}

$icons = [
    'success' => 'check-circle-fill',
    'danger'  => 'exclamation-octagon-fill',
    'warning' => 'exclamation-triangle-fill',
    'info'    => 'info-circle-fill',
];
?>
<?php foreach ($flashes as $flash): ?>
    <?php $type = $flash['type'] ?? 'info'; ?>
    <div class="alert alert-<?= e($type) ?> alert-dismissible fade show d-flex align-items-start gap-2"
         role="alert"<?= $type === 'success' ? ' data-auto-dismiss' : '' ?>>
        <i class="bi bi-<?= e($icons[$type] ?? 'info-circle-fill') ?> flex-shrink-0 mt-1"></i>
        <div><?= e($flash['message'] ?? '') ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="<?= te('action.close') ?>"></button>
    </div>
<?php endforeach; ?>
