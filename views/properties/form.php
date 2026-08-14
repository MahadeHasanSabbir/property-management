<?php
/**
 * Create / edit a property record.
 *
 * One form for both create and edit, since the fields are identical.
 *
 * @var array|null $property  null when creating
 * @var array      $moujas
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Csrf;

$editing = $property !== null;
$action  = $editing ? url('properties/' . $property['id']) : url('properties');

/** Current value: submitted-and-rejected input first, then the stored row. */
$v = static function (string $field) use ($property): string {
    $old = old($field);
    if ($old !== '') {
        return $old;
    }
    return (string) ($property[$field] ?? '');
};
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?= $editing ? te('property.edit') : te('property.add') ?></h1>
    <a href="<?= e($editing ? url('properties/' . $property['id']) : url('properties')) ?>"
       class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i><?= te('action.back') ?>
    </a>
</div>

<form method="post" action="<?= e($action) ?>" novalidate>
    <?= Csrf::field() ?>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-4">
                    <label for="deed_no" class="form-label">
                        <?= te('property.deed_no') ?> <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control record-value" id="deed_no"
                           name="deed_no" value="<?= e($v('deed_no')) ?>" maxlength="40" required>
                </div>

                <?php /* Optional: the registration date is often unknown. */ ?>
                <div class="col-md-4">
                    <label for="deed_date" class="form-label"><?= te('property.deed_date') ?></label>
                    <input type="date" class="form-control" id="deed_date"
                           name="deed_date" value="<?= e($v('deed_date')) ?>">
                </div>

                <?php /* Numeric, so it can be summed and range-searched. */ ?>
                <div class="col-md-4">
                    <label for="area_cent" class="form-label"><?= te('property.area') ?></label>
                    <input type="number" step="0.001" min="0" class="form-control"
                           id="area_cent" name="area_cent" value="<?= e($v('area_cent')) ?>">
                </div>

                <div class="col-md-6">
                    <label for="old_owner" class="form-label"><?= te('property.old_owner') ?></label>
                    <input type="text" class="form-control" id="old_owner"
                           name="old_owner" value="<?= e($v('old_owner')) ?>" maxlength="120">
                </div>

                <div class="col-md-6">
                    <label for="new_owner" class="form-label"><?= te('property.new_owner') ?></label>
                    <input type="text" class="form-control" id="new_owner"
                           name="new_owner" value="<?= e($v('new_owner')) ?>" maxlength="120">
                </div>

                <div class="col-md-6">
                    <label for="mouja" class="form-label">
                        <?= te('property.mouja') ?> <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="mouja" name="mouja"
                           value="<?= e($v('mouja')) ?>" maxlength="100" list="mouja-list" required>
                    <datalist id="mouja-list">
                        <?php foreach ($moujas as $mouja): ?>
                            <option value="<?= e($mouja) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
            </div>
        </div>
    </div>

    <?php /* The four comma-separated identifier fields. The raw string is
             stored exactly as typed; property_identifiers is rebuilt from it on
             every save, so searching matches whole tokens rather than
             substrings. */ ?>
    <div class="card mb-3">
        <div class="card-header py-2">
            <span class="small text-body-secondary"><?= te('property.multi_hint') ?></span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php
                $identifierFields = [
                    'dag_current'      => 'property.dag_current',
                    'dag_previous'     => 'property.dag_previous',
                    'khatian_current'  => 'property.khatian_current',
                    'khatian_previous' => 'property.khatian_previous',
                ];
                foreach ($identifierFields as $field => $label):
                    ?>
                    <div class="col-md-6">
                        <label for="<?= e($field) ?>" class="form-label"><?= te($label) ?></label>
                        <input type="text" class="form-control token-input" id="<?= e($field) ?>"
                               name="<?= e($field) ?>" value="<?= e($v($field)) ?>"
                               maxlength="255" placeholder="12, 25, 1232">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <label for="notes" class="form-label"><?= te('property.notes') ?></label>
            <textarea class="form-control" id="notes" name="notes" rows="3"
                      maxlength="2000"><?= e($v('notes')) ?></textarea>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i><?= te('action.save') ?>
        </button>
        <a href="<?= e($editing ? url('properties/' . $property['id']) : url('properties')) ?>"
           class="btn btn-outline-secondary"><?= te('action.cancel') ?></a>
    </div>
</form>
