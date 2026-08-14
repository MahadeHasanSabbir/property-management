<?php
/**
 * Own profile.
 *
 * @var array      $user
 * @var array|null $usage  null for staff and admins, who own no records
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Auth;
use App\Csrf;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0"><?= te('profile.title') ?></h1>
    <div class="d-flex gap-2">
        <a href="<?= e(url('profile/edit')) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil me-1"></i><?= te('profile.edit') ?>
        </a>
        <a href="<?= e(url('profile/password')) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-key me-1"></i><?= te('profile.change_password') ?>
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('auth.name') ?></dt>
            <dd class="col-sm-8 col-lg-9"><?= e($user['name']) ?></dd>

            <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('auth.email') ?></dt>
            <dd class="col-sm-8 col-lg-9"><?= e($user['email']) ?></dd>

            <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('auth.phone') ?></dt>
            <dd class="col-sm-8 col-lg-9 record-value"><?= e($user['phone'] ?: '—') ?></dd>

            <?php if (!empty($user['user_code'])): ?>
                <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('profile.user_code') ?></dt>
                <dd class="col-sm-8 col-lg-9 record-value"><?= e($user['user_code']) ?></dd>
            <?php endif; ?>

            <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('admin.role') ?></dt>
            <dd class="col-sm-8 col-lg-9"><?= te('role.' . $user['role']) ?></dd>

            <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('profile.member_since') ?></dt>
            <dd class="col-sm-8 col-lg-9"><?= e(fmt_date($user['created_at'])) ?></dd>

            <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('profile.last_sign_in') ?></dt>
            <dd class="col-sm-8 col-lg-9">
                <?= $user['last_login_at'] ? e(fmt_date($user['last_login_at'])) : te('common.never') ?>
            </dd>

            <?php if ($usage !== null): ?>
                <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('plan.current') ?></dt>
                <dd class="col-sm-8 col-lg-9">
                    <?= e($usage['plan']['name'] ?? '—') ?>
                    <a href="<?= e(url('plan')) ?>" class="small ms-2"><?= te('action.view') ?></a>
                </dd>

                <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('profile.records_used') ?></dt>
                <dd class="col-sm-8 col-lg-9 record-value">
                    <?= (int) $usage['used'] ?>
                    <?php if ($usage['limit'] !== null): ?> / <?= (int) $usage['limit'] ?><?php endif; ?>
                </dd>
            <?php endif; ?>
        </dl>
    </div>
</div>

<?php /* Self-deletion requires the account password and a POST. The legacy
         version ran on a bare GET with no parameters at all, so an <img> tag
         pointing at it destroyed the account. */ ?>
<?php if (Auth::isCustomer()): ?>
    <div class="card border-danger-subtle">
        <div class="card-body">
            <h2 class="h6 text-danger"><?= te('profile.delete_account') ?></h2>
            <p class="text-body-secondary small"><?= te('profile.delete_warning') ?></p>

            <form method="post" action="<?= e(url('profile/delete')) ?>"
                  data-confirm="<?= te('profile.delete_warning') ?>" class="row g-2 align-items-end">
                <?= Csrf::field() ?>
                <div class="col-sm-6 col-md-4">
                    <label for="del-password" class="form-label small"><?= te('auth.password') ?></label>
                    <input type="password" class="form-control form-control-sm"
                           id="del-password" name="password" autocomplete="current-password" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash me-1"></i><?= te('profile.delete_account') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
