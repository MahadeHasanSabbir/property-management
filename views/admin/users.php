<?php
/**
 * User list.
 *
 * The record count is a live subquery rather than a stored counter, which
 * would drift away from the rows it describes.
 *
 * @var array $rows
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Auth;
use App\Csrf;
use App\Permission;

$statusClass = [
    'active'    => 'text-bg-success',
    'pending'   => 'text-bg-warning',
    'suspended' => 'text-bg-secondary',
];
$params = array_filter(['search' => $search, 'role' => $role]);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0"><?= te('admin.users') ?></h1>
    <?php if (Auth::can('user.create')): ?>
        <a href="<?= e(url('admin/users/create')) ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus me-1"></i><?= te('admin.add_user') ?>
        </a>
    <?php endif; ?>
</div>

<?php /* POSTs, then redirects to the encoded GET, so the filters end up inside
         the ?q= token rather than as readable query parameters. */ ?>
<form method="post" action="<?= e(url('admin/users')) ?>" class="row g-2 mb-3" data-no-lock>
    <?= Csrf::field() ?>
    <div class="col-sm-5 col-md-4">
        <input type="search" class="form-control" name="search" value="<?= e($search) ?>"
               placeholder="<?= te('action.search') ?>" maxlength="100">
    </div>
    <div class="col-sm-4 col-md-3">
        <select class="form-select" name="role">
            <option value=""><?= te('admin.role') ?></option>
            <?php foreach (Permission::roles() as $r): ?>
                <option value="<?= e($r) ?>" <?= $role === $r ? 'selected' : '' ?>>
                    <?= te('role.' . $r) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary">
            <i class="bi bi-search"></i>
        </button>
        <?php if ($params): ?>
            <a href="<?= e(url('admin/users')) ?>" class="btn btn-link"><?= te('action.reset') ?></a>
        <?php endif; ?>
    </div>
</form>

<?php if (!$rows): ?>
    <div class="empty-state border rounded-3">
        <i class="bi bi-people"></i>
        <p class="mt-3 mb-0 text-body-secondary"><?= te('search.no_results') ?></p>
    </div>
<?php else: ?>
    <div class="table-scroll">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th scope="col"><?= te('auth.name') ?></th>
                    <th scope="col"><?= te('auth.email') ?></th>
                    <th scope="col"><?= te('admin.role') ?></th>
                    <th scope="col"><?= te('admin.plan') ?></th>
                    <th scope="col" class="text-end"><?= te('admin.records') ?></th>
                    <th scope="col"><?= te('admin.status') ?></th>
                    <th scope="col"><?= te('admin.created') ?></th>
                    <th scope="col" class="text-end"><?= te('common.actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <a href="<?= e(url('admin/users/' . $row['id'])) ?>"><?= e($row['name']) ?></a>
                            <?php if ($row['user_code']): ?>
                                <div class="small text-body-secondary record-value"><?= e($row['user_code']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= e($row['email']) ?></td>
                        <td><?= te('role.' . $row['role']) ?></td>
                        <td><?= e($row['plan_name'] ?? '—') ?></td>
                        <td class="text-end record-value">
                            <?= (int) $row['record_count'] ?>
                            <?php if ($row['property_limit'] !== null): ?>
                                <span class="text-body-secondary">/ <?= (int) $row['property_limit'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= e($statusClass[$row['status']] ?? 'text-bg-light') ?>">
                                <?= te('status.' . $row['status']) ?>
                            </span>
                        </td>
                        <td><?= e(fmt_date($row['created_at'])) ?></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a class="btn btn-outline-secondary"
                                   href="<?= e(url('admin/users/' . $row['id'])) ?>"
                                   title="<?= te('action.view') ?>">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a class="btn btn-outline-secondary"
                                   href="<?= e(url('admin/users/' . $row['id'] . '/properties')) ?>"
                                   title="<?= te('admin.view_records') ?>">
                                    <i class="bi bi-folder2-open"></i>
                                </a>
                                <?php if (Auth::can('user.update')): ?>
                                    <a class="btn btn-outline-secondary"
                                       href="<?= e(url('admin/users/' . $row['id'] . '/edit')) ?>"
                                       title="<?= te('action.edit') ?>">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
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
