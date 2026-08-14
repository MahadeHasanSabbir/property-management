<?php
/**
 * Language switcher. Appears once, in the footer, so it is reachable from every
 * page including the sign-in screen.
 *
 * ?setlang=xx is handled in bootstrap.php before any output, so the Set-Cookie
 * header is still valid when it fires.
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Lang;

$current = Lang::code();
?>
<div class="btn-group btn-group-sm" role="group" aria-label="<?= te('nav.language') ?>">
    <?php foreach (Lang::available() as $code): ?>
        <a class="btn <?= $code === $current ? 'btn-secondary' : 'btn-outline-secondary' ?>"
           href="<?= e(url(ltrim((new App\Request())->path(), '/'), ['setlang' => $code])) ?>"
           <?= $code === $current ? 'aria-current="true"' : '' ?>>
            <?= e(Lang::label($code)) ?>
        </a>
    <?php endforeach; ?>
</div>
