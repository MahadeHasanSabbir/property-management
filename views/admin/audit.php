<?php
/**
 * Audit log.
 *
 * @var array  $rows
 * @var array  $actions
 * @var string $action
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Csrf;

$params = array_filter(['action' => $action]);

/** Destructive actions stand out, since they are what an audit is usually for. */
$tone = static function (string $name): string {
    if (str_contains($name, 'delete')) {
        return 'text-bg-danger';
    }
    if (str_contains($name, 'create') || str_contains($name, 'register')) {
        return 'text-bg-success';
    }
    if (str_contains($name, 'password') || str_contains($name, 'reset')) {
        return 'text-bg-warning';
    }
    return 'text-bg-secondary';
};
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0"><?= te('admin.audit_log') ?></h1>

    <form method="post" action="<?= e(url('admin/audit-log')) ?>" class="d-flex gap-2" data-no-lock>
        <?= Csrf::field() ?>
        <select class="form-select form-select-sm" name="action" data-submit-on-change>
            <option value=""><?= te('admin.audit_action') ?></option>
            <?php foreach ($actions as $name): ?>
                <option value="<?= e($name) ?>" <?= $action === $name ? 'selected' : '' ?>>
                    <?= e($name) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <noscript><button type="submit" class="btn btn-sm btn-outline-secondary"><?= te('action.search') ?></button></noscript>
    </form>
</div>

<?php if (!$rows): ?>
    <div class="empty-state border rounded-3">
        <i class="bi bi-clock-history"></i>
        <p class="mt-3 mb-0 text-body-secondary"><?= te('admin.no_audit') ?></p>
    </div>
<?php else: ?>
    <div class="table-scroll">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th scope="col"><?= te('admin.audit_when') ?></th>
                    <th scope="col"><?= te('admin.audit_actor') ?></th>
                    <th scope="col"><?= te('admin.audit_action') ?></th>
                    <th scope="col"><?= te('admin.audit_target') ?></th>
                    <th scope="col">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="text-nowrap small"><?= e($row['created_at']) ?></td>
                        <td class="small">
                            <?php /* actor_label is a snapshot taken when the entry
                                     was written, so it stays readable after the
                                     account is deleted. */ ?>
                            <?= e($row['actor_label'] ?? '—') ?>
                        </td>
                        <td>
                            <span class="badge <?= e($tone($row['action'])) ?>"><?= e($row['action']) ?></span>
                        </td>
                        <td class="small record-value">
                            <?php if ($row['entity']): ?>
                                <?= e($row['entity']) ?><?= $row['entity_id'] ? ' #' . e($row['entity_id']) : '' ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                            <?php if ($row['meta']): ?>
                                <div class="text-body-secondary text-truncate" style="max-width:22rem">
                                    <?= e($row['meta']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="small record-value"><?= e($row['ip'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?= App\View::renderPartial('partials.pagination', [
            'basePath' => $basePath,
            'page'     => $page,
            'lastPage' => $lastPage,
            'params'   => $params,
            'total'    => $total,
            'per'      => $per,
        ]) ?>
<?php endif; ?>
