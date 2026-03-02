<?php
declare(strict_types=1);

// Migration script — runs once per deploy via 'zsc execOnce' in
// initCommands. Creates the greetings table and seeds the initial row.
// Idempotent: IF NOT EXISTS and ON CONFLICT DO NOTHING are defense-in-depth
// even though zsc execOnce prevents repeated execution.

try {
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        getenv('DB_HOST') ?: 'localhost',
        getenv('DB_PORT') ?: '5432',
        getenv('DB_NAME') ?: 'db',
    );

    $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS greetings (
            id      INTEGER PRIMARY KEY,
            message TEXT    NOT NULL
        )
    ");

    $pdo->exec("
        INSERT INTO greetings (id, message)
        VALUES (1, 'Hello from Zerops!')
        ON CONFLICT (id) DO NOTHING
    ");

    echo "Migration completed successfully\n";
    exit(0);
} catch (Throwable $e) {
    echo 'Migration failed: ' . $e->getMessage() . "\n";
    exit(1);
}
