<?php
declare(strict_types=1);

header('Content-Type: application/json');

$response = [
    'type'     => 'php',
    'greeting' => '',
    'status'   => [],
];

$httpCode = 200;

try {
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        getenv('DB_HOST'),
        getenv('DB_PORT'),
        getenv('DB_NAME'),
    );

    $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT            => 5,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Verify connectivity and query the migrated greeting row.
    $stmt = $pdo->query('SELECT message FROM greetings LIMIT 1');
    $row  = $stmt->fetch();

    $response['greeting']           = $row['message'];
    $response['status']['database'] = 'OK';
} catch (Throwable $e) {
    $response['greeting']           = '';
    $response['status']['database'] = 'ERROR: ' . $e->getMessage();
    $httpCode = 503;
}

http_response_code($httpCode);
echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
