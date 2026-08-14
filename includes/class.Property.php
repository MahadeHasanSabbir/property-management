<?php
/**
 * Property records — one row per land-deed entry.
 *
 * The four dag/khatian columns store the raw string exactly as typed, commas
 * and all. That string is canonical; property_identifiers is a derived index
 * rebuilt from it by reindex(), so a bug in the splitter can never lose data.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Property
{
    /** The four (column, kind, scope) triples that feed the identifier index. */
    private const IDENTIFIER_MAP = [
        ['dag_current',      'dag',     'current'],
        ['dag_previous',     'dag',     'previous'],
        ['khatian_current',  'khatian', 'current'],
        ['khatian_previous', 'khatian', 'previous'],
    ];

    /** Columns a search may sort by. Anything else is rejected, never passed through. */
    public const SORTABLE = ['seq', 'deed_no', 'deed_date', 'area_cent', 'mouja', 'created_at'];

    // --- Reads ---------------------------------------------------------------

    public static function find(int $id): ?array
    {
        return Database::one(
            'SELECT * FROM properties WHERE id = ? AND deleted_at IS NULL',
            [$id]
        );
    }

    /**
     * Find a record that belongs to a specific user.
     *
     * Ownership is enforced in the query itself rather than by a later check,
     * so there is no code path that fetches somebody else's row first.
     */
    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::one(
            'SELECT * FROM properties WHERE id = ? AND user_id = ? AND deleted_at IS NULL',
            [$id, $userId]
        );
    }

    public static function countForUser(int $userId): int
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM properties WHERE user_id = ? AND deleted_at IS NULL',
            [$userId]
        );
    }

    public static function recent(int $userId, int $limit = 5): array
    {
        return Database::all(
            'SELECT * FROM properties
              WHERE user_id = ? AND deleted_at IS NULL
              ORDER BY created_at DESC, id DESC
              LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    /** Distinct moujas this user has used, for the search dropdown. */
    public static function moujasForUser(int $userId): array
    {
        return array_column(
            Database::all(
                "SELECT DISTINCT mouja FROM properties
                  WHERE user_id = ? AND deleted_at IS NULL AND mouja <> ''
                  ORDER BY mouja",
                [$userId]
            ),
            'mouja'
        );
    }

    // --- Writes --------------------------------------------------------------

    /**
     * Create a record and its identifier rows, inside one transaction, after
     * checking the plan limit under a row lock.
     */
    public static function create(array $user, array $data): int
    {
        return Database::transaction(static function () use ($user, $data) {
            PlanLimit::assertCanCreate($user);

            $userId = (int) $user['id'];
            $seq    = self::nextSeq($userId);

            Database::run(
                'INSERT INTO properties
                    (user_id, seq, deed_no, deed_date, area_cent, old_owner, new_owner,
                     mouja, dag_current, dag_previous, khatian_current, khatian_previous, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $userId,
                    $seq,
                    $data['deed_no'],
                    $data['deed_date'] ?: null,
                    $data['area_cent'] === '' ? null : $data['area_cent'],
                    $data['old_owner'],
                    $data['new_owner'],
                    $data['mouja'],
                    $data['dag_current'],
                    $data['dag_previous'],
                    $data['khatian_current'],
                    $data['khatian_previous'],
                    $data['notes'] ?: null,
                ]
            );

            $id = (int) Database::lastId();
            self::reindex($id, $userId, $data);
            return $id;
        });
    }

    public static function update(int $id, int $userId, array $data): void
    {
        Database::transaction(static function () use ($id, $userId, $data) {
            Database::run(
                'UPDATE properties
                    SET deed_no = ?, deed_date = ?, area_cent = ?, old_owner = ?,
                        new_owner = ?, mouja = ?, dag_current = ?, dag_previous = ?,
                        khatian_current = ?, khatian_previous = ?, notes = ?
                  WHERE id = ? AND user_id = ?',
                [
                    $data['deed_no'],
                    $data['deed_date'] ?: null,
                    $data['area_cent'] === '' ? null : $data['area_cent'],
                    $data['old_owner'],
                    $data['new_owner'],
                    $data['mouja'],
                    $data['dag_current'],
                    $data['dag_previous'],
                    $data['khatian_current'],
                    $data['khatian_previous'],
                    $data['notes'] ?: null,
                    $id,
                    $userId,
                ]
            );

            self::reindex($id, $userId, $data);
        });
    }

    /** Soft delete, so an audit entry still has something to point at. */
    public static function delete(int $id, int $userId): void
    {
        Database::run(
            'UPDATE properties SET deleted_at = NOW() WHERE id = ? AND user_id = ? AND deleted_at IS NULL',
            [$id, $userId]
        );
    }

    /** Hard delete every record for a user (account deletion). */
    public static function deleteAllForUser(int $userId): void
    {
        // property_identifiers and property_documents cascade.
        Database::run('DELETE FROM properties WHERE user_id = ?', [$userId]);
    }

    /**
     * Next per-user display number, computed from the data rather than held in
     * a counter that could drift away from it.
     */
    public static function nextSeq(int $userId): int
    {
        return ((int) Database::scalar(
            'SELECT COALESCE(MAX(seq), 0) FROM properties WHERE user_id = ?',
            [$userId]
        )) + 1;
    }

    // --- Identifier index ----------------------------------------------------

    /**
     * Rebuild the identifier rows for one record from its raw strings.
     * Called on every write, and in bulk by database/reindex.php.
     */
    public static function reindex(int $propertyId, int $userId, array $data): void
    {
        Database::run('DELETE FROM property_identifiers WHERE property_id = ?', [$propertyId]);

        $rows = [];
        foreach (self::IDENTIFIER_MAP as [$column, $kind, $scope]) {
            foreach (split_tokens((string) ($data[$column] ?? '')) as $token) {
                // Values longer than the column are a data error, not a reason
                // to fail the whole save — the raw string is kept regardless.
                $rows[] = [$propertyId, $userId, $kind, $scope, mb_substr($token, 0, 30)];
            }
        }

        if (!$rows) {
            return;
        }

        // The composite primary key already de-duplicates "12,12,12";
        // INSERT IGNORE keeps that from being an error.
        $sql = 'INSERT IGNORE INTO property_identifiers
                    (property_id, user_id, kind, scope, value) VALUES '
             . implode(', ', array_fill(0, count($rows), '(?, ?, ?, ?, ?)'));

        Database::run($sql, array_merge(...$rows));
    }

    // --- Search --------------------------------------------------------------

    /**
     * Search a user's records.
     *
     * How it behaves:
     *
     *  - Dag and khatian match EXACT tokens via property_identifiers, so
     *    searching 12 does not also match '512,34' or '3412'.
     *  - Scope defaults to "current or previous"; narrowing is optional.
     *  - Filters combine with AND by default, so each one narrows the result.
     *  - EXISTS rather than JOIN: a record with three dag tokens would
     *    otherwise be returned three times.
     *  - Sorting is whitelisted and LIMIT/OFFSET are bound integers, so neither
     *    can carry SQL.
     *
     * @param array $f filters, already sanitised by the controller
     * @return array{rows:array, total:int}
     */
    public static function search(int $userId, array $f, int $limit, int $offset, string $sort, string $dir): array
    {
        $clauses = [];
        $params  = [];

        // Dag / khatian: exact token match, optionally scoped.
        foreach ([['dag', 'dag_scope'], ['khatian', 'khatian_scope']] as [$kind, $scopeKey]) {
            $value = trim((string) ($f[$kind] ?? ''));
            if ($value === '') {
                continue;
            }

            $sub      = 'SELECT 1 FROM property_identifiers i
                          WHERE i.property_id = p.id AND i.kind = ? AND i.value = ?';
            $subParam = [$kind, $value];

            $scope = $f[$scopeKey] ?? 'any';
            if ($scope === 'current' || $scope === 'previous') {
                $sub       .= ' AND i.scope = ?';
                $subParam[] = $scope;
            }

            $clauses[] = 'EXISTS (' . $sub . ')';
            $params    = array_merge($params, $subParam);
        }

        if (($f['deed_no'] ?? '') !== '') {
            $clauses[] = 'p.deed_no = ?';
            $params[]  = $f['deed_no'];
        }

        if (($f['mouja'] ?? '') !== '') {
            $clauses[] = 'p.mouja = ?';
            $params[]  = $f['mouja'];
        }

        // Owner: prefix match by default so the index is usable; "contains" is
        // offered explicitly and documented as the slow path.
        if (($f['owner'] ?? '') !== '') {
            $pattern = ($f['owner_mode'] ?? 'starts') === 'contains'
                ? '%' . self::escapeLike($f['owner']) . '%'
                : self::escapeLike($f['owner']) . '%';

            $clauses[] = "(p.old_owner LIKE ? ESCAPE '\\\\' OR p.new_owner LIKE ? ESCAPE '\\\\')";
            $params[]  = $pattern;
            $params[]  = $pattern;
        }

        if (($f['area_min'] ?? '') !== '') {
            $clauses[] = 'p.area_cent >= ?';
            $params[]  = $f['area_min'];
        }
        if (($f['area_max'] ?? '') !== '') {
            $clauses[] = 'p.area_cent <= ?';
            $params[]  = $f['area_max'];
        }

        if (($f['date_from'] ?? '') !== '') {
            $clauses[] = 'p.deed_date >= ?';
            $params[]  = $f['date_from'];
        }
        if (($f['date_to'] ?? '') !== '') {
            $clauses[] = 'p.deed_date <= ?';
            $params[]  = $f['date_to'];
        }

        // The ownership guard is ALWAYS AND-ed, never folded into the OR group
        // — otherwise "match any" would return other users' records.
        $where  = 'p.user_id = ? AND p.deleted_at IS NULL';
        $bind   = [$userId];

        if ($clauses) {
            $glue   = (($f['mode'] ?? 'all') === 'any') ? ' OR ' : ' AND ';
            $where .= ' AND (' . implode($glue, $clauses) . ')';
            $bind   = array_merge($bind, $params);
        }

        $total = (int) Database::scalar(
            'SELECT COUNT(*) FROM properties p WHERE ' . $where,
            $bind
        );

        // Whitelisted, so neither value can carry SQL.
        $sort = in_array($sort, self::SORTABLE, true) ? $sort : 'seq';
        $dir  = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

        $rows = Database::all(
            'SELECT p.* FROM properties p
              WHERE ' . $where . '
              ORDER BY p.' . $sort . ' ' . $dir . ', p.id ' . $dir . '
              LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset,
            $bind
        );

        return ['rows' => $rows, 'total' => $total];
    }

    /** Escape LIKE wildcards so a literal % or _ in a name is not a wildcard. */
    private static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    /** Every matching row, unpaginated — for CSV export. */
    public static function searchAll(int $userId, array $f, string $sort, string $dir): array
    {
        // A generous ceiling rather than none: an unbounded export would happily
        // exhaust memory on a large account.
        $result = self::search($userId, $f, 10000, 0, $sort, $dir);
        return $result['rows'];
    }
}
