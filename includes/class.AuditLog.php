<?php
/**
 * Audit trail — who did what, and when.
 *
 * Land records are provenance-sensitive: knowing which account edited or
 * deleted a deed entry matters. The legacy app recorded nothing beyond a
 * last-login date and a raw visitor IP list.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class AuditLog
{
    /**
     * Record an action. Never allowed to break the operation it is recording —
     * a failure to write the log is logged to disk and swallowed.
     *
     * @param string $action e.g. 'property.delete'
     * @param array  $meta   small context payload, stored as JSON
     */
    public static function record(string $action, ?string $entity = null, ?string $entityId = null, array $meta = []): void
    {
        try {
            $user = Auth::user();

            Database::run(
                'INSERT INTO audit_logs
                    (user_id, actor_label, action, entity, entity_id, meta, ip)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $user['id'] ?? null,
                    // Snapshot the actor so the entry stays readable after the
                    // account is deleted and user_id becomes NULL.
                    $user ? ($user['name'] . ' <' . $user['email'] . '>') : null,
                    $action,
                    $entity,
                    $entityId,
                    $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
                    client_ip(),
                ]
            );
        } catch (\Throwable $e) {
            error_log('AuditLog: ' . $e->getMessage());
        }
    }

    public static function paginate(int $limit, int $offset, string $action = ''): array
    {
        $where  = [];
        $params = [];

        if ($action !== '') {
            $where[]  = 'action = ?';
            $params[] = $action;
        }

        $sql = 'SELECT * FROM audit_logs'
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
             . ' ORDER BY created_at DESC, id DESC'
             . ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        return Database::all($sql, $params);
    }

    public static function countAll(string $action = ''): int
    {
        if ($action !== '') {
            return (int) Database::scalar('SELECT COUNT(*) FROM audit_logs WHERE action = ?', [$action]);
        }
        return (int) Database::scalar('SELECT COUNT(*) FROM audit_logs');
    }

    /** Distinct action names, for the filter dropdown. */
    public static function actions(): array
    {
        return array_column(
            Database::all('SELECT DISTINCT action FROM audit_logs ORDER BY action'),
            'action'
        );
    }
}
