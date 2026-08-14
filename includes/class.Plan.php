<?php
/**
 * Subscription plans. Entitlements are typed columns on the `plans` table and
 * are editable at /admin/plans, so changing a limit never requires a code
 * change — which is the whole point of not writing the numbers into constants.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Plan
{
    public static function all(): array
    {
        return Database::all('SELECT * FROM plans ORDER BY sort_order, code');
    }

    public static function find(?string $code): ?array
    {
        if ($code === null || $code === '') {
            return null;
        }
        return Database::one('SELECT * FROM plans WHERE code = ?', [$code]);
    }

    /** The plan new registrations get. */
    public static function defaultCode(): ?string
    {
        $code = Database::scalar('SELECT code FROM plans WHERE is_default = 1 ORDER BY sort_order LIMIT 1');
        if ($code) {
            return (string) $code;
        }
        // No plan flagged default — fall back to the cheapest rather than
        // leaving the user with no plan at all.
        $code = Database::scalar('SELECT code FROM plans ORDER BY sort_order LIMIT 1');
        return $code ? (string) $code : null;
    }

    public static function update(string $code, array $data): void
    {
        Database::run(
            'UPDATE plans
                SET name = ?, property_limit = ?, can_upload_documents = ?,
                    can_export = ?, sort_order = ?
              WHERE code = ?',
            [
                $data['name'],
                // Blank means unlimited, stored as NULL rather than 0 — 0 would
                // mean "no records allowed at all".
                ($data['property_limit'] === '' || $data['property_limit'] === null)
                    ? null
                    : max(0, (int) $data['property_limit']),
                !empty($data['can_upload_documents']) ? 1 : 0,
                !empty($data['can_export']) ? 1 : 0,
                (int) ($data['sort_order'] ?? 0),
                $code,
            ]
        );
    }

    /** Make exactly one plan the default for new sign-ups. */
    public static function setDefault(string $code): void
    {
        Database::transaction(static function () use ($code) {
            Database::run('UPDATE plans SET is_default = 0');
            Database::run('UPDATE plans SET is_default = 1 WHERE code = ?', [$code]);
        });
    }
}
