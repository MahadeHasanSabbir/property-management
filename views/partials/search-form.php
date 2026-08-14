<?php
/**
 * Property search filters.
 *
 * Submits by GET so results stay linkable; url() folds the values into the
 * encoded ?q= token. Every value is re-validated server-side in
 * PropertyController::readFilters(), because the token is decoded into $_GET
 * and can therefore be crafted.
 *
 * @var array  $filters
 * @var array  $moujas
 * @var string $basePath    where results are displayed (reset link)
 * @var string $formAction  where the filters are posted
 */

defined('APP_BOOTSTRAPPED') || exit;

$f = $filters;
$formAction = $formAction ?? $basePath;
?>
<?php /* POST, then the controller redirects to the encoded GET equivalent.
         A plain GET form would put every filter in the URL as readable
         key=value pairs, bypassing the ?q= token entirely. This way the
         result page is still a bookmarkable GET, and it needs no JavaScript. */ ?>
<form method="post" action="<?= e(url($formAction)) ?>" class="card mb-4" id="search-form" data-no-lock>
    <?= App\Csrf::field() ?>
    <div class="card-body">
        <div class="row g-3">

            <?php /* Dag: matches current OR previous unless narrowed. The old
                     form forced an either/or radio choice. */ ?>
            <div class="col-md-3">
                <label for="f-dag" class="form-label"><?= te('search.dag') ?></label>
                <input type="text" inputmode="numeric" class="form-control token-input"
                       id="f-dag" name="dag" value="<?= e($f['dag']) ?>" maxlength="20">
            </div>
            <div class="col-md-3">
                <label for="f-dag-scope" class="form-label"><?= te('search.scope') ?></label>
                <select class="form-select" id="f-dag-scope" name="dag_scope">
                    <option value="any"      <?= $f['dag_scope'] === 'any'      ? 'selected' : '' ?>><?= te('search.scope_any') ?></option>
                    <option value="current"  <?= $f['dag_scope'] === 'current'  ? 'selected' : '' ?>><?= te('search.scope_current') ?></option>
                    <option value="previous" <?= $f['dag_scope'] === 'previous' ? 'selected' : '' ?>><?= te('search.scope_previous') ?></option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="f-khatian" class="form-label"><?= te('search.khatian') ?></label>
                <input type="text" inputmode="numeric" class="form-control token-input"
                       id="f-khatian" name="khatian" value="<?= e($f['khatian']) ?>" maxlength="20">
            </div>
            <div class="col-md-3">
                <label for="f-khatian-scope" class="form-label"><?= te('search.scope') ?></label>
                <select class="form-select" id="f-khatian-scope" name="khatian_scope">
                    <option value="any"      <?= $f['khatian_scope'] === 'any'      ? 'selected' : '' ?>><?= te('search.scope_any') ?></option>
                    <option value="current"  <?= $f['khatian_scope'] === 'current'  ? 'selected' : '' ?>><?= te('search.scope_current') ?></option>
                    <option value="previous" <?= $f['khatian_scope'] === 'previous' ? 'selected' : '' ?>><?= te('search.scope_previous') ?></option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="f-deed" class="form-label"><?= te('property.deed_no') ?></label>
                <input type="text" class="form-control record-value" id="f-deed"
                       name="deed_no" value="<?= e($f['deed_no']) ?>" maxlength="40">
            </div>

            <?php /* Suggestions drawn from the user's own moujas, so an exact
                     match does not depend on retyping it identically. */ ?>
            <div class="col-md-3">
                <label for="f-mouja" class="form-label"><?= te('property.mouja') ?></label>
                <input type="text" class="form-control" id="f-mouja" name="mouja"
                       value="<?= e($f['mouja']) ?>" maxlength="100" list="mouja-options">
                <datalist id="mouja-options">
                    <?php foreach ($moujas as $mouja): ?>
                        <option value="<?= e($mouja) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="col-md-3">
                <label for="f-owner" class="form-label"><?= te('search.owner') ?></label>
                <input type="text" class="form-control" id="f-owner" name="owner"
                       value="<?= e($f['owner']) ?>" maxlength="120">
            </div>
            <div class="col-md-3">
                <label for="f-owner-mode" class="form-label"><?= te('search.owner_mode') ?></label>
                <select class="form-select" id="f-owner-mode" name="owner_mode">
                    <option value="starts"   <?= $f['owner_mode'] === 'starts'   ? 'selected' : '' ?>><?= te('search.owner_starts') ?></option>
                    <option value="contains" <?= $f['owner_mode'] === 'contains' ? 'selected' : '' ?>><?= te('search.owner_contains') ?></option>
                </select>
            </div>

            <?php /* Range filters, possible only now that area is DECIMAL and
                     the date column is a real nullable DATE. */ ?>
            <div class="col-md-3">
                <label for="f-area-min" class="form-label"><?= te('search.area_min') ?></label>
                <input type="number" step="0.001" min="0" class="form-control"
                       id="f-area-min" name="area_min" value="<?= e($f['area_min']) ?>">
            </div>
            <div class="col-md-3">
                <label for="f-area-max" class="form-label"><?= te('search.area_max') ?></label>
                <input type="number" step="0.001" min="0" class="form-control"
                       id="f-area-max" name="area_max" value="<?= e($f['area_max']) ?>">
            </div>
            <div class="col-md-3">
                <label for="f-date-from" class="form-label"><?= te('search.date_from') ?></label>
                <input type="date" class="form-control" id="f-date-from"
                       name="date_from" value="<?= e($f['date_from']) ?>">
            </div>
            <div class="col-md-3">
                <label for="f-date-to" class="form-label"><?= te('search.date_to') ?></label>
                <input type="date" class="form-control" id="f-date-to"
                       name="date_to" value="<?= e($f['date_to']) ?>">
            </div>

            <?php /* Defaults to AND, so each additional filter narrows the
                     result rather than widening it. */ ?>
            <div class="col-md-3">
                <label for="f-mode" class="form-label"><?= te('search.mode') ?></label>
                <select class="form-select" id="f-mode" name="mode">
                    <option value="all" <?= $f['mode'] === 'all' ? 'selected' : '' ?>><?= te('search.mode_all') ?></option>
                    <option value="any" <?= $f['mode'] === 'any' ? 'selected' : '' ?>><?= te('search.mode_any') ?></option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="f-per" class="form-label"><?= te('search.per_page') ?></label>
                <select class="form-select" id="f-per" name="per">
                    <?php foreach (array_map('intval', explode(',', PAGE_SIZES)) as $size): ?>
                        <option value="<?= $size ?>" <?= (int) $f['per'] === $size ? 'selected' : '' ?>><?= $size ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="hidden" name="sort" value="<?= e($f['sort']) ?>">
            <input type="hidden" name="dir"  value="<?= e($f['dir']) ?>">
        </div>
    </div>

    <div class="card-footer d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-search me-1"></i><?= te('action.search') ?>
        </button>
        <a href="<?= e(url($basePath)) ?>" class="btn btn-outline-secondary">
            <?= te('action.reset') ?>
        </a>
    </div>
</form>
