<?php
/**
 * Staff and administrator area: sign-in, dashboard, contact messages, the audit
 * log, and site settings.
 *
 * Admins are ordinary rows in `users` with role = 'admin'. The separate
 * sign-in URL exists so admin access is not advertised on the public login
 * form; it is not the security boundary. That is the role check in the route
 * middleware, which applies whichever page you signed in through.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class AdminController
{
    // --- Sign in -------------------------------------------------------------

    public function showLogin(): void
    {
        View::render('admin.login', [
            'title' => t('auth.sign_in_admin'),
        ], 'auth');
    }

    public function login(): void
    {
        $email    = post('email');
        $password = (string) ($_POST['password'] ?? '');

        // staffOnly: a valid customer account is refused here.
        $result = Auth::attempt($email, $password, true);

        if (!$result['ok']) {
            flash_old(['email' => $email]);

            switch ($result['error']) {
                case 'throttled':
                    flash('danger', t('auth.throttled', ['minutes' => (int) ceil($result['wait'] / 60)]));
                    break;
                case 'not_staff':
                    flash('danger', t('auth.not_staff'));
                    break;
                case 'inactive':
                    flash('danger', t('auth.account_inactive'));
                    break;
                default:
                    flash('danger', t('auth.invalid'));
            }
            redirect('admin/login');
        }

        AuditLog::record('auth.admin_login', 'user', (string) Auth::id());
        flash('success', t('auth.welcome_back', ['name' => Auth::user()['name']]));
        redirect('admin');
    }

    // --- Dashboard -----------------------------------------------------------

    /**
     * Tiles show counts that mean something.
     *
     * The legacy dashboard had six tiles and most were wrong: "Number of active
     * user" ran SELECT COUNT(ID) FROM user (that is every user, not active
     * ones); "registered user" read a drifting VARCHAR(3) counter instead of
     * counting; and the "logged in today" tile compared a zero-padded date
     * against values written with a non-padded day format, so it read 0 on the
     * 1st through the 9th of every month.
     */
    public function dashboard(): void
    {
        View::render('admin.dashboard', [
            'title'  => t('admin.dashboard'),
            'active' => 'admin',
            'stats'  => [
                'users'      => (int) Database::scalar(
                    "SELECT COUNT(*) FROM users WHERE deleted_at IS NULL AND role = 'customer'"
                ),
                'properties' => (int) Database::scalar(
                    'SELECT COUNT(*) FROM properties WHERE deleted_at IS NULL'
                ),
                'documents'  => (int) Database::scalar('SELECT COUNT(*) FROM property_documents'),
                'signups'    => (int) Database::scalar(
                    'SELECT COUNT(*) FROM users
                      WHERE deleted_at IS NULL AND created_at > (NOW() - INTERVAL 30 DAY)'
                ),
                'views'      => Tracker::viewsSince(30),
                'messages'   => Contact::countAll('new'),
            ],
            'recentUsers' => Database::all(
                "SELECT id, name, email, user_code, created_at
                   FROM users
                  WHERE deleted_at IS NULL AND role = 'customer'
                  ORDER BY created_at DESC LIMIT 5"
            ),
        ]);
    }

    // --- Contact messages ----------------------------------------------------

    public function messages(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            redirect('admin/messages', array_filter([
                'status' => in_array(post('status'), ['new', 'read'], true) ? post('status') : '',
            ]));
        }

        $page   = clamp_int(get('page', '1'), 1, 100000, 1);
        $per    = PAGE_SIZE_DEFAULT;
        $status = in_array(get('status'), ['new', 'read'], true) ? get('status') : '';

        $total = Contact::countAll($status);

        View::render('admin.messages', [
            'title'    => t('admin.messages'),
            'active'   => 'messages',
            'rows'     => Contact::paginate($per, ($page - 1) * $per, $status),
            'total'    => $total,
            'page'     => $page,
            'per'      => $per,
            'lastPage' => max(1, (int) ceil($total / $per)),
            'status'   => $status,
            'basePath' => 'admin/messages',
        ]);
    }

    public function markMessageRead(string $id): void
    {
        Contact::markRead((int) $id, (int) Auth::id());
        redirect('admin/messages');
    }

    public function deleteMessage(string $id): void
    {
        Contact::delete((int) $id);
        AuditLog::record('message.delete', 'message', $id);

        flash('success', t('admin.message_deleted'));
        redirect('admin/messages');
    }

    // --- Audit log -----------------------------------------------------------

    public function auditLog(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            redirect('admin/audit-log', array_filter([
                'action' => mb_substr(post('action'), 0, 64),
            ]));
        }

        $page   = clamp_int(get('page', '1'), 1, 100000, 1);
        $per    = 25;
        $action = mb_substr(get('action'), 0, 64);

        $total = AuditLog::countAll($action);

        View::render('admin.audit', [
            'title'    => t('admin.audit_log'),
            'active'   => 'audit',
            'rows'     => AuditLog::paginate($per, ($page - 1) * $per, $action),
            'actions'  => AuditLog::actions(),
            'action'   => $action,
            'total'    => $total,
            'page'     => $page,
            'per'      => $per,
            'lastPage' => max(1, (int) ceil($total / $per)),
            'basePath' => 'admin/audit-log',
        ]);
    }

    // --- Settings ------------------------------------------------------------

    /** Keys an administrator may edit. Anything else in the POST is ignored. */
    private const EDITABLE_SETTINGS = [
        'site_name', 'contact_email', 'contact_phone', 'contact_address', 'allow_registration',
    ];

    public function settings(): void
    {
        View::render('admin.settings', [
            'title'    => t('admin.settings'),
            'active'   => 'settings',
            'settings' => Setting::all(),
        ]);
    }

    public function updateSettings(): void
    {
        $values = [];
        foreach (self::EDITABLE_SETTINGS as $key) {
            $values[$key] = $key === 'allow_registration'
                ? (isset($_POST[$key]) ? '1' : '0')
                : post($key);
        }

        Setting::setMany($values, self::EDITABLE_SETTINGS);
        AuditLog::record('settings.update');

        flash('success', t('admin.settings_saved'));
        redirect('admin/settings');
    }
}
