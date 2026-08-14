<?php
/**
 * Plan quota enforcement.
 *
 * Two rules matter here, and both are reactions to how the legacy code behaved:
 *
 *  1. The count is always a live COUNT(*), never a stored counter. The legacy
 *     `user.property` column was a max-UID pointer rather than a count (it held
 *     '019' for 19 rows), drifted out of step, and was "repaired" from inside
 *     page renders. Two different totals were displayed side by side on the old
 *     profile page as a result.
 *
 *  2. Being over the limit makes an account read-only, never broken. An
 *     administrator can lower a limit below a user's current usage, so create
 *     is blocked while view, edit and delete stay available — otherwise the
 *     account could never be brought back under its limit.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class PlanLimit
{
    /**
     * Current usage for a user.
     *
     * @return array{used:int, limit:?int, remaining:?int, over:bool,
     *               plan:?array, can_upload:bool, can_export:bool, percent:int}
     */
    public static function usage(array $user): array
    {
        $plan  = Plan::find($user['plan_code'] ?? null);
        $used  = Property::countForUser((int) $user['id']);

        // A per-user override beats the plan's limit, so one account can be
        // granted more room without inventing a new plan.
        $limit = $user['property_limit_override'] !== null
            ? (int) $user['property_limit_override']
            : ($plan && $plan['property_limit'] !== null ? (int) $plan['property_limit'] : null);

        $remaining = $limit === null ? null : max(0, $limit - $used);
        $percent   = ($limit === null || $limit === 0)
            ? 0
            : (int) min(100, round(($used / $limit) * 100));

        return [
            'used'       => $used,
            'limit'      => $limit,
            'remaining'  => $remaining,
            'over'       => $limit !== null && $used > $limit,
            'plan'       => $plan,
            'can_upload' => (bool) ($plan['can_upload_documents'] ?? 0),
            'can_export' => (bool) ($plan['can_export'] ?? 0),
            'percent'    => $percent,
        ];
    }

    /**
     * Assert that this user may add one more record, and return the seq to use.
     *
     * Runs inside the caller's transaction and locks the user row first, so two
     * simultaneous submissions cannot both pass the check and exceed the limit.
     * The legacy code read a counter, added one and wrote it back with no lock
     * at all.
     */
    public static function assertCanCreate(array $user): void
    {
        $id = (int) $user['id'];

        // Lock the parent row for the duration of the transaction.
        Database::run('SELECT id FROM users WHERE id = ? FOR UPDATE', [$id]);

        $plan  = Plan::find($user['plan_code'] ?? null);
        $limit = $user['property_limit_override'] !== null
            ? (int) $user['property_limit_override']
            : ($plan && $plan['property_limit'] !== null ? (int) $plan['property_limit'] : null);

        if ($limit === null) {
            return; // unlimited
        }

        $used = (int) Database::scalar(
            'SELECT COUNT(*) FROM properties WHERE user_id = ? AND deleted_at IS NULL',
            [$id]
        );

        if ($used >= $limit) {
            throw new PlanLimitException(t('plan.upgrade_prompt', [
                'limit' => $limit,
                'plan'  => $plan['name'] ?? '—',
            ]));
        }
    }

    /** Assert the plan includes document uploads. */
    public static function assertCanUpload(array $user): void
    {
        $plan = Plan::find($user['plan_code'] ?? null);
        if (empty($plan['can_upload_documents'])) {
            throw new PlanLimitException(t('plan.no_documents'));
        }
    }

    /** Assert the plan includes CSV export. */
    public static function assertCanExport(array $user): void
    {
        $plan = Plan::find($user['plan_code'] ?? null);
        if (empty($plan['can_export'])) {
            throw new PlanLimitException(t('plan.no_export'));
        }
    }
}
