<?php
/**
 * Role → permission map.
 *
 * Roles live in the database (users.role); the map from a role to the actions
 * it may perform is a constant here. With three roles and one administrator,
 * permissions/role_permissions/user_permissions tables would be three joins and
 * an admin UI to maintain something that never varies at runtime.
 *
 * Call sites are identical to a database-backed version — Auth::can('user.delete')
 * — so this can be promoted later without touching a single controller.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Permission
{
    /**
     * A trailing '.*' grants everything in that group; a bare '*' grants
     * everything. Permissions are named <entity>.<action>.
     */
    public const MATRIX = [
        'customer' => [
            'property.*',   // create, view, edit, delete — own records only
            'document.*',   // subject to the plan's can_upload_documents
            'profile.*',
        ],
        'staff' => [
            'user.view',
            'property.view',
            'message.view',
            'message.update',
            'profile.*',
        ],
        'admin' => [
            '*',
        ],
    ];

    /** Does this role grant this permission? */
    public static function allows(?string $role, string $permission): bool
    {
        if ($role === null || !isset(self::MATRIX[$role])) {
            return false;
        }

        foreach (self::MATRIX[$role] as $granted) {
            if ($granted === '*' || $granted === $permission) {
                return true;
            }
            // 'property.*' covers 'property.create'
            if (substr($granted, -2) === '.*'
                && strncmp($permission, substr($granted, 0, -1), strlen($granted) - 1) === 0) {
                return true;
            }
        }
        return false;
    }

    /** All role slugs, for admin dropdowns. */
    public static function roles(): array
    {
        return array_keys(self::MATRIX);
    }
}
