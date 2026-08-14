<?php
/**
 * A customer's records, read-only, for staff.
 *
 * Reuses the same table partial as the customer's own listing, so the two
 * cannot drift apart.
 *
 * @var array $user
 * @var array $rows
 */

defined('APP_BOOTSTRAPPED') || exit;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h4 mb-0"><?= te('property.title') ?></h1>
        <p class="text-body-secondary small mb-0">
            <?= e($user['name']) ?> · <?= e($user['email']) ?>
        </p>
    </div>
    <a href="<?= e(url('admin/users/' . $user['id'])) ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i><?= te('action.back') ?>
    </a>
</div>

<?php if (!$rows): ?>
    <div class="empty-state border rounded-3">
        <i class="bi bi-folder2-open"></i>
        <p class="mt-3 mb-0 text-body-secondary"><?= te('property.none') ?></p>
    </div>
<?php else: ?>
    <?= App\View::renderPartial('partials.property-table', [
            'rows'     => $rows,
            'basePath' => $basePath,
            'active_f' => [],
            'sort'     => 'seq',
            'dir'      => 'ASC',
            'readonly' => true,
        ]) ?>

    <?= App\View::renderPartial('partials.pagination', [
            'basePath' => $basePath,
            'page'     => $page,
            'lastPage' => $lastPage,
            'params'   => [],
            'total'    => $total,
            'per'      => $per,
        ]) ?>
<?php endif; ?>
