<?php
// Supabase Storage connectivity test
header('Content-Type: text/plain; charset=utf-8');
echo "Supabase Storage Test\n";

$supabaseUrl = getenv('SUPABASE_URL') ?: '';
$supabaseKey = getenv('SUPABASE_SERVICE_KEY') ?: '';
$bucket = getenv('SUPABASE_BUCKET') ?: '';

if ($supabaseUrl === '' || $supabaseKey === '') {
    echo "Missing SUPABASE_URL or SUPABASE_SERVICE_KEY environment variables.\n";
    http_response_code(400);
    exit;
}

$endpoint = rtrim($supabaseUrl, '/') . '/storage/v1/buckets';

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $supabaseKey,
    'apikey: ' . $supabaseKey,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($response === false) {
    echo "cURL error: " . $curlErr . "\n";
    http_response_code(500);
    exit;
}

echo "HTTP Status: " . $httpCode . "\n";
echo "Response body:\n";
echo $response . "\n";

// If a specific bucket was provided, show a simple check URL
if ($bucket !== '') {
    echo "\nProvided bucket: $bucket\n";
    echo "You can also test listing objects via the storage API once permissions are confirmed.\n";
}
