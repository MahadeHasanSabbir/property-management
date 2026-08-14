<?php
/**
 * Public landing page.
 *
 * Bootstrap 5 equivalents of the legacy markup: `jumbotron` and `well` were
 * removed in Bootstrap 4, `panel` became `card`, and glyphicons were dropped
 * entirely in favour of Bootstrap Icons.
 *
 * @var array $settings
 */

defined('APP_BOOTSTRAPPED') || exit;

use App\Csrf;

$features = [
    ['icon' => 'shield-check',  'title' => 'home.feature1.title', 'body' => 'home.feature1.body'],
    ['icon' => 'search',        'title' => 'home.feature2.title', 'body' => 'home.feature2.body'],
    ['icon' => 'paperclip',     'title' => 'home.feature3.title', 'body' => 'home.feature3.body'],
];
?>
<div class="p-4 p-md-5 mb-4 rounded-3 bg-body-tertiary border">
    <div class="col-lg-8">
        <h1 class="display-6 fw-bold"><?= te('home.welcome') ?></h1>
        <p class="fs-5 text-body-secondary"><?= te('home.intro') ?></p>
        <?php if (!App\Auth::check()): ?>
            <a href="<?= e(url('register')) ?>" class="btn btn-primary">
                <i class="bi bi-arrow-right-circle me-1"></i><?= te('home.get_started') ?>
            </a>
        <?php endif; ?>
        <a href="<?= e(url('about')) ?>" class="btn btn-outline-secondary"><?= te('home.learn_more') ?></a>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ($features as $feature): ?>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <i class="bi bi-<?= e($feature['icon']) ?> fs-3 text-primary"></i>
                    <h2 class="h5 card-title mt-2"><?= te($feature['title']) ?></h2>
                    <p class="card-text text-body-secondary"><?= te($feature['body']) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-md-5">
        <h2 class="h4"><?= te('home.contact_title') ?></h2>
        <ul class="list-unstyled text-body-secondary">
            <li class="mb-2">
                <i class="bi bi-envelope me-2"></i>
                <a href="mailto:<?= e($settings['contact_email'] ?? '') ?>">
                    <?= e($settings['contact_email'] ?? '') ?>
                </a>
            </li>
            <li class="mb-2"><i class="bi bi-telephone me-2"></i><?= e($settings['contact_phone'] ?? '') ?></li>
            <li class="mb-2"><i class="bi bi-geo-alt me-2"></i><?= e($settings['contact_address'] ?? '') ?></li>
        </ul>
    </div>

    <div class="col-md-7">
        <h2 class="h4"><?= te('home.message_title') ?></h2>
        <?php /* POST + CSRF. The legacy form posted to itself and the handler
                 inlined $_POST straight into an INSERT with no escaping. */ ?>
        <form method="post" action="<?= e(url('contact')) ?>" novalidate>
            <?= Csrf::field() ?>

            <div class="mb-3">
                <label for="c-name" class="form-label"><?= te('contact.name') ?></label>
                <input type="text" class="form-control" id="c-name" name="name"
                       value="<?= e(old('name')) ?>" maxlength="60" required>
            </div>

            <div class="mb-3">
                <label for="c-email" class="form-label"><?= te('contact.email') ?></label>
                <input type="email" class="form-control" id="c-email" name="email"
                       value="<?= e(old('email')) ?>" maxlength="190" required>
            </div>

            <div class="mb-3">
                <label for="c-message" class="form-label"><?= te('contact.message') ?></label>
                <textarea class="form-control" id="c-message" name="message" rows="5"
                          maxlength="2000" required><?= e(old('message')) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send me-1"></i><?= te('contact.send') ?>
            </button>
        </form>
    </div>
</div>
