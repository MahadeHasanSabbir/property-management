<?php
/**
 * Thin PDO wrapper — a lazily-created singleton connection plus prepared-
 * statement conveniences. Every query in the application goes through here, so
 * all user input is bound, never interpolated.
 *
 * This replaces 29 copies of mysqli_connect("localhost","root","","property")
 * and roughly the same number of raw interpolated query strings, none of which
 * used a prepared statement.
 *
 * Adapted from the sister project (Aminship) so both codebases share one data
 * layer shape.
 */

namespace App;

defined('APP_BOOTSTRAPPED') || exit;

final class Database
{
    private static ?\PDO $pdo = null;

    /** Get (or open) the shared PDO connection. */
    public static function pdo(): \PDO
    {
        if (self::$pdo === null) {
            self::$pdo = self::connect(DB_NAME);
        }
        return self::$pdo;
    }

    /**
     * Open a connection to an arbitrary database. Used by the migration script,
     * which needs the legacy schema alongside the new one.
     */
    public static function connect(string $database): \PDO
    {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, $database, DB_CHARSET);

        return new \PDO($dsn, DB_USER, DB_PASS, [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            // Real prepared statements, so the driver never builds SQL by
            // string substitution.
            \PDO::ATTR_EMULATE_PREPARES   => false,
            \PDO::ATTR_STRINGIFY_FETCHES  => false,
        ]);
    }

    /** Run a prepared statement and return the statement handle. */
    public static function run(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Fetch a single row, or null. */
    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** Fetch all rows. */
    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    /** Fetch a single scalar from the first column. */
    public static function scalar(string $sql, array $params = [])
    {
        return self::run($sql, $params)->fetchColumn();
    }

    /** Last auto-increment id. */
    public static function lastId(): string
    {
        return self::pdo()->lastInsertId();
    }

    // --- Transactions --------------------------------------------------------
    // The legacy registration ran three statements with no transaction, so a
    // failure partway through left a user row whose data table was never
    // created — an account permanently broken on every page load.

    public static function begin(): void
    {
        self::pdo()->beginTransaction();
    }

    public static function commit(): void
    {
        self::pdo()->commit();
    }

    public static function rollBack(): void
    {
        if (self::pdo()->inTransaction()) {
            self::pdo()->rollBack();
        }
    }

    /** Run a callback inside a transaction, rolling back on any exception. */
    public static function transaction(callable $fn)
    {
        self::begin();
        try {
            $result = $fn();
            self::commit();
            return $result;
        } catch (\Throwable $e) {
            self::rollBack();
            throw $e;
        }
    }
}
