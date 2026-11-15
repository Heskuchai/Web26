<?php

declare(strict_types=1);

$assets = [
    'css' => ['style.css', 'text/css; charset=UTF-8'],
    'image' => ['cache.svg', 'image/svg+xml'],
];
$type = $_GET['type'] ?? null;

if (!is_string($type) || !isset($assets[$type])) {
    http_response_code(404);
    exit('Resource not found');
}

[$file, $mime] = $assets[$type];
$maxAge = 86400;
header("Cache-Control: public, max-age=$maxAge");
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');
header("Content-Type: $mime");
readfile(__DIR__ . '/' . $file);