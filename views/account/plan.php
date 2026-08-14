<?php
/**
 * Plan overview and comparison.
 *
 * Limits and features are read from the `plans` table, so this page reflects
 * whatever an administrator has configured — nothing here is hardcoded.
 *
 * @var array $user
 * @var array $usage
 * @var array $plans
 */

defined('APP_BOOTSTRAPPED') || exit;

$currentCode = $user['plan_code'] ?? null;
?>
<h1 class="h4 mb-3"><?= te('plan.title') ?></h1>

<?= App\View::renderPartial('partials.plan-usage', ['usage' => $usage]) ?>

<div class="row g-3">
    <?php foreach ($plans as $plan): ?>
        <?php $isCurrent = ($plan['code'] === $currentCode); ?>
        <div class="col-md-6">
            <div class="card h-100 <?= $isCurrent ? 'border-primary' : '' ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><?= e($plan['name']) ?></span>
                    <?php if ($isCurrent): ?>
                        <span class="badge text-bg-primary"><?= te('plan.current') ?></span>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-body-secondary"><?= te('plan.limit') ?></span>
                            <strong class="record-value">
                                <?= $plan['property_limit'] === null
                                        ? te('common.unlimited')
                                        : (int) $plan['property_limit'] ?>
                            </strong>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-body-secondary"><?= te('plan.documents') ?></span>
                            <span>
                                <?php if ($plan['can_upload_documents']): ?>
                                    <i class="bi bi-check-lg text-success"></i>
                                <?php else: ?>
                                    <i class="bi bi-dash text-body-secondary"></i>
                                <?php endif; ?>
                            </span>
                        </li>
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-body-secondary"><?= te('plan.export') ?></span>
                            <span>
                                <?php if ($plan['can_export']): ?>
                                    <i class="bi bi-check-lg text-success"></i>
                                <?php else: ?>
                                    <i class="bi bi-dash text-body-secondary"></i>
                                <?php endif; ?>
                            </span>
                        </li>
                    </ul>
                </div>

                <?php if (!$isCurrent): ?>
                    <div class="card-footer">
                        <?php /* Plans are assigned by an administrator; there is
                                 no self-service billing in this application. */ ?>
                        <span class="small text-body-secondary">
                            <i class="bi bi-info-circle me-1"></i>
                            <?= te('plan.upgrade_prompt', [
                                    'limit' => $usage['limit'] ?? '—',
                                    'plan'  => $usage['plan']['name'] ?? '—',
                                ]) ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
