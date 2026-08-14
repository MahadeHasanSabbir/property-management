<?php
/**
 * Rebuild `property_identifiers` from the raw dag/khatian strings.
 *
 * Usage:
 *     php database/reindex.php
 *
 * This exists because the raw strings on `properties` are canonical and the
 * identifier table is derived. That relationship is what makes the design safe:
 * a bug in the comma splitter can be corrected and the index regenerated,
 * because nothing the user typed was ever thrown away.
 *
 * Run it after changing split_tokens(), or any time the search results look
 * wrong.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("This script must be run from the command line.\n");
}

require_once __DIR__ . '/../includes/bootstrap.php';

use App\Database;
use App\Property;

$rows = Database::all(
    'SELECT id, user_id, dag_current, dag_previous, khatian_current, khatian_previous
       FROM properties
      WHERE deleted_at IS NULL
      ORDER BY id'
);

echo 'Reindexing ' . count($rows) . " property record(s)…\n";

$before = (int) Database::scalar('SELECT COUNT(*) FROM property_identifiers');
$done   = 0;

foreach ($rows as $row) {
    Property::reindex((int) $row['id'], (int) $row['user_id'], $row);
    $done++;

    if ($done % 100 === 0) {
        echo "  {$done}…\n";
    }
}

$after = (int) Database::scalar('SELECT COUNT(*) FROM property_identifiers');

echo "\nDone.\n";
printf("  identifier rows before  %d\n", $before);
printf("  identifier rows after   %d\n", $after);

if ($before !== $after) {
    printf("  difference              %+d\n", $after - $before);
    echo "\n  A difference is expected only if the tokeniser changed or rows were\n";
    echo "  edited outside the application. Otherwise investigate.\n";
}
echo "\n";
