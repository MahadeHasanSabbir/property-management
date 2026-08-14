<?php
/**
 * Narrow, centred layout for sign-in, registration and password reset.
 * Deliberately has no navigation: nothing on these pages should invite a
 * half-authenticated visitor to wander.
 *
 * @var string $content
 * @var string|null $title
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Lang;

$title = $title ?? t('auth.sign_in');
?>
<!DOCTYPE html>
<html lang="<?= e(Lang::code()) ?>" dir="<?= e(Lang::dir()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="same-origin">
    <title><?= e($title) ?> · <?= te('app.name') ?></title>
    <link rel="stylesheet" href="<?= e(asset('vendor/bootstrap/css/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('vendor/bootstrap-icons/bootstrap-icons.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="bg-body-tertiary">
    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-sm-10 col-md-7 col-lg-5">

                    <div class="text-center mb-4">
                        <a href="<?= e(url('')) ?>" class="text-decoration-none">
                            <i class="bi bi-house-door-fill fs-2 text-primary"></i>
                            <div class="h5 mt-2 mb-0"><?= te('app.name') ?></div>
                        </a>
                    </div>

                    <?= App\View::renderPartial('partials.flash') ?>

                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <?= $content ?>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <?= App\View::renderPartial('partials.lang-switcher') ?>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script src="<?= e(asset('vendor/bootstrap/js/bootstrap.bundle.min.js')) ?>"></script>
    <script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
