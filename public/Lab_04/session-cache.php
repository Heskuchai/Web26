<?php

declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
$start = hrtime(true);
session_cache_limiter('');

$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (!session_start([
    'cookie_path' => rtrim($scriptDirectory, '/') . '/',
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
])) {
    http_response_code(500);
    exit('Не вдалося відкрити сесію.');
}

$key = 'lab04_currency_rates';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    unset($_SESSION[$key]);
    session_write_close();
    header('Location: session-cache.php', true, 303);
    exit;
}

$state = isset($_SESSION[$key]) ? 'HIT' : 'MISS';
if ($state === 'MISS') {
    sleep(2);
    $_SESSION[$key] = [
        'generated' => gmdate('Y-m-d H:i:s') . ' UTC',
        'USD' => number_format(random_int(4050, 4450) / 100, 2, '.', ''),
        'EUR' => number_format(random_int(4500, 4950) / 100, 2, '.', ''),
    ];
}
$rates = $_SESSION[$key];
session_write_close();

$duration = (hrtime(true) - $start) / 1e6;
header("X-Session-Cache: $state");
header(sprintf('Server-Timing: session;dur=%.2F;desc="%s"', $duration, $state));
?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Демонстраційні курси валют</title>
    <link rel="stylesheet" href="asset.php?type=css">
</head>
<body>
<main>
    <a href="index.html">До лабораторної</a>
    <h1>Демонстраційні курси валют</h1>
    <p>Кеш сесії: <?= $state ?>. Згенеровано: <?= $rates['generated'] ?></p>
    <ul>
        <li>USD: <?= $rates['USD'] ?> грн</li>
        <li>EUR: <?= $rates['EUR'] ?> грн</li>
    </ul>
    <form method="post"><button>Очистити кеш сесії</button></form>
</main>
</body>
</html>