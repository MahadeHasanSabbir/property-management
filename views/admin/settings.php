<?php
/**
 * Site settings.
 *
 * Contact details used to be hardcoded in the markup of index.php and
 * about.php, so changing a phone number meant editing two files.
 *
 * @var array $settings
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Csrf;

$s = static fn(string $key): string => (string) ($settings[$key] ?? '');
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h1 class="h4 mb-3"><?= te('admin.settings') ?></h1>

        <form method="post" action="<?= e(url('admin/settings')) ?>">
            <?= Csrf::field() ?>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="site_name" class="form-label"><?= te('app.name') ?></label>
                        <input type="text" class="form-control" id="site_name"
                               name="site_name" value="<?= e($s('site_name')) ?>" maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="contact_email" class="form-label"><?= te('contact.email') ?></label>
                        <input type="email" class="form-control" id="contact_email"
                               name="contact_email" value="<?= e($s('contact_email')) ?>" maxlength="190">
                    </div>

                    <div class="mb-3">
                        <label for="contact_phone" class="form-label"><?= te('auth.phone') ?></label>
                        <input type="text" class="form-control" id="contact_phone"
                               name="contact_phone" value="<?= e($s('contact_phone')) ?>" maxlength="30">
                    </div>

                    <div class="mb-3">
                        <label for="contact_address" class="form-label"><?= te('home.contact_title') ?></label>
                        <input type="text" class="form-control" id="contact_address"
                               name="contact_address" value="<?= e($s('contact_address')) ?>" maxlength="190">
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1"
                               id="allow_registration" name="allow_registration"
                               <?= ($s('allow_registration') === '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="allow_registration">
                            <?= te('auth.sign_up') ?>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i><?= te('action.save') ?>
            </button>
        </form>
    </div>
</div>
