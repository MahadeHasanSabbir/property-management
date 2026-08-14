<?php
/**
 * Customer dashboard.
 *
 * @var array $user
 * @var array $usage
 * @var array $recent
 */

defined('APP_BOOTSTRAPPED') || exit;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h4 mb-0"><?= te('nav.dashboard') ?></h1>
        <p class="text-body-secondary mb-0 small"><?= e($user['name']) ?></p>
    </div>
    <a class="btn btn-primary btn-sm" href="<?= e(url('properties/create')) ?>">
        <i class="bi bi-plus-lg me-1"></i><?= te('property.add') ?>
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-tile h-100">
            <div class="card-body">
                <div class="stat-label text-body-secondary"><?= te('profile.records_used') ?></div>
                <div class="stat-value record-value"><?= (int) $usage['used'] ?></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card stat-tile h-100">
            <div class="card-body">
                <div class="stat-label text-body-secondary"><?= te('plan.limit') ?></div>
                <div class="stat-value record-value">
                    <?= $usage['limit'] === null ? te('common.unlimited') : (int) $usage['limit'] ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card stat-tile h-100">
            <div class="card-body">
                <div class="stat-label text-body-secondary"><?= te('plan.current') ?></div>
                <div class="stat-value"><?= e($usage['plan']['name'] ?? '—') ?></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card stat-tile h-100">
            <div class="card-body">
                <div class="stat-label text-body-secondary"><?= te('profile.last_sign_in') ?></div>
                <div class="stat-value fs-6">
                    <?= $user['last_login_at'] ? e(fmt_date($user['last_login_at'])) : te('common.never') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= App\View::renderPartial('partials.plan-usage', ['usage' => $usage]) ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><?= te('property.title') ?></span>
        <a href="<?= e(url('properties')) ?>" class="small"><?= te('action.view') ?></a>
    </div>

    <?php if (!$recent): ?>
        <div class="card-body empty-state">
            <i class="bi bi-folder2-open"></i>
            <p class="mt-3 mb-1"><?= te('property.none') ?></p>
            <p class="text-body-secondary small mb-0"><?= te('property.none_hint') ?></p>
        </div>
    <?php else: ?>
        <div class="table-scroll">
            <table class="table table-sm mb-0 align-middle">
                <thead>
                    <tr>
                        <th scope="col"><?= te('property.seq') ?></th>
                        <th scope="col"><?= te('property.deed_no') ?></th>
                        <th scope="col"><?= te('property.mouja') ?></th>
                        <th scope="col" class="text-end"><?= te('property.area') ?></th>
                        <th scope="col" class="text-end"><?= te('common.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $row): ?>
                        <tr>
                            <td class="record-value"><?= (int) $row['seq'] ?></td>
                            <td class="record-value"><?= e($row['deed_no']) ?: '—' ?></td>
                            <td><?= e($row['mouja']) ?: '—' ?></td>
                            <td class="text-end record-value"><?= e(fmt_area($row['area_cent'])) ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="<?= e(url('properties/' . $row['id'])) ?>">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
