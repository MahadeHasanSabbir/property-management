<?php
/**
 * The property records table. Shared by the customer listing, the search
 * results, and the admin's read-only view of a customer's records — so the
 * columns can never drift apart the way admin/view.php and profile/view.php did
 * (they were ~80% identical and both had to be edited for any change).
 *
 * @var array       $rows
 * @var string      $basePath   route the sort links point at
 * @var array       $active_f   filters to preserve in sort links
 * @var string|null $sort
 * @var string|null $dir
 * @var bool        $readonly   admin view: no edit/delete controls
 * @var string      $highlight  dag/khatian token to mark, if any
 */

defined('APP_BOOTSTRAPPED') || exit;

$readonly  = $readonly  ?? false;
$active_f  = $active_f  ?? [];
$sort      = $sort      ?? 'seq';
$dir       = $dir       ?? 'ASC';
$highlight = trim((string) ($highlight ?? ''));

/** A sortable column header that flips direction when it is already active. */
$header = static function (string $column, string $label) use ($basePath, $active_f, $sort, $dir): string {
    $isActive = ($sort === $column);
    $next     = ($isActive && $dir === 'ASC') ? 'DESC' : 'ASC';

    $params = $active_f;
    $params['sort'] = $column;
    $params['dir']  = $next;
    unset($params['page']);

    $icon = $isActive
        ? ($dir === 'ASC' ? 'caret-up-fill' : 'caret-down-fill')
        : 'chevron-expand';

    return sprintf(
        '<a href="%s" class="link-body-emphasis text-decoration-none d-inline-flex align-items-center gap-1">%s<i class="bi bi-%s small opacity-50"></i></a>',
        e(url($basePath, $params)),
        e($label),
        $icon
    );
};

/**
 * Mark the token that matched the search inside a comma list, so it is obvious
 * why a row was returned. Compares whole tokens — never a substring, which is
 * the mistake the old LIKE '%12%' search made.
 */
$tokens = static function (string $raw) use ($highlight): string {
    if ($raw === '') {
        return '—';
    }

    $out = [];
    foreach (split_tokens($raw) as $token) {
        $out[] = ($highlight !== '' && $token === $highlight)
            ? '<mark class="token-hit">' . e($token) . '</mark>'
            : e($token);
    }
    return $out ? implode(', ', $out) : '—';
};
?>
<div class="table-scroll">
    <table class="table table-sm table-hover align-middle mb-0">
        <thead>
            <tr>
                <th scope="col"><?= $header('seq', t('property.seq')) ?></th>
                <th scope="col"><?= $header('deed_no', t('property.deed_no')) ?></th>
                <th scope="col"><?= te('property.dag_current') ?></th>
                <th scope="col"><?= te('property.dag_previous') ?></th>
                <th scope="col"><?= te('property.khatian_current') ?></th>
                <th scope="col"><?= te('property.khatian_previous') ?></th>
                <th scope="col"><?= te('property.old_owner') ?></th>
                <th scope="col"><?= te('property.new_owner') ?></th>
                <th scope="col"><?= $header('mouja', t('property.mouja')) ?></th>
                <th scope="col" class="text-end"><?= $header('area_cent', t('property.area')) ?></th>
                <th scope="col"><?= $header('deed_date', t('property.deed_date')) ?></th>
                <th scope="col" class="text-end no-print"><?= te('common.actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td class="record-value"><?= (int) $row['seq'] ?></td>
                    <td class="record-value"><?= e($row['deed_no']) ?: '—' ?></td>
                    <td class="record-value"><?= $tokens($row['dag_current']) ?></td>
                    <td class="record-value"><?= $tokens($row['dag_previous']) ?></td>
                    <td class="record-value"><?= $tokens($row['khatian_current']) ?></td>
                    <td class="record-value"><?= $tokens($row['khatian_previous']) ?></td>
                    <td><?= e($row['old_owner']) ?: '—' ?></td>
                    <td><?= e($row['new_owner']) ?: '—' ?></td>
                    <td><?= e($row['mouja']) ?: '—' ?></td>
                    <td class="text-end record-value"><?= e(fmt_area($row['area_cent'])) ?></td>
                    <td class="record-value"><?= e(fmt_date($row['deed_date'])) ?></td>
                    <td class="text-end no-print">
                        <?php if ($readonly): ?>
                            <span class="text-body-secondary small">—</span>
                        <?php else: ?>
                            <div class="btn-group btn-group-sm">
                                <a class="btn btn-outline-secondary"
                                   href="<?= e(url('properties/' . $row['id'])) ?>"
                                   title="<?= te('action.view') ?>">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a class="btn btn-outline-secondary"
                                   href="<?= e(url('properties/' . $row['id'] . '/edit')) ?>"
                                   title="<?= te('action.edit') ?>">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
