<?php
/**
 * Create / edit a user account.
 *
 * @var array|null $user   null when creating
 * @var array      $plans
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Auth;
use App\Csrf;
use App\Permission;

$editing = $user !== null;
$action  = $editing ? url('admin/users/' . $user['id']) : url('admin/users/create');
$isSelf  = $editing && ((int) $user['id'] === Auth::id());

$v = static function (string $field) use ($user): string {
    $old = old($field);
    return $old !== '' ? $old : (string) ($user[$field] ?? '');
};
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0"><?= $editing ? te('action.edit') : te('admin.add_user') ?></h1>
            <a href="<?= e($editing ? url('admin/users/' . $user['id']) : url('admin/users')) ?>"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i><?= te('action.back') ?>
            </a>
        </div>

        <form method="post" action="<?= e($action) ?>" novalidate>
            <?= Csrf::field() ?>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label"><?= te('auth.name') ?></label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?= e($v('name')) ?>" maxlength="60" required>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label"><?= te('auth.email') ?></label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= e($v('email')) ?>" maxlength="190" required>
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label"><?= te('auth.phone') ?></label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="<?= e($v('phone')) ?>" maxlength="20" placeholder="01712345678">
                        </div>

                        <?php if (!$editing): ?>
                            <div class="col-md-6">
                                <label for="password" class="form-label"><?= te('auth.password') ?></label>
                                <input type="password" class="form-control" id="password"
                                       name="password" minlength="8" autocomplete="new-password" required>
                                <div class="form-text form-hint"><?= te('auth.must_change_password') ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="role" class="form-label"><?= te('admin.role') ?></label>
                            <?php /* An admin cannot change their own role: with a
                                     single administrator, one submit would lock
                                     everyone out of the admin area. */ ?>
                            <select class="form-select" id="role" name="role" <?= $isSelf ? 'disabled' : '' ?>>
                                <?php foreach (Permission::roles() as $r): ?>
                                    <option value="<?= e($r) ?>" <?= $v('role') === $r ? 'selected' : '' ?>>
                                        <?= te('role.' . $r) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($isSelf): ?>
                                <input type="hidden" name="role" value="<?= e($user['role']) ?>">
                                <div class="form-text form-hint"><?= te('admin.self_demote') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="plan_code" class="form-label"><?= te('admin.plan') ?></label>
                            <select class="form-select" id="plan_code" name="plan_code">
                                <option value=""><?= te('common.none') ?></option>
                                <?php foreach ($plans as $plan): ?>
                                    <option value="<?= e($plan['code']) ?>"
                                        <?= $v('plan_code') === $plan['code'] ? 'selected' : '' ?>>
                                        <?= e($plan['name']) ?>
                                        (<?= $plan['property_limit'] === null
                                                ? te('common.unlimited')
                                                : (int) $plan['property_limit'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="status" class="form-label"><?= te('admin.status') ?></label>
                            <select class="form-select" id="status" name="status">
                                <?php foreach (['active', 'pending', 'suspended'] as $s): ?>
                                    <option value="<?= e($s) ?>"
                                        <?= ($v('status') ?: 'active') === $s ? 'selected' : '' ?>>
                                        <?= te('status.' . $s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if ($editing): ?>
                            <div class="col-md-6">
                                <label for="property_limit_override" class="form-label">
                                    <?= te('admin.limit_override') ?>
                                </label>
                                <input type="number" min="0" class="form-control"
                                       id="property_limit_override" name="property_limit_override"
                                       value="<?= e((string) ($user['property_limit_override'] ?? '')) ?>">
                                <div class="form-text form-hint"><?= te('admin.limit_override_hint') ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i><?= te('action.save') ?>
                </button>
                <a href="<?= e($editing ? url('admin/users/' . $user['id']) : url('admin/users')) ?>"
                   class="btn btn-outline-secondary"><?= te('action.cancel') ?></a>
            </div>
        </form>
    </div>
</div>
