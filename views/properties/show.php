<?php
/**
 * A single property record, with its attached documents.
 *
 * @var array $property
 * @var array $documents
 * @var array $usage
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Csrf;
use App\Document;

/** Render a comma list as separate, readable tokens. */
$tokens = static function (string $raw): string {
    $parts = split_tokens($raw);
    return $parts ? implode(', ', array_map('e', $parts)) : '—';
};

$fields = [
    ['property.deed_no',          e($property['deed_no']) ?: '—',                 true],
    ['property.deed_date',        e(fmt_date($property['deed_date'])),            true],
    ['property.area',             e(fmt_area($property['area_cent'])),            true],
    ['property.mouja',            e($property['mouja']) ?: '—',                   false],
    ['property.old_owner',        e($property['old_owner']) ?: '—',               false],
    ['property.new_owner',        e($property['new_owner']) ?: '—',               false],
    ['property.dag_current',      $tokens($property['dag_current']),              true],
    ['property.dag_previous',     $tokens($property['dag_previous']),             true],
    ['property.khatian_current',  $tokens($property['khatian_current']),          true],
    ['property.khatian_previous', $tokens($property['khatian_previous']),         true],
];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0">
        <?= te('property.one') ?>
        <span class="text-body-secondary record-value">#<?= (int) $property['seq'] ?></span>
    </h1>

    <div class="d-flex gap-2 no-print">
        <a href="<?= e(url('properties')) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i><?= te('action.back') ?>
        </a>
        <a href="<?= e(url('properties/' . $property['id'] . '/edit')) ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil me-1"></i><?= te('action.edit') ?>
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <dl class="row mb-0">
            <?php foreach ($fields as [$label, $value, $isRecordValue]): ?>
                <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te($label) ?></dt>
                <dd class="col-sm-8 col-lg-9 <?= $isRecordValue ? 'record-value' : '' ?>"><?= $value ?></dd>
            <?php endforeach; ?>

            <?php if (!empty($property['notes'])): ?>
                <dt class="col-sm-4 col-lg-3 text-body-secondary fw-normal"><?= te('property.notes') ?></dt>
                <dd class="col-sm-8 col-lg-9" style="white-space:pre-wrap"><?= e($property['notes']) ?></dd>
            <?php endif; ?>
        </dl>
    </div>
</div>

<?php /* Documents: a Pro-plan feature, so the upload control only appears when
         the plan allows it. Files are served through a controller that checks
         ownership — they are not reachable by URL. */ ?>
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-paperclip me-1"></i><?= te('property.documents') ?></span>
        <span class="badge text-bg-light"><?= count($documents) ?></span>
    </div>

    <div class="card-body">
        <?php if (!$documents): ?>
            <p class="text-body-secondary mb-3"><?= te('document.none') ?></p>
        <?php else: ?>
            <ul class="list-group list-group-flush mb-3">
                <?php foreach ($documents as $doc): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center gap-2 px-0">
                        <div class="text-truncate">
                            <i class="bi bi-file-earmark me-1"></i>
                            <a href="<?= e(url('documents/' . $doc['id'])) ?>"><?= e($doc['original_name']) ?></a>
                            <span class="text-body-secondary small ms-2">
                                <?= e(Document::formatBytes((int) $doc['size_bytes'])) ?>
                            </span>
                        </div>

                        <form method="post" action="<?= e(url('documents/' . $doc['id'] . '/delete')) ?>"
                              data-confirm="<?= te('document.delete_confirm') ?>" class="m-0">
                            <?= Csrf::field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($usage['can_upload']): ?>
            <form method="post" action="<?= e(url('properties/' . $property['id'] . '/documents')) ?>"
                  enctype="multipart/form-data" class="row g-2 align-items-end">
                <?= Csrf::field() ?>
                <div class="col-sm">
                    <label for="document" class="form-label"><?= te('document.upload') ?></label>
                    <input type="file" class="form-control" id="document" name="document"
                           accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                    <div class="form-text form-hint">
                        PDF, JPEG, PNG, WebP · max <?= e(Document::formatBytes(UPLOAD_MAX_BYTES)) ?>
                    </div>
                </div>
                <div class="col-sm-auto">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-upload me-1"></i><?= te('action.upload') ?>
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-light border mb-0 small d-flex justify-content-between align-items-center">
                <span><i class="bi bi-lock me-1"></i><?= te('plan.no_documents') ?></span>
                <a href="<?= e(url('plan')) ?>" class="btn btn-sm btn-outline-primary"><?= te('plan.upgrade') ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php /* Delete is POST + CSRF, never a link. */ ?>
<form method="post" action="<?= e(url('properties/' . $property['id'] . '/delete')) ?>"
      data-confirm="<?= te('property.delete_confirm') ?>" class="no-print">
    <?= Csrf::field() ?>
    <button type="submit" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-trash me-1"></i><?= te('action.delete') ?>
    </button>
</form>
