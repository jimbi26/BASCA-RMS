<?php
// DB connection test (PostgreSQL via PDO)
header('Content-Type: text/plain; charset=utf-8');
echo "DB Connection Test\n";

$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: '';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASSWORD') ?: '';

if ($host === '' || $dbname === '' || $user === '') {
    echo "Missing required DB environment variables.\n";
    echo "Please set DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD in Vercel.\n";
    http_response_code(400);
    exit;
}

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Connected to database successfully.\n";
    $stmt = $pdo->query('SELECT version()');
    $version = $stmt ? $stmt->fetchColumn() : '(unknown)';
    echo "Postgres version: " . $version . "\n";
    http_response_code(200);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    http_response_code(500);
}
