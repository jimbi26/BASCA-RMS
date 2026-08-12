<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
// Use safeLoad so the app won't error when there's no .env (e.g. on Vercel).
$dotenv->safeLoad();

// Prefer variables loaded into $_ENV, otherwise fall back to getenv().
$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? '';
$port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '';
$dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? '';
$username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? '';
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? '';

try {

    $pdo = new PDO(
        "pgsql:host={$host};port={$port};dbname={$dbname}",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]
    );

} catch (PDOException $e) {

    die(
        "Database connection failed: " .
        $e->getMessage()
    );
}