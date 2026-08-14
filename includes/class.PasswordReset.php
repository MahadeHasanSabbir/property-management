<?php
/**
 * Password reset tokens.
 *
 * Only the SHA-256 of the token is stored, so reading the database does not
 * hand over working reset links.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class PasswordReset
{
    /** Issue a fresh token, invalidating any earlier ones for that user. */
    public static function issue(int $userId): string
    {
        $token = bin2hex(random_bytes(32));

        Database::transaction(static function () use ($userId, $token) {
            // One live token per user: an old link stops working the moment a
            // new one is requested.
            Database::run('DELETE FROM password_resets WHERE user_id = ?', [$userId]);

            Database::run(
                'INSERT INTO password_resets (user_id, token_hash, expires_at)
                 VALUES (?, ?, (NOW() + INTERVAL ? MINUTE))',
                [$userId, hash('sha256', $token), RESET_TOKEN_TTL_MINUTES]
            );
        });

        return $token;
    }

    /** Look up a usable token, or null when missing, used or expired. */
    public static function resolve(string $token): ?array
    {
        if ($token === '' || !ctype_xdigit($token)) {
            return null;
        }

        return Database::one(
            'SELECT * FROM password_resets
              WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()',
            [hash('sha256', $token)]
        );
    }

    /** Mark a token used so the link cannot be replayed. */
    public static function consume(int $id): void
    {
        Database::run('UPDATE password_resets SET used_at = NOW() WHERE id = ?', [$id]);
    }
}
