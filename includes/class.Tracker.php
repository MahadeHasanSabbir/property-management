<?php
/**
 * Page-view logging — replaces the legacy `visitors` table.
 *
 * Kept for auditing, but surfaced only as a simple 30-day count. The old admin
 * dashboard built four tiles on this data and every one of them was wrong:
 * the table's PRIMARY KEY was a 1-second TIMESTAMP, so concurrent visits
 * collided and the failed inserts were never checked.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Tracker
{
    /**
     * Record a view. Never allowed to break a page render — analytics is not
     * worth a 500, so a failure here is logged and swallowed.
     */
    public static function record(string $path): void
    {
        try {
            Database::run(
                'INSERT INTO page_views (ip, path, user_id, user_agent) VALUES (?, ?, ?, ?)',
                [
                    client_ip(),
                    mb_substr($path, 0, 190),
                    Auth::id(),
                    mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                ]
            );

            // Opportunistic retention trim.
            if (random_int(1, 200) === 1) {
                Database::run('DELETE FROM page_views WHERE created_at < (NOW() - INTERVAL 1 YEAR)');
            }
        } catch (\Throwable $e) {
            error_log('Tracker: ' . $e->getMessage());
        }
    }

    /** Total views in the last N days. */
    public static function viewsSince(int $days = 30): int
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM page_views WHERE created_at > (NOW() - INTERVAL ? DAY)',
            [$days]
        );
    }

    /** Distinct client addresses in the last N days. */
    public static function visitorsSince(int $days = 30): int
    {
        return (int) Database::scalar(
            'SELECT COUNT(DISTINCT ip) FROM page_views
              WHERE ip IS NOT NULL AND created_at > (NOW() - INTERVAL ? DAY)',
            [$days]
        );
    }
}
