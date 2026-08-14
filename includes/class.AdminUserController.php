<?php
/**
 * Administration of user accounts and plans.
 *
 * Two legacy behaviours are deliberately not reproduced:
 *   - "Reset password" used to set the password equal to the user's own id,
 *     and ids were sequential and guessable — an account-takeover primitive.
 *     A random temporary password is generated instead, shown once, and the
 *     account is forced to change it at next sign-in.
 *   - Changing the admin's own record used to rewrite the primary key, and a
 *     bug wrote an undefined variable into it, blanking the username and
 *     locking the administrator out permanently.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class AdminUserController
{
    // --- Listing -------------------------------------------------------------

    public function index(): void
    {
        // The filter form posts; redirect to the encoded GET so the URL carries
        // one ?q= token instead of readable key=value pairs.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            redirect('admin/users', array_filter([
                'search' => mb_substr(post('search'), 0, 100),
                'role'   => in_array(post('role'), Permission::roles(), true) ? post('role') : '',
            ]));
        }

        $page   = clamp_int(get('page', '1'), 1, 100000, 1);
        $per    = PAGE_SIZE_DEFAULT;
        $search = mb_substr(get('search'), 0, 100);
        $role   = in_array(get('role'), Permission::roles(), true) ? get('role') : '';

        $total = User::countAll($search, $role);

        View::render('admin.users', [
            'title'    => t('admin.users'),
            'active'   => 'users',
            'rows'     => User::paginate($per, ($page - 1) * $per, $search, $role),
            'total'    => $total,
            'page'     => $page,
            'per'      => $per,
            'lastPage' => max(1, (int) ceil($total / $per)),
            'search'   => $search,
            'role'     => $role,
            'basePath' => 'admin/users',
        ]);
    }

    public function show(string $id): void
    {
        $user = $this->mustFind((int) $id);

        View::render('admin.user-show', [
            'title'  => $user['name'],
            'active' => 'users',
            'user'   => $user,
            'usage'  => $user['role'] === 'customer' ? PlanLimit::usage($user) : null,
            'docs'   => Document::countForUser((int) $user['id']),
        ]);
    }

    /** A staff member browsing one customer's records, read-only. */
    public function properties(string $id): void
    {
        $user = $this->mustFind((int) $id);

        $page = clamp_int(get('page', '1'), 1, 100000, 1);
        $per  = PAGE_SIZE_DEFAULT;

        $result = Property::search((int) $user['id'], [], $per, ($page - 1) * $per, 'seq', 'ASC');

        View::render('admin.user-properties', [
            'title'    => $user['name'],
            'active'   => 'users',
            'user'     => $user,
            'rows'     => $result['rows'],
            'total'    => $result['total'],
            'page'     => $page,
            'per'      => $per,
            'lastPage' => max(1, (int) ceil($result['total'] / $per)),
            'basePath' => 'admin/users/' . $user['id'] . '/properties',
        ]);
    }

    // --- Create / edit -------------------------------------------------------

    public function create(): void
    {
        View::render('admin.user-form', [
            'title'  => t('admin.add_user'),
            'active' => 'users',
            'user'   => null,
            'plans'  => Plan::all(),
        ]);
    }

    public function edit(string $id): void
    {
        View::render('admin.user-form', [
            'title'  => t('action.edit'),
            'active' => 'users',
            'user'   => $this->mustFind((int) $id),
            'plans'  => Plan::all(),
        ]);
    }

    public function store(): void
    {
        $data     = $this->readForm();
        $password = (string) ($_POST['password'] ?? '');

        $errors = $this->validate($data, null);
        if (!valid_password($password)) {
            $errors[] = t('valid.password');
        }

        if ($errors) {
            flash_old($data);
            foreach ($errors as $error) {
                flash('danger', $error);
            }
            redirect('admin/users/create');
        }

        $id = User::create([
            'user_code' => User::nextUserCode(),
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'],
            'password'  => password_hash($password, PASSWORD_BCRYPT),
            'role'      => $data['role'],
            'plan_code' => $data['role'] === 'customer' ? ($data['plan_code'] ?: Plan::defaultCode()) : null,
            'status'    => $data['status'],
            'must_change_password' => 1,
        ]);

        AuditLog::record('user.create', 'user', (string) $id, ['email' => $data['email']]);

        flash('success', t('admin.user_created'));
        redirect('admin/users/' . $id);
    }

    public function update(string $id): void
    {
        $user = $this->mustFind((int) $id);
        $data = $this->readForm();

        // An administrator cannot demote themselves; otherwise the last admin
        // can lock everyone out of the admin area with a single form submit.
        if ((int) $user['id'] === Auth::id() && $data['role'] !== $user['role']) {
            flash('danger', t('admin.self_demote'));
            redirect('admin/users/' . $user['id'] . '/edit');
        }

        if ($errors = $this->validate($data, (int) $user['id'])) {
            flash_old($data);
            foreach ($errors as $error) {
                flash('danger', $error);
            }
            redirect('admin/users/' . $user['id'] . '/edit');
        }

        User::updateAdminFields((int) $user['id'], [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'],
            'role'      => $data['role'],
            'plan_code' => $data['role'] === 'customer' ? $data['plan_code'] : null,
            'status'    => $data['status'],
            'property_limit_override' => $data['property_limit_override'],
        ]);

        AuditLog::record('user.update', 'user', (string) $user['id']);

        flash('success', t('admin.user_updated'));
        redirect('admin/users/' . $user['id']);
    }

    /**
     * Issue a random temporary password, shown once to the administrator.
     * Never derived from anything guessable about the account.
     */
    public function resetPassword(string $id): void
    {
        $user = $this->mustFind((int) $id);

        // Ambiguous characters left out so it can be read aloud reliably.
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $temp     = '';
        for ($i = 0; $i < 12; $i++) {
            $temp .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        User::forcePasswordChange((int) $user['id'], password_hash($temp, PASSWORD_BCRYPT));
        AuditLog::record('user.password_reset', 'user', (string) $user['id']);

        flash('warning', t('admin.password_reset', ['password' => $temp]));
        redirect('admin/users/' . $user['id']);
    }

    public function destroy(string $id): void
    {
        $user = $this->mustFind((int) $id);

        if ((int) $user['id'] === Auth::id()) {
            flash('danger', t('admin.self_delete'));
            redirect('admin/users/' . $user['id']);
        }

        $userId = (int) $user['id'];

        Database::transaction(static function () use ($userId) {
            Document::deleteAllForUser($userId);
            Property::deleteAllForUser($userId);
            User::softDelete($userId);
        });

        AuditLog::record('user.delete', 'user', (string) $userId, ['email' => $user['email']]);

        flash('success', t('admin.user_deleted'));
        redirect('admin/users');
    }

    // --- Plans ---------------------------------------------------------------

    public function plans(): void
    {
        View::render('admin.plans', [
            'title'  => t('admin.plans'),
            'active' => 'plans',
            'plans'  => Plan::all(),
        ]);
    }

    public function updatePlan(string $code): void
    {
        if (Plan::find($code) === null) {
            throw new HttpException(404);
        }

        Plan::update($code, [
            'name'                 => mb_substr(post('name'), 0, 40),
            'property_limit'       => post('property_limit'),
            'can_upload_documents' => isset($_POST['can_upload_documents']),
            'can_export'           => isset($_POST['can_export']),
            'sort_order'           => post('sort_order', '0'),
        ]);

        if (post('is_default') !== '') {
            Plan::setDefault($code);
        }

        AuditLog::record('plan.update', 'plan', $code);

        flash('success', t('admin.plan_saved'));
        redirect('admin/plans');
    }

    // --- Helpers -------------------------------------------------------------

    private function mustFind(int $id): array
    {
        $user = User::find($id);
        if ($user === null) {
            throw new HttpException(404);
        }
        return $user;
    }

    private function readForm(): array
    {
        $phoneRaw = post('phone');

        return [
            'name'      => mb_substr(post('name'), 0, 60),
            'email'     => mb_substr(post('email'), 0, 190),
            'phone_raw' => $phoneRaw,
            'phone'     => $phoneRaw === '' ? null : normalize_phone($phoneRaw),
            'role'      => in_array(post('role'), Permission::roles(), true) ? post('role') : 'customer',
            'plan_code' => post('plan_code'),
            'status'    => in_array(post('status'), ['pending', 'active', 'suspended'], true)
                ? post('status')
                : 'active',
            'property_limit_override' => post('property_limit_override'),
        ];
    }

    private function validate(array $data, ?int $exceptId): array
    {
        $errors = [];

        if (!valid_name($data['name'])) {
            $errors[] = t('valid.name');
        }
        if (!valid_email($data['email'])) {
            $errors[] = t('valid.email');
        } elseif (User::emailTaken($data['email'], $exceptId)) {
            $errors[] = t('valid.email_taken');
        }
        if ($data['phone_raw'] !== '' && $data['phone'] === null) {
            $errors[] = t('valid.phone');
        }
        if ($data['property_limit_override'] !== '' && !ctype_digit((string) $data['property_limit_override'])) {
            $errors[] = t('valid.number') . ' (' . t('admin.limit_override') . ')';
        }

        return $errors;
    }
}
