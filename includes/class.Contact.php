<?php
/**
 * Contact messages from the public form.
 *
 * This is an unauthenticated endpoint whose output is read by administrators,
 * so every value is bound on the way in and escaped on the way out.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Contact
{
    public static function create(string $name, string $email, string $message): int
    {
        Database::run(
            'INSERT INTO contact_messages (name, email, message, ip, user_agent)
             VALUES (?, ?, ?, ?, ?)',
            [
                $name,
                strtolower(trim($email)),
                $message,
                client_ip(),
                mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            ]
        );

        return (int) Database::lastId();
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM contact_messages WHERE id = ?', [$id]);
    }

    public static function paginate(int $limit, int $offset, string $status = ''): array
    {
        $where  = [];
        $params = [];

        if ($status !== '') {
            $where[]  = 'status = ?';
            $params[] = $status;
        }

        $sql = 'SELECT * FROM contact_messages'
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
             . ' ORDER BY created_at DESC'
             . ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

        return Database::all($sql, $params);
    }

    public static function countAll(string $status = ''): int
    {
        if ($status !== '') {
            return (int) Database::scalar(
                'SELECT COUNT(*) FROM contact_messages WHERE status = ?',
                [$status]
            );
        }
        return (int) Database::scalar('SELECT COUNT(*) FROM contact_messages');
    }

    public static function markRead(int $id, int $readerId): void
    {
        Database::run(
            "UPDATE contact_messages
                SET status = 'read', read_at = NOW(), read_by = ?
              WHERE id = ? AND status = 'new'",
            [$readerId, $id]
        );
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM contact_messages WHERE id = ?', [$id]);
    }
}
