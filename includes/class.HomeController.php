<?php
/**
 * Public pages: home, about, the inheritance calculator, and the contact form.
 *
 * Open to everyone, signed in or not — there is no reason a signed-in visitor
 * should be redirected away from the home page.
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
     * Store a contact message. Public and unauthenticated, so every field is
     * validated and bound here and escaped again where it is displayed.
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
