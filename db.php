<?php
/**
 * Database bootstrap for admin/auth features.
 *
 * Uses PDO with a configurable DSN (via DATABASE_URL or DB_DSN) and falls back
 * to a local SQLite database in data/database.sqlite when not provided.
 */

function getPdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = getenv('DB_DSN') ?: getenv('DATABASE_URL');
    if (!$dsn) {
        $dataDir = __DIR__ . '/data';
        if (!is_dir($dataDir) && !mkdir($dataDir, 0750, true) && !is_dir($dataDir)) {
            throw new RuntimeException('Unable to create data directory for SQLite database.');
        }
        $dsn = 'sqlite:' . $dataDir . '/database.sqlite';
    }

    $user = getenv('DB_USER') ?: null;
    $password = getenv('DB_PASS') ?: null;

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $pdo = new PDO($dsn, $user, $password, $options);
    ensureUsersTable($pdo);
    ensureOrdersTable($pdo);

    return $pdo;
}

function ensureUsersTable(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL,
            name TEXT,
            google_id TEXT,
            is_admin INTEGER DEFAULT 0,
            metamask_address TEXT,
            coinbase_address TEXT
        )'
    );
}

function ensureOrdersTable(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS orders (
            order_id VARCHAR(64) PRIMARY KEY,
            user_email TEXT,
            payment_status TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )'
    );
}
