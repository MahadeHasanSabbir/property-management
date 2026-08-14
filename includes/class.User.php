<?php
/**
 * Users — reads and writes for the `users` table.
 *
 * Every query is a named method with hand-written SQL and bound parameters.
 * There is no base model or query builder: with six entities and a few dozen
 * queries, an abstraction layer would cost more than it saves and tends to grow
 * into a poor ORM.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class User
{
    /** Columns safe to select everywhere (includes the hash — internal use). */
    private const COLUMNS = 'id, user_code, name, email, phone, password, role,
        plan_code, property_limit_override, status, locale, must_change_password,
        email_verified_at, password_changed_at, last_login_at, last_login_ip,
        created_at, updated_at, deleted_at';

    public static function find(int $id): ?array
    {
        return Database::one(
            'SELECT ' . self::COLUMNS . ' FROM users WHERE id = ? AND deleted_at IS NULL',
            [$id]
        );
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::one(
            'SELECT ' . self::COLUMNS . ' FROM users WHERE email = ? AND deleted_at IS NULL',
            [strtolower(trim($email))]
        );
    }

    public static function findByCode(string $code): ?array
    {
        return Database::one(
            'SELECT ' . self::COLUMNS . ' FROM users WHERE user_code = ? AND deleted_at IS NULL',
            [$code]
        );
    }

    /** Is this e-mail already taken by someone other than $exceptId? */
    public static function emailTaken(string $email, ?int $exceptId = null): bool
    {
        $sql    = 'SELECT COUNT(*) FROM users WHERE email = ? AND deleted_at IS NULL';
        $params = [strtolower(trim($email))];

        if ($exceptId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }

        return (int) Database::scalar($sql, $params) > 0;
    }

    /**
     * Create a user and return the new id.
     * The caller hashes the password; this method never sees plaintext.
     */
    public static function create(array $data): int
    {
        Database::run(
            'INSERT INTO users
                (user_code, name, email, phone, password, role, plan_code,
                 status, locale, must_change_password, password_changed_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                $data['user_code'] ?? null,
                $data['name'],
                strtolower(trim($data['email'])),
                $data['phone'] ?? null,
                $data['password'],
                $data['role'] ?? 'customer',
                $data['plan_code'] ?? null,
                $data['status'] ?? 'active',
                $data['locale'] ?? DEFAULT_LANG,
                !empty($data['must_change_password']) ? 1 : 0,
            ]
        );

        return (int) Database::lastId();
    }

    /** Update the profile fields a user may change about themselves. */
    public static function updateProfile(int $id, array $data): void
    {
        Database::run(
            'UPDATE users SET name = ?, email = ?, phone = ?, locale = ? WHERE id = ?',
            [
                $data['name'],
                strtolower(trim($data['email'])),
                $data['phone'] ?? null,
                $data['locale'] ?? DEFAULT_LANG,
                $id,
            ]
        );
    }

    /** Update the fields only an administrator may change. */
    public static function updateAdminFields(int $id, array $data): void
    {
        Database::run(
            'UPDATE users
                SET name = ?, email = ?, phone = ?, role = ?, plan_code = ?,
                    status = ?, property_limit_override = ?
              WHERE id = ?',
            [
                $data['name'],
                strtolower(trim($data['email'])),
                $data['phone'] ?? null,
                $data['role'],
                $data['plan_code'] ?: null,
                $data['status'],
                ($data['property_limit_override'] === '' || $data['property_limit_override'] === null)
                    ? null
                    : (int) $data['property_limit_override'],
                $id,
            ]
        );
    }

    public static function updatePassword(int $id, string $hash, bool $clearMustChange = true): void
    {
        Database::run(
            'UPDATE users
                SET password = ?, password_changed_at = NOW(), must_change_password = ?
              WHERE id = ?',
            [$hash, $clearMustChange ? 0 : 1, $id]
        );
    }

    /** Force a password change on next sign-in (used by an admin reset). */
    public static function forcePasswordChange(int $id, string $hash): void
    {
        Database::run(
            'UPDATE users
                SET password = ?, password_changed_at = NOW(), must_change_password = 1
              WHERE id = ?',
            [$hash, $id]
        );
    }

    public static function touchLogin(int $id, ?string $ip): void
    {
        Database::run(
            'UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?',
            [$ip, $id]
        );
    }

    /**
     * Soft-delete. Property records are removed by the caller inside the same
     * transaction; the row is retained so audit_logs entries keep a target.
     */
    public static function softDelete(int $id): void
    {
        Database::run(
            "UPDATE users
                SET deleted_at = NOW(),
                    status     = 'suspended',
                    email      = CONCAT('deleted+', id, '@invalid'),
                    user_code  = NULL
              WHERE id = ?",
            [$id]
        );
    }

    /**
     * A page of users with their live record counts.
     *
     * The count is computed here, never read from a stored counter: the legacy
     * `user.property` column was a max-UID pointer rather than a count (it held
     * '019' for 19 rows) and drifted out of step, which is why two different
     * "total records" figures appeared side by side in the old profile page.
     */
    public static function paginate(int $limit, int $offset, string $search = '', string $role = ''): array
    {
        $where  = ['u.deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[]  = '(u.name LIKE ? OR u.email LIKE ? OR u.user_code LIKE ?)';
            $like     = $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($role !== '') {
            $where[]  = 'u.role = ?';
            $params[] = $role;
        }

        $sql = 'SELECT u.*, p.name AS plan_name, p.property_limit,
                       (SELECT COUNT(*) FROM properties pr
                         WHERE pr.user_id = u.id AND pr.deleted_at IS NULL) AS record_count
                  FROM users u
             LEFT JOIN plans p ON p.code = u.plan_code
                 WHERE ' . implode(' AND ', $where) . '
              ORDER BY u.created_at DESC
                 LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        return Database::all($sql, $params);
    }

    public static function countAll(string $search = '', string $role = ''): int
    {
        $where  = ['deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[]  = '(name LIKE ? OR email LIKE ? OR user_code LIKE ?)';
            $like     = $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($role !== '') {
            $where[]  = 'role = ?';
            $params[] = $role;
        }

        return (int) Database::scalar(
            'SELECT COUNT(*) FROM users WHERE ' . implode(' AND ', $where),
            $params
        );
    }

    /**
     * Generate the next display code, e.g. 2608140001.
     * Purely cosmetic — it is not a credential and not a primary key, so unlike
     * the legacy scheme it cannot collide into a broken account. The legacy
     * generator read a VARCHAR(3) counter, added one and wrote it back with no
     * lock, and hard-capped at 999 users.
     */
    public static function nextUserCode(): string
    {
        $prefix = date('ymd');
        $max    = Database::scalar(
            'SELECT MAX(user_code) FROM users WHERE user_code LIKE ?',
            [$prefix . '%']
        );

        $next = $max ? ((int) substr((string) $max, 6) + 1) : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
