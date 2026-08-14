<?php
/**
 * Inheritance / land-distribution calculator.
 *
 * Shares (preserved from the original implementation): the wife takes 1/8, the
 * remainder is divided so that each son receives two shares to each daughter's
 * one.
 *
 * This existed twice in the legacy tree — distribution.php and
 * profile/distribution.php were ~88% identical, differing only in the session
 * guard and asset path prefixes, so every change had to be made twice. There is
 * one copy now, reachable signed in or not.
 *
 * The maths stays client-side, as before: it involves no stored data, so a
 * round trip would only make it slower.
 */

defined('APP_BOOTSTRAPPED') || exit;
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h1 class="h3"><?= te('calc.title') ?></h1>
        <p class="text-body-secondary"><?= te('calc.intro') ?></p>

        <div class="card">
            <div class="card-body">
                <?php /* data-no-lock as well as the defaultPrevented check in
                         app.js: this form never submits, so its button must
                         never be disabled. */ ?>
                <form id="calc-form" class="row g-3" novalidate data-no-lock>
                    <div class="col-sm-6">
                        <label for="calc-area" class="form-label"><?= te('calc.total_area') ?></label>
                        <input type="number" class="form-control" id="calc-area"
                               min="0" step="0.001" value="0" required>
                    </div>
                    <div class="col-sm-6 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="calc-wife" checked>
                            <label class="form-check-label" for="calc-wife"><?= te('calc.wife') ?></label>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="calc-sons" class="form-label"><?= te('calc.sons') ?></label>
                        <input type="number" class="form-control" id="calc-sons" min="0" step="1" value="0">
                    </div>
                    <div class="col-sm-6">
                        <label for="calc-daughters" class="form-label"><?= te('calc.daughters') ?></label>
                        <input type="number" class="form-control" id="calc-daughters" min="0" step="1" value="0">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calculator me-1"></i><?= te('calc.calculate') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="calc-result" class="mt-4 d-none">
            <h2 class="h5"><?= te('calc.result') ?></h2>
            <div class="table-scroll">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th scope="col"><?= te('admin.user') ?></th>
                            <th scope="col" class="text-end"><?= te('calc.share') ?></th>
                        </tr>
                    </thead>
                    <tbody id="calc-rows"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var LABELS = {
        wife:      <?= json_encode(t('calc.wife'), JSON_UNESCAPED_UNICODE) ?>,
        son:       <?= json_encode(t('calc.each_son'), JSON_UNESCAPED_UNICODE) ?>,
        daughter:  <?= json_encode(t('calc.each_daughter'), JSON_UNESCAPED_UNICODE) ?>,
        remaining: <?= json_encode(t('calc.remaining'), JSON_UNESCAPED_UNICODE) ?>,
        total:     <?= json_encode(t('calc.total_area'), JSON_UNESCAPED_UNICODE) ?>
    };

    var form   = document.getElementById('calc-form');
    var result = document.getElementById('calc-result');
    var rows   = document.getElementById('calc-rows');

    function fmt(n) {
        return (Math.round(n * 1000) / 1000).toLocaleString(undefined, { maximumFractionDigits: 3 });
    }

    function addRow(label, value, count, strong) {
        var tr = document.createElement('tr');
        var th = document.createElement('td');
        var td = document.createElement('td');

        th.textContent = count ? label + ' (×' + count + ')' : label;
        td.textContent = fmt(value);
        td.className = 'text-end record-value';

        if (strong) {
            tr.className = 'table-light fw-semibold';
        }
        tr.appendChild(th);
        tr.appendChild(td);
        rows.appendChild(tr);
    }

    function calculate() {
        var total     = parseFloat(document.getElementById('calc-area').value) || 0;
        var hasWife   = document.getElementById('calc-wife').checked;
        var sons      = parseInt(document.getElementById('calc-sons').value, 10) || 0;
        var daughters = parseInt(document.getElementById('calc-daughters').value, 10) || 0;

        rows.innerHTML = '';

        if (total <= 0) {
            result.classList.add('d-none');
            return;
        }

        var wifeShare = hasWife ? total / 8 : 0;
        var rest      = total - wifeShare;

        // Each son counts as two shares, each daughter as one.
        var units   = (sons * 2) + daughters;
        var perUnit = units > 0 ? rest / units : 0;

        if (hasWife)       { addRow(LABELS.wife, wifeShare, 0); }
        if (sons > 0)      { addRow(LABELS.son, perUnit * 2, sons); }
        if (daughters > 0) { addRow(LABELS.daughter, perUnit, daughters); }

        // With no children the remainder belongs to heirs this calculator does
        // not cover. Show it rather than letting the figures quietly fail to
        // add up to the total.
        if (units === 0 && rest > 0) {
            addRow(LABELS.remaining, rest, 0);
        }

        addRow(LABELS.total, total, 0, true);
        result.classList.remove('d-none');
    }

    // The button still works, but the result also follows the inputs live, so
    // the answer is never stale relative to what is on screen.
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        calculate();
    });
    form.addEventListener('input', calculate);
    form.addEventListener('change', calculate);
})();
</script>
