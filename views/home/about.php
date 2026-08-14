<?php
/**
 * About page. Static prose, so it needs no controller logic of its own.
 *
 * @var array $settings
 */

defined('APP_BOOTSTRAPPED') || exit;
?>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <h1 class="h3 mb-3"><?= te('nav.about') ?></h1>

        <p class="lead text-body-secondary"><?= te('app.tagline') ?></p>
        <p><?= te('home.intro') ?></p>

        <div class="row g-3 my-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6"><?= te('home.feature1.title') ?></h2>
                        <p class="card-text text-body-secondary mb-0"><?= te('home.feature1.body') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6"><?= te('home.feature2.title') ?></h2>
                        <p class="card-text text-body-secondary mb-0"><?= te('home.feature2.body') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6"><?= te('home.feature3.title') ?></h2>
                        <p class="card-text text-body-secondary mb-0"><?= te('home.feature3.body') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6"><?= te('calc.title') ?></h2>
                        <p class="card-text text-body-secondary mb-0"><?= te('calc.intro') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="h5 mt-4"><?= te('home.contact_title') ?></h2>
        <ul class="list-unstyled text-body-secondary">
            <li class="mb-2">
                <i class="bi bi-envelope me-2"></i>
                <a href="mailto:<?= e($settings['contact_email'] ?? '') ?>"><?= e($settings['contact_email'] ?? '') ?></a>
            </li>
            <li class="mb-2"><i class="bi bi-telephone me-2"></i><?= e($settings['contact_phone'] ?? '') ?></li>
            <li class="mb-2"><i class="bi bi-geo-alt me-2"></i><?= e($settings['contact_address'] ?? '') ?></li>
        </ul>
    </div>
</div>
