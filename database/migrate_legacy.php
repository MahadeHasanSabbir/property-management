<?php
/**
 * One-time migration from the legacy `property` database into `property_v2`.
 *
 * Usage (from the project root):
 *     php database/migrate_legacy.php            # dry run — writes nothing
 *     php database/migrate_legacy.php --commit   # actually migrate
 *     php database/migrate_legacy.php --commit --quiet
 *
 * Design rules, each a reaction to how the legacy data actually behaves:
 *
 *   - Reads the legacy database, never writes to it. Nothing is dropped, so the
 *     old app stays runnable side by side for comparison.
 *   - Dry run by default. --commit is required to write anything.
 *   - One transaction PER USER, so a single bad account cannot abort the run.
 *   - Idempotent: users are matched on user_code and properties on
 *     (user_id, legacy_uid), so re-running is a no-op.
 *   - Anomalies are RECORDED and the run continues. The legacy codebase's habit
 *     was `or die("Failed to upload data!")`; a migration that dies halfway
 *     tells you nothing about the rows it never reached.
 *
 * Every anomaly lands in the `migration_report` table and in the printed
 * summary, with one of these codes:
 *
 *   MISSING_TABLE           user row exists but its user<ID> table does not
 *                           (legacy registration ran three statements with no
 *                           transaction)
 *   ORPHAN_TABLE            user<ID> table exists with no matching user row
 *                           (legacy deletion ran DELETE then DROP, unguarded)
 *   ZERO_DATE               rdate '0000-00-00' -> NULL
 *   NON_NUMERIC_SIZE        size was VARCHAR; unparseable values -> NULL
 *   EMPTY_TOKEN             empty segment in a comma list, e.g. "12,,34"
 *   TOKEN_NOT_NUMERIC       a dag/khatian token that is not a plain number
 *   DUP_EMAIL               e-mail already used (legacy had no UNIQUE index)
 *   PHONE_UNPARSEABLE       phone cannot be normalised to +880 form
 *   PASSWORD_IS_DEFAULT_ID  password equals the user's own id, which is what
 *                           the legacy admin "reset password" produced
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("This script must be run from the command line.\n");
}

require_once __DIR__ . '/../includes/bootstrap.php';

use App\Database;

$commit = in_array('--commit', $argv, true);
$quiet  = in_array('--quiet', $argv, true);

$report  = [];
$counts  = ['users' => 0, 'properties' => 0, 'identifiers' => 0, 'skipped' => 0];

/** Record an anomaly (and echo it unless quiet). */
$note = function (string $severity, string $code, string $message, ?string $table = null, ?string $uid = null)
        use (&$report, $quiet): void {
    $report[] = compact('severity', 'code', 'message', 'table', 'uid');
    if (!$quiet && $severity !== 'info') {
        printf("  [%-7s] %-22s %s\n", strtoupper($severity), $code, $message);
    }
};

$say = function (string $line = '') use ($quiet): void {
    if (!$quiet) {
        echo $line . "\n";
    }
};

$say(str_repeat('=', 74));
$say('  Legacy migration: ' . LEGACY_DB_NAME . '  ->  ' . DB_NAME);
$say('  Mode: ' . ($commit ? 'COMMIT (writes)' : 'DRY RUN (writes nothing)'));
$say(str_repeat('=', 74));
$say();

// --- Connect -----------------------------------------------------------------

try {
    $legacy = Database::connect(LEGACY_DB_NAME);
} catch (Throwable $e) {
    exit("Cannot open the legacy database '" . LEGACY_DB_NAME . "': " . $e->getMessage() . "\n");
}

$target = Database::pdo();

// --- Discover the per-user tables --------------------------------------------
// The regex excludes the `user` table itself, which is not a per-user table.

$tables = $legacy->query(
    "SELECT table_name FROM information_schema.tables
      WHERE table_schema = " . $legacy->quote(LEGACY_DB_NAME) . "
        AND table_name REGEXP '^user[0-9]{9}$'"
)->fetchAll(PDO::FETCH_COLUMN);

$legacyUsers = $legacy->query('SELECT * FROM `user`')->fetchAll(PDO::FETCH_ASSOC);

$say(sprintf('Found %d legacy user row(s) and %d per-user table(s).',
    count($legacyUsers), count($tables)));
$say();

// Both directions of the mismatch matter.
$userIds    = array_column($legacyUsers, 'ID');
$tableIds   = array_map(static fn($t) => substr($t, 4), $tables);
$defaultPlan = (string) (Database::scalar('SELECT code FROM plans WHERE is_default = 1 LIMIT 1') ?: 'basic');

foreach (array_diff($tableIds, $userIds) as $orphan) {
    $note('warning', 'ORPHAN_TABLE',
        "Table user{$orphan} has no matching row in `user`; its records cannot be attributed and are skipped.",
        'user' . $orphan);
}

// --- Migrate each user -------------------------------------------------------

foreach ($legacyUsers as $legacyUser) {
    $code  = (string) $legacyUser['ID'];
    $table = 'user' . $code;

    $say("User {$code} — " . ($legacyUser['name'] ?? '?'));

    try {
        if ($commit) {
            $target->beginTransaction();
        }

        // -- The user row ----------------------------------------------------

        $email    = strtolower(trim((string) $legacyUser['mail']));
        $rawPhone = trim((string) $legacyUser['phone']);
        $phone    = normalize_phone($rawPhone);

        if ($phone === null && $rawPhone !== '') {
            // Keep the original rather than dropping it. A migration must not
            // lose data it cannot interpret; the number is flagged so it can be
            // corrected, and the user is asked for a valid one next time they
            // edit their profile.
            $phone = mb_substr($rawPhone, 0, 20);
            $note('warning', 'PHONE_UNPARSEABLE',
                "Phone '{$rawPhone}' is not a valid Bangladeshi mobile number; kept as-is for review.",
                $table);
        }

        // Login is by e-mail now, so a usable address is required. The legacy
        // `user` table had no UNIQUE index on `mail`.
        if ($email === '' || !valid_email($email)) {
            $note('error', 'DUP_EMAIL',
                "User {$code} has no usable e-mail ('{$legacyUser['mail']}'); skipped. "
                . 'Set a valid address in the legacy database and re-run.', $table);
            $counts['skipped']++;
            if ($commit) {
                $target->rollBack();
            }
            $say();
            continue;
        }

        $existing = Database::one('SELECT id, user_code FROM users WHERE user_code = ?', [$code]);
        $clash    = Database::one('SELECT id, user_code FROM users WHERE email = ?', [$email]);

        if ($clash && (!$existing || $clash['id'] !== $existing['id'])) {
            $note('error', 'DUP_EMAIL',
                "E-mail {$email} already belongs to user_code "
                . ($clash['user_code'] ?? $clash['id']) . "; user {$code} skipped.", $table);
            $counts['skipped']++;
            if ($commit) {
                $target->rollBack();
            }
            $say();
            continue;
        }

        // The legacy admin "reset password" set the password to the user's own
        // id, and ids were sequential. profile/about.php even warned about it.
        $mustChange = password_verify($code, (string) $legacyUser['password']);
        if ($mustChange) {
            $note('warning', 'PASSWORD_IS_DEFAULT_ID',
                "User {$code}'s password equals their user id; a password change is forced at next sign-in.",
                $table);
        }

        if ($existing) {
            $userId = (int) $existing['id'];
            $say("  user row already migrated (id {$userId})");
        } elseif ($commit) {
            Database::run(
                'INSERT INTO users
                    (user_code, name, email, phone, password, role, plan_code, status,
                     locale, must_change_password, password_changed_at, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
                [
                    $code,
                    trim((string) $legacyUser['name']) ?: 'User ' . $code,
                    $email,
                    $phone,
                    // bcrypt hashes port across unchanged; both tables used
                    // password_hash(PASSWORD_BCRYPT).
                    (string) $legacyUser['password'],
                    'customer',
                    $defaultPlan,
                    'active',
                    DEFAULT_LANG,
                    $mustChange ? 1 : 0,
                    $legacyUser['passlast'] ?: null,
                ]
            );
            $userId = (int) Database::lastId();
            $counts['users']++;
            $say("  created user id {$userId}");
        } else {
            $userId = 0; // dry run
            $counts['users']++;
            $say('  would create user row');
        }

        // -- Their property records ------------------------------------------

        if (!in_array($table, $tables, true)) {
            $note('warning', 'MISSING_TABLE',
                "User {$code} has no {$table}; migrated with zero property records.", $table);
            if ($commit) {
                $target->commit();
            }
            $say();
            continue;
        }

        $rows = $legacy->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        $say('  ' . count($rows) . ' property record(s)');

        foreach ($rows as $row) {
            $uid = (string) $row['UID'];

            // rdate is DATE NOT NULL in the legacy schema but the insert never
            // populated it, so every live row holds the zero date.
            $deedDate = $row['rdate'];
            if ($deedDate === '0000-00-00' || $deedDate === null || $deedDate === '') {
                $deedDate = null;
                $note('info', 'ZERO_DATE', "{$table}.{$uid}: registration date was empty.", $table, $uid);
            }

            // size was VARCHAR(7); anything non-numeric is preserved in notes
            // rather than discarded.
            $rawSize = trim((string) $row['size']);
            $area    = null;
            $notes   = null;
            if ($rawSize !== '') {
                if (is_numeric($rawSize)) {
                    $area = $rawSize;
                } else {
                    $notes = 'Legacy area value: ' . $rawSize;
                    $note('warning', 'NON_NUMERIC_SIZE',
                        "{$table}.{$uid}: area '{$rawSize}' is not numeric; kept in notes.", $table, $uid);
                }
            }

            // Flag malformed comma lists before they are split.
            foreach (['dagno', 'pdagno', 'khatian', 'pkhatian'] as $column) {
                $raw = (string) $row[$column];
                if ($raw === '') {
                    continue;
                }
                foreach (explode(',', $raw) as $piece) {
                    if (trim($piece) === '') {
                        $note('info', 'EMPTY_TOKEN',
                            "{$table}.{$uid}: {$column} '{$raw}' has an empty segment.", $table, $uid);
                    } elseif (!valid_identifier_token(trim($piece))) {
                        $note('warning', 'TOKEN_NOT_NUMERIC',
                            "{$table}.{$uid}: {$column} token '" . trim($piece) . "' is not a plain number.",
                            $table, $uid);
                    }
                }
            }

            $data = [
                // Raw strings preserved exactly, spaces and all.
                'dag_current'      => (string) $row['dagno'],
                'dag_previous'     => (string) $row['pdagno'],
                'khatian_current'  => (string) $row['khatian'],
                'khatian_previous' => (string) $row['pkhatian'],
            ];

            if (!$commit) {
                $counts['properties']++;
                $counts['identifiers'] += count(split_tokens($data['dag_current']))
                    + count(split_tokens($data['dag_previous']))
                    + count(split_tokens($data['khatian_current']))
                    + count(split_tokens($data['khatian_previous']));
                continue;
            }

            // Idempotency: (user_id, legacy_uid) is unique.
            $already = Database::scalar(
                'SELECT id FROM properties WHERE user_id = ? AND legacy_uid = ?',
                [$userId, $uid]
            );
            if ($already) {
                continue;
            }

            Database::run(
                'INSERT INTO properties
                    (user_id, seq, deed_no, deed_date, area_cent, old_owner, new_owner, mouja,
                     dag_current, dag_previous, khatian_current, khatian_previous, notes, legacy_uid)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $userId,
                    // The legacy UID was a zero-padded string ('001'); it becomes
                    // the numeric display sequence.
                    (int) $uid,
                    (string) $row['dnum'],
                    $deedDate,
                    $area,
                    (string) $row['oldowner'],
                    (string) $row['newowner'],
                    (string) $row['mouja'],
                    $data['dag_current'],
                    $data['dag_previous'],
                    $data['khatian_current'],
                    $data['khatian_previous'],
                    $notes,
                    $uid,
                ]
            );

            $propertyId = (int) Database::lastId();
            App\Property::reindex($propertyId, $userId, $data);

            $counts['properties']++;
            $counts['identifiers'] += (int) Database::scalar(
                'SELECT COUNT(*) FROM property_identifiers WHERE property_id = ?',
                [$propertyId]
            );
        }

        if ($commit) {
            $target->commit();
        }
    } catch (Throwable $e) {
        if ($commit && $target->inTransaction()) {
            $target->rollBack();
        }
        $counts['skipped']++;
        $note('error', 'USER_FAILED', "User {$code}: " . $e->getMessage(), $table);
    }

    $say();
}

// --- Contact messages and visitor history ------------------------------------
// Both legacy tables used a 1-second TIMESTAMP as PRIMARY KEY, so rows were
// already being lost before this migration ever ran. What survived is carried
// across; the undercount is not recoverable.

$say('Contact messages and visitor history…');

if ($commit) {
    foreach ($legacy->query('SELECT * FROM `massage`')->fetchAll(PDO::FETCH_ASSOC) as $m) {
        $exists = Database::scalar(
            'SELECT COUNT(*) FROM contact_messages WHERE email = ? AND created_at = ?',
            [strtolower(trim((string) $m['email'])), $m['time']]
        );
        if (!$exists) {
            Database::run(
                'INSERT INTO contact_messages (name, email, message, status, created_at)
                 VALUES (?, ?, ?, ?, ?)',
                [
                    (string) $m['name'],
                    strtolower(trim((string) $m['email'])),
                    (string) $m['text'],
                    'read',
                    $m['time'],
                ]
            );
        }
    }

    foreach ($legacy->query('SELECT * FROM `visitors`')->fetchAll(PDO::FETCH_ASSOC) as $v) {
        $exists = Database::scalar(
            'SELECT COUNT(*) FROM page_views WHERE ip = ? AND created_at = ?',
            [(string) $v['ip'], $v['time']]
        );
        if (!$exists) {
            Database::run(
                'INSERT INTO page_views (ip, path, created_at) VALUES (?, ?, ?)',
                [(string) $v['ip'], '/', $v['time']]
            );
        }
    }
}

$messageCount = (int) $legacy->query('SELECT COUNT(*) FROM `massage`')->fetchColumn();
$visitorCount = (int) $legacy->query('SELECT COUNT(*) FROM `visitors`')->fetchColumn();
$say("  {$messageCount} message(s), {$visitorCount} visitor row(s)");
$say();

// --- Persist the report ------------------------------------------------------

if ($commit && $report) {
    foreach ($report as $entry) {
        Database::run(
            'INSERT INTO migration_report (legacy_table, legacy_uid, severity, code, message)
             VALUES (?, ?, ?, ?, ?)',
            [$entry['table'], $entry['uid'], $entry['severity'], $entry['code'],
             mb_substr($entry['message'], 0, 500)]
        );
    }
}

// --- Summary -----------------------------------------------------------------

$byCode = [];
foreach ($report as $entry) {
    $byCode[$entry['code']] = ($byCode[$entry['code']] ?? 0) + 1;
}

echo str_repeat('=', 74) . "\n";
echo "  SUMMARY" . ($commit ? '' : '  (dry run — nothing was written)') . "\n";
echo str_repeat('=', 74) . "\n";
printf("  users migrated      %d\n",  $counts['users']);
printf("  users skipped       %d\n",  $counts['skipped']);
printf("  property records    %d\n",  $counts['properties']);
printf("  identifier rows     %d\n",  $counts['identifiers']);

if ($byCode) {
    echo "\n  Anomalies:\n";
    ksort($byCode);
    foreach ($byCode as $code => $n) {
        printf("    %-24s %d\n", $code, $n);
    }
}

if (!$commit) {
    echo "\n  Re-run with --commit to apply.\n";
} else {
    echo "\n  Done. The legacy database was not modified.\n";
    echo "  Full detail: SELECT * FROM migration_report ORDER BY id;\n";
}
echo "\n";
