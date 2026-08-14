<?php
/**
 * Everything about the signed-in person: registration, sign-in and sign-out,
 * password reset, the customer dashboard, and profile management.
 *
 * Merged into one controller on purpose — these actions share validation rules
 * and redirect targets, and splitting them across four classes would spread one
 * concern thinly.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class AccountController
{
    // --- Sign in -------------------------------------------------------------

    public function showLogin(): void
    {
        View::render('auth.login', [
            'title' => t('auth.sign_in'),
        ], 'auth');
    }

    public function login(): void
    {
        $email    = post('email');
        $password = $_POST['password'] ?? '';

        $result = Auth::attempt($email, (string) $password, false);

        if (!$result['ok']) {
            flash_old(['email' => $email]);
            flash('danger', $this->loginError($result));
            redirect('login');
        }

        $user = Auth::user();
        flash('success', t('auth.welcome_back', ['name' => $user['name']]));

        // Return the visitor to whatever they were trying to reach.
        $intended = $_SESSION['intended'] ?? null;
        unset($_SESSION['intended']);

        if ($intended && $intended !== '/login') {
            redirect(ltrim($intended, '/'));
        }
        redirect(Auth::isStaff() ? 'admin' : 'dashboard');
    }

    /** Translate an attempt() failure into a message. */
    private function loginError(array $result): string
    {
        switch ($result['error']) {
            case 'throttled':
                return t('auth.throttled', ['minutes' => (int) ceil($result['wait'] / 60)]);
            case 'inactive':
                return t('auth.account_inactive');
            case 'not_staff':
                return t('auth.not_staff');
            default:
                return t('auth.invalid');
        }
    }

    public function logout(): void
    {
        if (Auth::check()) {
            AuditLog::record('auth.logout', 'user', (string) Auth::id());
        }

        // Genuinely destroys the session, unlike the legacy logout.php which
        // only set a flash and redirected — leaving the visitor signed in if
        // they navigated anywhere other than the login page.
        Auth::logout();

        flash('success', t('auth.signed_out'));
        redirect('login');
    }

    // --- Registration --------------------------------------------------------

    public function showRegister(): void
    {
        if (!Setting::bool('allow_registration', true)) {
            throw new HttpException(403);
        }

        View::render('auth.register', [
            'title' => t('auth.sign_up'),
        ], 'auth');
    }

    public function register(): void
    {
        if (!Setting::bool('allow_registration', true)) {
            throw new HttpException(403);
        }

        $name     = post('name');
        $email    = post('email');
        $phoneRaw = post('phone');
        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirm'] ?? '');

        $phone  = $phoneRaw === '' ? null : normalize_phone($phoneRaw);
        $errors = [];

        if (!valid_name($name)) {
            $errors[] = t('valid.name');
        }
        if (!valid_email($email)) {
            $errors[] = t('valid.email');
        } elseif (User::emailTaken($email)) {
            $errors[] = t('valid.email_taken');
        }
        if ($phoneRaw !== '' && $phone === null) {
            $errors[] = t('valid.phone');
        }
        if (!valid_password($password)) {
            $errors[] = t('valid.password');
        } elseif (!hash_equals($password, $confirm)) {
            $errors[] = t('auth.password_mismatch');
        }

        if ($errors) {
            flash_old(['name' => $name, 'email' => $email, 'phone' => $phoneRaw]);
            foreach ($errors as $error) {
                flash('danger', $error);
            }
            redirect('register');
        }

        $plan = Plan::defaultCode();

        // One transaction. The legacy registration ran three statements with no
        // transaction at all, so a failure partway through left a user row whose
        // data table had never been created — an account broken forever.
        $userId = Database::transaction(static function () use ($name, $email, $phone, $password, $plan) {
            return User::create([
                'user_code' => User::nextUserCode(),
                'name'      => $name,
                'email'     => $email,
                'phone'     => $phone,
                // NOTE: the password is hashed as typed. The legacy code ran
                // mysqli_real_escape_string() over it first, so any password
                // containing a quote or backslash was hashed in its escaped
                // form.
                'password'  => password_hash($password, PASSWORD_BCRYPT),
                'role'      => 'customer',
                'plan_code' => $plan,
                'status'    => 'active',
                'locale'    => Lang::code(),
            ]);
        });

        AuditLog::record('user.register', 'user', (string) $userId, ['email' => $email]);

        flash('success', t('auth.registered'));
        redirect('login');
    }

    // --- Password reset ------------------------------------------------------

    public function showForgot(): void
    {
        View::render('auth.forgot', ['title' => t('auth.reset_title')], 'auth');
    }

    public function sendReset(): void
    {
        $email = post('email');
        $user  = valid_email($email) ? User::findByEmail($email) : null;

        if ($user && $user['status'] === 'active') {
            $token = PasswordReset::issue((int) $user['id']);
            Mailer::sendPasswordReset($user, $token);
            AuditLog::record('auth.reset_requested', 'user', (string) $user['id']);
        }

        // Always the same response, whether or not the address is registered —
        // otherwise this endpoint enumerates accounts.
        flash('info', t('auth.reset_sent'));
        redirect('login');
    }

    public function showReset(string $token): void
    {
        if (PasswordReset::resolve($token) === null) {
            flash('danger', t('auth.reset_invalid'));
            redirect('login');
        }

        View::render('auth.reset', [
            'title' => t('auth.reset_title'),
            'token' => $token,
        ], 'auth');
    }

    public function resetPassword(string $token): void
    {
        $reset = PasswordReset::resolve($token);
        if ($reset === null) {
            flash('danger', t('auth.reset_invalid'));
            redirect('login');
        }

        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirm'] ?? '');

        if (!valid_password($password)) {
            flash('danger', t('valid.password'));
            redirect('reset-password/' . $token);
        }
        if (!hash_equals($password, $confirm)) {
            flash('danger', t('auth.password_mismatch'));
            redirect('reset-password/' . $token);
        }

        Database::transaction(static function () use ($reset, $password) {
            User::updatePassword((int) $reset['user_id'], password_hash($password, PASSWORD_BCRYPT));
            PasswordReset::consume((int) $reset['id']);
        });

        AuditLog::record('auth.reset_completed', 'user', (string) $reset['user_id']);

        flash('success', t('auth.reset_done'));
        redirect('login');
    }

    // --- Dashboard -----------------------------------------------------------

    public function dashboard(): void
    {
        $user  = Auth::user();
        $usage = PlanLimit::usage($user);

        View::render('account.dashboard', [
            'title'   => t('nav.dashboard'),
            'active'  => 'dashboard',
            'user'    => $user,
            'usage'   => $usage,
            'recent'  => Property::recent((int) $user['id'], 5),
        ]);
    }

    // --- Profile -------------------------------------------------------------

    public function profile(): void
    {
        $user = Auth::user();

        View::render('account.profile', [
            'title'  => t('profile.title'),
            'active' => 'profile',
            'user'   => $user,
            'usage'  => Auth::isCustomer() ? PlanLimit::usage($user) : null,
        ]);
    }

    public function editProfile(): void
    {
        View::render('account.profile-edit', [
            'title'  => t('profile.edit'),
            'active' => 'profile',
            'user'   => Auth::user(),
        ]);
    }

    public function updateProfile(): void
    {
        $user     = Auth::user();
        $name     = post('name');
        $email    = post('email');
        $phoneRaw = post('phone');
        $locale   = post('locale', DEFAULT_LANG);

        $phone  = $phoneRaw === '' ? null : normalize_phone($phoneRaw);
        $errors = [];

        if (!valid_name($name)) {
            $errors[] = t('valid.name');
        }
        if (!valid_email($email)) {
            $errors[] = t('valid.email');
        } elseif (User::emailTaken($email, (int) $user['id'])) {
            $errors[] = t('valid.email_taken');
        }
        if ($phoneRaw !== '' && $phone === null) {
            $errors[] = t('valid.phone');
        }
        if (!in_array($locale, Lang::available(), true)) {
            $locale = DEFAULT_LANG;
        }

        if ($errors) {
            flash_old(['name' => $name, 'email' => $email, 'phone' => $phoneRaw]);
            foreach ($errors as $error) {
                flash('danger', $error);
            }
            redirect('profile/edit');
        }

        User::updateProfile((int) $user['id'], [
            'name'   => $name,
            'email'  => $email,
            'phone'  => $phone,
            'locale' => $locale,
        ]);

        $_SESSION['locale'] = $locale;
        AuditLog::record('profile.update', 'user', (string) $user['id']);

        flash('success', t('profile.updated'));
        redirect('profile');
    }

    public function editPassword(): void
    {
        View::render('account.password', [
            'title'  => t('profile.change_password'),
            'active' => 'profile',
            'user'   => Auth::user(),
        ]);
    }

    public function updatePassword(): void
    {
        $user    = Auth::user();
        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');

        if (!password_verify($current, $user['password'])) {
            flash('danger', t('auth.password_wrong'));
            redirect('profile/password');
        }
        if (!valid_password($new)) {
            flash('danger', t('valid.password'));
            redirect('profile/password');
        }
        if (!hash_equals($new, $confirm)) {
            flash('danger', t('auth.password_mismatch'));
            redirect('profile/password');
        }

        User::updatePassword((int) $user['id'], password_hash($new, PASSWORD_BCRYPT));
        AuditLog::record('profile.password_change', 'user', (string) $user['id']);

        flash('success', t('auth.password_changed'));
        redirect('profile');
    }

    /**
     * Delete one's own account.
     *
     * POST-only and CSRF-protected. The legacy equivalent ran on a bare GET
     * with no parameters at all, so an <img src> pointing at it destroyed the
     * signed-in visitor's account and dropped their data table.
     */
    public function deleteAccount(): void
    {
        $user     = Auth::user();
        $password = (string) ($_POST['password'] ?? '');

        if (!password_verify($password, $user['password'])) {
            flash('danger', t('auth.password_wrong'));
            redirect('profile');
        }

        $id = (int) $user['id'];

        Database::transaction(static function () use ($id) {
            Document::deleteAllForUser($id);
            Property::deleteAllForUser($id);
            User::softDelete($id);
        });

        AuditLog::record('user.self_delete', 'user', (string) $id);

        Auth::logout();
        flash('success', t('profile.deleted'));
        redirect('');
    }

    // --- Plan ----------------------------------------------------------------

    public function plan(): void
    {
        $user = Auth::user();

        View::render('account.plan', [
            'title'  => t('plan.title'),
            'active' => 'plan',
            'user'   => $user,
            'usage'  => PlanLimit::usage($user),
            'plans'  => Plan::all(),
        ]);
    }
}
