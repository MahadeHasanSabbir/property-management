<?php
/**
 * Property records listing (search with no filters applied by default).
 *
 * @var array $rows
 * @var array $usage
 * @var array $filters
 */

defined('APP_BOOTSTRAPPED') || exit;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0"><?= te('property.title') ?></h1>

    <div class="d-flex gap-2">
        <?php if ($usage['can_export'] && $total > 0): ?>
            <a class="btn btn-outline-secondary btn-sm"
               href="<?= e(url('properties/export', $active_f)) ?>">
                <i class="bi bi-download me-1"></i><?= te('action.export') ?>
            </a>
        <?php endif; ?>

        <a class="btn btn-primary btn-sm" href="<?= e(url('properties/create')) ?>">
            <i class="bi bi-plus-lg me-1"></i><?= te('property.add') ?>
        </a>
    </div>
</div>

<?= App\View::renderPartial('partials.plan-usage', ['usage' => $usage]) ?>

<?= App\View::renderPartial('partials.search-form', [
        'filters'    => $filters,
        'moujas'     => $moujas,
        'basePath'   => $basePath,
        'formAction' => $formAction,
    ]) ?>

<?php if (!$rows): ?>
    <div class="empty-state border rounded-3">
        <i class="bi bi-folder2-open"></i>
        <p class="mt-3 mb-1"><?= $active_f ? te('search.no_results') : te('property.none') ?></p>
        <p class="text-body-secondary small">
            <?= $active_f ? te('search.no_results_hint') : te('property.none_hint') ?>
        </p>
        <?php if (!$active_f): ?>
            <a class="btn btn-primary btn-sm mt-2" href="<?= e(url('properties/create')) ?>">
                <i class="bi bi-plus-lg me-1"></i><?= te('property.add') ?>
            </a>
        <?php endif; ?>
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
