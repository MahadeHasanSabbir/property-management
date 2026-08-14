<?php
/**
 * Search screen. Same machinery as the listing, but leads with the filters and
 * shows which ones are active.
 */

defined('APP_BOOTSTRAPPED') || exit;

/** Human label for each active filter chip. */
$labels = [
    'dag'           => t('search.dag'),
    'dag_scope'     => t('search.scope'),
    'khatian'       => t('search.khatian'),
    'khatian_scope' => t('search.scope'),
    'deed_no'       => t('property.deed_no'),
    'mouja'         => t('property.mouja'),
    'owner'         => t('search.owner'),
    'owner_mode'    => t('search.owner_mode'),
    'area_min'      => t('search.area_min'),
    'area_max'      => t('search.area_max'),
    'date_from'     => t('search.date_from'),
    'date_to'       => t('search.date_to'),
    'mode'          => t('search.mode'),
    'sort'          => t('search.results'),
    'dir'           => t('search.results'),
    'per'           => t('search.per_page'),
];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0"><?= te('search.title') ?></h1>

    <?php if ($usage['can_export'] && $total > 0): ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('properties/export', $active_f)) ?>">
            <i class="bi bi-download me-1"></i><?= te('action.export') ?>
        </a>
    <?php endif; ?>
</div>

<?= App\View::renderPartial('partials.search-form', [
        'filters'    => $filters,
        'moujas'     => $moujas,
        'basePath'   => $basePath,
        'formAction' => $formAction,
    ]) ?>

<?php if ($active_f): ?>
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <span class="text-body-secondary small"><?= te('search.active_filters') ?>:</span>

        <?php foreach ($active_f as $key => $value): ?>
            <?php /* Removing one chip keeps the rest — the link is the current
                     filter set minus this key. */ ?>
            <?php $without = $active_f; unset($without[$key]); ?>
            <a class="badge rounded-pill text-bg-secondary text-decoration-none filter-chip"
               href="<?= e(url($basePath, $without)) ?>">
                <?= e($labels[$key] ?? $key) ?>: <?= e((string) $value) ?>
                <i class="bi bi-x-lg ms-1"></i>
            </a>
        <?php endforeach; ?>

        <a class="btn btn-link btn-sm p-0 ms-1" href="<?= e(url($basePath)) ?>">
            <?= te('search.clear') ?>
        </a>
    </div>
<?php endif; ?>

<?php if (!$rows): ?>
    <div class="empty-state border rounded-3">
        <i class="bi bi-search"></i>
        <p class="mt-3 mb-1"><?= te('search.no_results') ?></p>
        <p class="text-body-secondary small mb-0"><?= te('search.no_results_hint') ?></p>
    </div>
<?php else: ?>
    <?= App\View::renderPartial('partials.property-table', [
            'rows'      => $rows,
            'basePath'  => $basePath,
            'active_f'  => $active_f,
            'sort'      => $filters['sort'],
            'dir'       => $filters['dir'],
            'highlight' => $filters['dag'] ?: $filters['khatian'],
        ]) ?>

    <?= App\View::renderPartial('partials.pagination', [
            'basePath' => $basePath,
            'page'     => $page,
            'lastPage' => $lastPage,
            'params'   => $active_f,
            'total'    => $total,
            'per'      => $per,
        ]) ?>
<?php endif; ?>
