<?php
/**
 * The single application layout.
 *
 * There is exactly one <!DOCTYPE>, one <head> and one navbar in the project,
 * and they live here.
 *
 * @var string      $content  rendered view body
 * @var string|null $title    page title (already translated)
 * @var string|null $active   active nav key
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Lang;

$title  = $title  ?? t('app.name');
$active = $active ?? '';
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
<body>
    <?= App\View::renderPartial('partials.navbar', ['active' => $active]) ?>

    <main class="py-4">
        <div class="container">
            <?= App\View::renderPartial('partials.flash') ?>
            <?= $content ?>
        </div>
    </main>

    <footer class="footer border-top py-3 mt-4">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="text-body-secondary small">
                &copy; <?= date('Y') ?> <?= te('app.name') ?>. <?= te('footer.rights') ?>
            </span>
            <?= App\View::renderPartial('partials.lang-switcher') ?>
        </div>
    </footer>

    <script src="<?= e(asset('vendor/bootstrap/js/bootstrap.bundle.min.js')) ?>"></script>
    <script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
