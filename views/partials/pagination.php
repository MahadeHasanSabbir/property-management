<?php
/**
 * Pagination control, used by every paged list in the app.
 *
 * The legacy project had two divergent implementations of this — one in
 * profile/view.php and another in admin/view.php — and both mangled the URL
 * with string surgery (`url.slice(0, -2)` if the URL happened to contain a '0').
 * There is one copy now, and it rebuilds links through url() so the encoded ?q=
 * token always carries the active filters forward.
 *
 * @var string $basePath  route path, e.g. 'properties/search'
 * @var int    $page
 * @var int    $lastPage
 * @var array  $params    filters to preserve across pages
 * @var int    $total
 * @var int    $per
 */

defined('APP_BOOTSTRAPPED') || exit;

$params   = $params ?? [];
$total    = (int) ($total ?? 0);
$per      = (int) ($per ?? PAGE_SIZE_DEFAULT);
$page     = (int) $page;
$lastPage = (int) $lastPage;

if ($total === 0) {
    return;
}

$from = (($page - 1) * $per) + 1;
$to   = min($total, $page * $per);

/** Link to a page, preserving every active filter. */
$link = static function (int $target) use ($basePath, $params): string {
    $query = $params;
    if ($target > 1) {
        $query['page'] = $target;
    } else {
        unset($query['page']);
    }
    return url($basePath, $query);
};

// A short window around the current page rather than every page number.
$window = 2;
$start  = max(1, $page - $window);
$end    = min($lastPage, $page + $window);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
    <span class="text-body-secondary small">
        <?= te('search.showing', ['from' => $from, 'to' => $to, 'total' => $total]) ?>
    </span>

    <?php if ($lastPage > 1): ?>
        <nav aria-label="<?= te('search.results') ?>">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e($link(max(1, $page - 1))) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <?php if ($start > 1): ?>
                    <li class="page-item"><a class="page-link" href="<?= e($link(1)) ?>">1</a></li>
                    <?php if ($start > 2): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= e($link($i)) ?>"
                           <?= $i === $page ? 'aria-current="page"' : '' ?>><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($end < $lastPage): ?>
                    <?php if ($end < $lastPage - 1): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= e($link($lastPage)) ?>"><?= $lastPage ?></a>
                    </li>
                <?php endif; ?>

                <li class="page-item <?= $page >= $lastPage ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e($link(min($lastPage, $page + 1))) ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
