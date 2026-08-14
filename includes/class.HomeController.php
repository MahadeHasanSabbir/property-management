<?php
/**
 * Public pages: home, about, the inheritance calculator, and the contact form.
 *
 * Legacy note: index.php, about.php and distribution.php all redirected a
 * signed-in visitor away to /auth. That was a UX defect dressed up as access
 * control — there is no reason a signed-in user cannot read the home page — so
 * these pages are now open to everyone.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class HomeController
{
    public function index(): void
    {
        Tracker::record('/');

        View::render('home.index', [
            'title'    => t('home.welcome'),
            'active'   => 'home',
            'settings' => Setting::all(),
        ]);
    }

    public function about(): void
    {
        Tracker::record('/about');

        View::render('home.about', [
            'title'    => t('nav.about'),
            'active'   => 'about',
            'settings' => Setting::all(),
        ]);
    }

    public function calculator(): void
    {
        Tracker::record('/calculator');

        View::render('home.calculator', [
            'title'  => t('calc.title'),
            'active' => 'calculator',
        ]);
    }

    /**
     * Store a contact message.
     *
     * The legacy version took $_POST['name'], ['email'] and ['text'] with no
     * escaping whatsoever and interpolated all three into an INSERT on a public
     * endpoint. Here everything is validated, bound, and escaped again on
     * output in the admin panel.
     */
    public function storeMessage(): void
    {
        $name    = post('name');
        $email   = post('email');
        $message = post('message');

        $errors = [];
        if (!valid_name($name)) {
            $errors[] = t('valid.name');
        }
        if (!valid_email($email)) {
            $errors[] = t('valid.email');
        }
        if ($message === '' || mb_strlen($message) > 2000) {
            $errors[] = t('valid.required', ['field' => t('contact.message')]);
        }

        if ($errors) {
            flash_old(['name' => $name, 'email' => $email, 'message' => $message]);
            foreach ($errors as $error) {
                flash('danger', $error);
            }
            redirect('');
        }

        Contact::create($name, $email, $message);
        flash('success', t('contact.sent'));
        redirect('');
    }
}
