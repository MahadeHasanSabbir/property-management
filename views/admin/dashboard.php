<?php
/**
 * Admin dashboard.
 *
 * Every tile is a live COUNT over exactly the thing it names.
 *
 * @var array $stats
 * @var array $recentUsers
 */

defined('APP_BOOTSTRAPPED') || exit;

// label, value, icon, optional link
$tiles = [
    ['admin.users_total',      $stats['users'],      'people',       url('admin/users')],
    ['admin.properties_total', $stats['properties'], 'folder2-open', null],
    ['admin.documents_total',  $stats['documents'],  'paperclip',    null],
    ['admin.signups_30d',      $stats['signups'],    'person-plus',  null],
    ['admin.views_30d',        $stats['views'],      'graph-up',     null],
    ['admin.messages_new',     $stats['messages'],   'envelope',     url('admin/messages')],
];
?>
<h1 class="h4 mb-3"><?= te('admin.dashboard') ?></h1>

<div class="row g-3 mb-4">
    <?php foreach ($tiles as [$label, $value, $icon, $href]): ?>
        <div class="col-6 col-lg-4 col-xl-2">
            <div class="card stat-tile h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-label text-body-secondary"><?= te($label) ?></div>
                        <i class="bi bi-<?= e($icon) ?> text-body-secondary"></i>
                    </div>
                    <div class="stat-value record-value"><?= (int) $value ?></div>
                    <?php if ($href): ?>
                        <a href="<?= e($href) ?>" class="stretched-link small"><?= te('action.view') ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><?= te('admin.users') ?></span>
        <a href="<?= e(url('admin/users')) ?>" class="small"><?= te('action.view') ?></a>
    </div>

    <?php if (!$recentUsers): ?>
        <div class="card-body empty-state">
            <i class="bi bi-people"></i>
            <p class="mt-3 mb-0 text-body-secondary"><?= te('common.none') ?></p>
        </div>
    <?php else: ?>
        <div class="table-scroll">
            <table class="table table-sm mb-0 align-middle">
                <thead>
                    <tr>
                        <th scope="col"><?= te('auth.name') ?></th>
                        <th scope="col"><?= te('auth.email') ?></th>
                        <th scope="col"><?= te('profile.user_code') ?></th>
                        <th scope="col"><?= te('admin.created') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $row): ?>
                        <tr>
                            <td>
                                <a href="<?= e(url('admin/users/' . $row['id'])) ?>"><?= e($row['name']) ?></a>
                            </td>
                            <td><?= e($row['email']) ?></td>
                            <td class="record-value"><?= e($row['user_code'] ?: '—') ?></td>
                            <td><?= e(fmt_date($row['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
