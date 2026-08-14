<?php
/**
 * Plan editor.
 *
 * This page is why no limit is written into PHP: basic-versus-pro entitlements
 * are data, and changing them is an admin task rather than a deployment.
 *
 * @var array $plans
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Csrf;
?>
<h1 class="h4 mb-3"><?= te('admin.plans') ?></h1>

<div class="row g-3">
    <?php foreach ($plans as $plan): ?>
        <div class="col-lg-6">
            <form method="post" action="<?= e(url('admin/plans/' . $plan['code'])) ?>" class="card h-100">
                <?= Csrf::field() ?>

                <div class="card-header d-flex justify-content-between align-items-center">
                    <code><?= e($plan['code']) ?></code>
                    <?php if ($plan['is_default']): ?>
                        <span class="badge text-bg-primary"><?= te('common.required') ?></span>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <label for="name-<?= e($plan['code']) ?>" class="form-label"><?= te('auth.name') ?></label>
                        <input type="text" class="form-control" id="name-<?= e($plan['code']) ?>"
                               name="name" value="<?= e($plan['name']) ?>" maxlength="40" required>
                    </div>

                    <div class="mb-3">
                        <label for="limit-<?= e($plan['code']) ?>" class="form-label"><?= te('plan.limit') ?></label>
                        <input type="number" min="0" class="form-control" id="limit-<?= e($plan['code']) ?>"
                               name="property_limit"
                               value="<?= $plan['property_limit'] === null ? '' : (int) $plan['property_limit'] ?>">
                        <div class="form-text form-hint"><?= te('admin.plan_limit_hint') ?></div>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="1"
                               id="docs-<?= e($plan['code']) ?>" name="can_upload_documents"
                               <?= $plan['can_upload_documents'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="docs-<?= e($plan['code']) ?>">
                            <?= te('plan.documents') ?>
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1"
                               id="export-<?= e($plan['code']) ?>" name="can_export"
                               <?= $plan['can_export'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="export-<?= e($plan['code']) ?>">
                            <?= te('plan.export') ?>
                        </label>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label for="sort-<?= e($plan['code']) ?>" class="form-label small">
                                <?= te('search.results') ?>
                            </label>
                            <input type="number" min="0" class="form-control form-control-sm"
                                   id="sort-<?= e($plan['code']) ?>" name="sort_order"
                                   value="<?= (int) $plan['sort_order'] ?>">
                        </div>

                        <?php if (!$plan['is_default']): ?>
                            <div class="col-6 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1"
                                           id="default-<?= e($plan['code']) ?>" name="is_default">
                                    <label class="form-check-label small" for="default-<?= e($plan['code']) ?>">
                                        <?= te('home.get_started') ?>
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg me-1"></i><?= te('action.save') ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endforeach; ?>
</div>
