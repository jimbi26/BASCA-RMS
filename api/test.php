<?php
// Simple PHP runtime test for Vercel
header('Content-Type: text/plain; charset=utf-8');
echo "PHP test OK\n";
echo "PHP version: " . phpversion() . "\n";
echo "getenv('BASE_URL')=" . (getenv('BASE_URL') ?: '(not set)') . "\n";

// Basic PDO test helper (does not attempt a DB connection here).
// To test DB connection, visit a separate endpoint that uses the environment variables.
