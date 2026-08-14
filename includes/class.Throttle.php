<?php
/**
 * Login throttling. The legacy app had none at all: an attacker could try
 * passwords indefinitely against sequential, guessable user ids.
 *
 * Counted per identifier (the e-mail) first. Per-IP limits are also recorded,
 * but on XAMPP every request arrives from 127.0.0.1, so an IP-only design would
 * be untestable locally and would lock out every user at once in shared-NAT
 * situations.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Throttle
{
    /**
     * Seconds remaining before this identifier may try again, or 0 when not
     * locked out.
     */
    public static function lockedFor(string $identifier): int
    {
        $row = Database::one(
            'SELECT COUNT(*) AS failures, MAX(created_at) AS last_at
               FROM login_attempts
              WHERE identifier = ?
                AND successful = 0
                AND created_at > (NOW() - INTERVAL ? MINUTE)',
            [$identifier, THROTTLE_DECAY_MINUTES]
        );

        if (!$row || (int) $row['failures'] < THROTTLE_MAX_ATTEMPTS) {
            return 0;
        }

        $unlockAt = strtotime($row['last_at']) + (THROTTLE_DECAY_MINUTES * 60);
        return max(0, $unlockAt - time());
    }

    /** Record one attempt. */
    public static function record(string $identifier, bool $successful): void
    {
        Database::run(
            'INSERT INTO login_attempts (identifier, ip, successful) VALUES (?, ?, ?)',
            [$identifier, client_ip(), $successful ? 1 : 0]
        );

        // Opportunistic cleanup so the table cannot grow without bound. Cheap
        // because it is indexed on created_at and only fires occasionally.
        if (random_int(1, 50) === 1) {
            Database::run('DELETE FROM login_attempts WHERE created_at < (NOW() - INTERVAL 30 DAY)');
        }
    }

    /** Drop the failure history for an identifier after a successful sign-in. */
    public static function clear(string $identifier): void
    {
        Database::run(
            'DELETE FROM login_attempts WHERE identifier = ? AND successful = 0',
            [$identifier]
        );
    }
}
