/* ---------------------------------------------------------------------------
   Property Management — application scripts.

   No jQuery. The legacy style/js/jscript.js used none either; Bootstrap 3's JS
   was only doing navbar collapse and alert dismissal, both of which Bootstrap
   5's bundle handles natively.

   The legacy file also held ten near-identical confirm() wrappers (permit,
   permit1..4, apermit, apermit1..2, giveinfo, givealert) which were the ONLY
   thing standing in front of destructive GET endpoints. Those endpoints are now
   POST + CSRF and authorised server-side; the confirmation below is a courtesy,
   not a control.
   --------------------------------------------------------------------------- */

(function () {
    'use strict';

    /**
     * Ask before submitting anything destructive.
     * Usage: <form data-confirm="Delete this record?"> ... </form>
     */
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        var message = form.getAttribute('data-confirm');
        if (message && !window.confirm(message)) {
            event.preventDefault();
            return;
        }

        // Nothing is being sent, so there is nothing to guard against. A form
        // handled entirely in JavaScript cancels its own submit event; locking
        // its button would leave the control dead until the page reloaded.
        // This listener is on `document`, so the form's own handler has already
        // run by now and defaultPrevented reflects its decision.
        if (event.defaultPrevented) {
            return;
        }

        // Stop double submission on slow connections — the legacy app had no
        // guard, so an impatient second click could create a duplicate record.
        var submit = form.querySelector('[type="submit"]');
        if (submit && !form.hasAttribute('data-no-lock')) {
            window.setTimeout(function () {
                submit.disabled = true;
                submit.classList.add('disabled');
            }, 0);
        }
    });

    /** Submit the closest form when a control changes (page size, sort). */
    document.addEventListener('change', function (event) {
        var el = event.target;
        if (el && el.hasAttribute && el.hasAttribute('data-submit-on-change')) {
            var form = el.closest('form');
            if (form) form.submit();
        }
    });

    /**
     * Normalise comma-separated number inputs on blur: "1232 , 25 ,12" becomes
     * "1232, 25, 12". Legacy data contains exactly that spacing, so tolerate it
     * on input and tidy it for display. The server splits and validates
     * regardless — this is presentation only.
     */
    document.addEventListener('blur', function (event) {
        var el = event.target;
        if (!el || !el.classList || !el.classList.contains('token-input')) return;

        var tokens = el.value.split(',')
            .map(function (t) { return t.trim(); })
            .filter(function (t) { return t !== ''; });

        el.value = tokens.join(', ');
    }, true);

    /** Auto-dismiss transient success notices; leave errors on screen. */
    window.setTimeout(function () {
        document.querySelectorAll('.alert-success[data-auto-dismiss]').forEach(function (alert) {
            if (window.bootstrap && window.bootstrap.Alert) {
                window.bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        });
    }, 6000);
})();
