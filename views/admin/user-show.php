<?php
/**
 * One user's account, as seen by staff.
 *
 * @var array      $user
 * @var array|null $usage
 * @var int        $docs
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Auth;
use App\Csrf;

$isSelf = ((int) $user['id'] === Auth::id());
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h4 mb-0"><?= e($user['name']) ?></h1>
        <p class="text-body-secondary small mb-0"><?= e($user['email']) ?></p>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= e(url('admin/users')) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i><?= te('action.back') ?>
        </a>
        <a href="<?= e(url('admin/users/' . $user['id'] . '/properties')) ?>"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-folder2-open me-1"></i><?= te('admin.view_records') ?>
        </a>
        <?php if (Auth::can('user.update')): ?>
            <a href="<?= e(url('admin/users/' . $user['id'] . '/edit')) ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil me-1"></i><?= te('action.edit') ?>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('profile.user_code') ?></dt>
            <dd class="col-sm-8 col-lg-9 record-value"><?= e($user['user_code'] ?: '—') ?></dd>

            <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('auth.phone') ?></dt>
            <dd class="col-sm-8 col-lg-9 record-value"><?= e($user['phone'] ?: '—') ?></dd>

            <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('admin.role') ?></dt>
            <dd class="col-sm-8 col-lg-9"><?= te('role.' . $user['role']) ?></dd>

            <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('admin.status') ?></dt>
            <dd class="col-sm-8 col-lg-9"><?= te('status.' . $user['status']) ?></dd>

            <?php if ($usage !== null): ?>
                <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('admin.plan') ?></dt>
                <dd class="col-sm-8 col-lg-9"><?= e($usage['plan']['name'] ?? '—') ?></dd>

                <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('admin.records') ?></dt>
                <dd class="col-sm-8 col-lg-9 record-value">
                    <?= (int) $usage['used'] ?>
                    <?php if ($usage['limit'] !== null): ?>
                        / <?= (int) $usage['limit'] ?>
                        <?php if ($user['property_limit_override'] !== null): ?>
                            <span class="badge text-bg-info ms-1"><?= te('admin.limit_override') ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        / <?= te('common.unlimited') ?>
                    <?php endif; ?>
                </dd>
            <?php endif; ?>

            <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('property.documents') ?></dt>
            <dd class="col-sm-8 col-lg-9 record-value"><?= (int) $docs ?></dd>

            <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('admin.created') ?></dt>
            <dd class="col-sm-8 col-lg-9"><?= e(fmt_date($user['created_at'])) ?></dd>

            <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('profile.last_sign_in') ?></dt>
            <dd class="col-sm-8 col-lg-9">
                <?= $user['last_login_at'] ? e(fmt_date($user['last_login_at'])) : te('common.never') ?>
            </dd>
        </dl>
    </div>
</div>

<?php if (Auth::can('user.update')): ?>
    <div class="card border-warning-subtle mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="h6 mb-1"><?= te('admin.reset_password') ?></h2>
                <?php /* A random temporary password, shown once and never
                         derived from anything about the account. */ ?>
                <p class="text-body-secondary small mb-0"><?= te('auth.must_change_password') ?></p>
            </div>

            <form method="post" action="<?= e(url('admin/users/' . $user['id'] . '/password')) ?>"
                  data-confirm="<?= te('action.confirm') ?>" class="m-0">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-key me-1"></i><?= te('admin.reset_password') ?>
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if (Auth::can('user.delete') && !$isSelf): ?>
    <div class="card border-danger-subtle">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="h6 text-danger mb-1"><?= te('admin.user_deleted') ?></h2>
                <p class="text-body-secondary small mb-0"><?= te('admin.delete_user_confirm') ?></p>
            </div>

            <form method="post" action="<?= e(url('admin/users/' . $user['id'] . '/delete')) ?>"
                  data-confirm="<?= te('admin.delete_user_confirm') ?>" class="m-0">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash me-1"></i><?= te('action.delete') ?>
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>
