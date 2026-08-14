<?php
/**
 * Plan usage meter, shown above the record list.
 *
 * Being over the limit is a read-only state, not a broken one — an
 * administrator can lower a limit below current usage, so the message says
 * clearly that editing and deleting still work.
 *
 * @var array $usage
 */

defined('APP_BOOTSTRAPPED') || exit;

if ($usage['limit'] === null) {
    return; // unlimited plans need no meter
}

$near = $usage['percent'] >= 80;
$bar  = $usage['over'] ? 'bg-danger' : ($near ? 'bg-warning' : 'bg-primary');
?>
<div class="mb-3">
    <div class="d-flex justify-content-between align-items-center small mb-1">
        <span class="text-body-secondary">
            <?= te('profile.records_used') ?>:
            <strong class="record-value"><?= (int) $usage['used'] ?></strong>
            / <span class="record-value"><?= (int) $usage['limit'] ?></span>
            <?php if ($usage['plan']): ?>
                <span class="badge text-bg-light ms-1"><?= e($usage['plan']['name']) ?></span>
            <?php endif; ?>
        </span>

        <?php if ($near || $usage['over']): ?>
            <a href="<?= e(url('plan')) ?>" class="link-primary"><?= te('plan.upgrade') ?></a>
        <?php endif; ?>
    </div>

    <div class="progress usage-meter" role="progressbar"
         aria-valuenow="<?= (int) $usage['percent'] ?>" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar <?= e($bar) ?>" style="width: <?= (int) $usage['percent'] ?>%"></div>
    </div>

    <?php if ($usage['over']): ?>
        <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?= te('plan.over_limit', ['used' => $usage['used'], 'limit' => $usage['limit']]) ?>
        </div>
    <?php elseif ($usage['remaining'] === 0): ?>
        <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?= te('plan.limit_reached') ?>
        </div>
    <?php endif; ?>
</div>
