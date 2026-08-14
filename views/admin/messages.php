<?php
/**
 * Contact messages.
 *
 * Every field here comes from an anonymous, unauthenticated form, so all of it
 * is escaped on output — otherwise a visitor could store markup that runs
 * inside an administrator's session.
 *
 * @var array $rows
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Auth;
use App\Csrf;

$params = array_filter(['status' => $status]);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0"><?= te('admin.messages') ?></h1>

    <form method="post" action="<?= e(url('admin/messages')) ?>" class="d-flex gap-2" data-no-lock>
        <?= Csrf::field() ?>
        <select class="form-select form-select-sm" name="status" data-submit-on-change>
            <option value=""     <?= $status === ''     ? 'selected' : '' ?>><?= te('common.actions') ?></option>
            <option value="new"  <?= $status === 'new'  ? 'selected' : '' ?>><?= te('admin.messages_new') ?></option>
            <option value="read" <?= $status === 'read' ? 'selected' : '' ?>><?= te('admin.mark_read') ?></option>
        </select>
        <noscript><button type="submit" class="btn btn-sm btn-outline-secondary"><?= te('action.search') ?></button></noscript>
    </form>
</div>

<?php if (!$rows): ?>
    <div class="empty-state border rounded-3">
        <i class="bi bi-envelope"></i>
        <p class="mt-3 mb-0 text-body-secondary"><?= te('admin.no_messages') ?></p>
    </div>
<?php else: ?>
    <div class="list-group mb-3">
        <?php foreach ($rows as $row): ?>
            <div class="list-group-item <?= $row['status'] === 'new' ? 'border-start border-3 border-primary' : '' ?>">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div class="me-auto">
                        <strong><?= e($row['name']) ?></strong>
                        <a href="mailto:<?= e($row['email']) ?>" class="ms-2 small"><?= e($row['email']) ?></a>
                        <?php if ($row['status'] === 'new'): ?>
                            <span class="badge text-bg-primary ms-2"><?= te('admin.messages_new') ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-body-secondary small"><?= e(fmt_date($row['created_at'])) ?></span>

                        <?php if ($row['status'] === 'new'): ?>
                            <form method="post" action="<?= e(url('admin/messages/' . $row['id'] . '/read')) ?>" class="m-0">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                        title="<?= te('admin.mark_read') ?>">
                                    <i class="bi bi-check2"></i>
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (Auth::can('message.delete')): ?>
                            <form method="post" action="<?= e(url('admin/messages/' . $row['id'] . '/delete')) ?>"
                                  data-confirm="<?= te('action.confirm') ?>" class="m-0">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <p class="mb-0 mt-2" style="white-space:pre-wrap"><?= e($row['message']) ?></p>

                <?php if ($row['ip']): ?>
                    <div class="text-body-secondary small mt-2 record-value"><?= e($row['ip']) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?= App\View::renderPartial('partials.pagination', [
            'basePath' => $basePath,
            'page'     => $page,
            'lastPage' => $lastPage,
            'params'   => $params,
            'total'    => $total,
            'per'      => $per,
        ]) ?>
<?php endif; ?>
