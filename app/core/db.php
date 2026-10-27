<?php
/**
 * Database: PDO connection (MySQL in production, SQLite for local development) and query helpers.
 */
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $c = config('db', []);
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
    if (($c['driver'] ?? 'mysql') === 'sqlite') {
        $file = $c['path'] ?? (STORAGE_DIR . '/database.sqlite');
        $pdo = new PDO('sqlite:' . $file, null, null, $opts);
        $pdo->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');
    } else {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'] ?? 'localhost', (int)($c['port'] ?? 3306), $c['name'] ?? '');
        $pdo = new PDO($dsn, $c['user'] ?? '', $c['pass'] ?? '', $opts);
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    return $pdo;
}

function db_driver(): string
{
    return (string)db()->getAttribute(PDO::ATTR_DRIVER_NAME);
}

function q(string $sql, array $p = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($p);
    return $st;
}

function row(string $sql, array $p = []): ?array
{
    $r = q($sql, $p)->fetch();
    return $r === false ? null : $r;
}

function rows(string $sql, array $p = []): array
{
    return q($sql, $p)->fetchAll();
}

function val(string $sql, array $p = [])
{
    $v = q($sql, $p)->fetchColumn();
    return $v === false ? null : $v;
}

function insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql = 'INSERT INTO ' . $table . ' (' . implode(',', $cols) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')';
    q($sql, array_values($data));
    return (int)db()->lastInsertId();
}

function update(string $table, array $data, string $where, array $wp = []): int
{
    if (!$data) return 0;
    $set = implode(',', array_map(function ($c) { return $c . '=?'; }, array_keys($data)));
    return q('UPDATE ' . $table . ' SET ' . $set . ' WHERE ' . $where, array_merge(array_values($data), $wp))->rowCount();
}
