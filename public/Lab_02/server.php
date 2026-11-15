<?php

require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php', true, 303);
    exit;
}

$serverInfo = [
    'IP-адреса клієнта (REMOTE_ADDR)'
    => $_SERVER['REMOTE_ADDR'] ?? 'Невідомо',

    'Браузер (HTTP_USER_AGENT)'
    => $_SERVER['HTTP_USER_AGENT'] ?? 'Не передано',

    'Поточний скрипт (PHP_SELF)'
    => $_SERVER['PHP_SELF'] ?? 'Невідомо',

    'Метод запиту (REQUEST_METHOD)'
    => $_SERVER['REQUEST_METHOD'] ?? 'Невідомо',

    'Шлях до файлу (SCRIPT_FILENAME)'
    => $_SERVER['SCRIPT_FILENAME'] ?? 'Невідомо',
];

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Інформація про сервер і запит</title>
</head>
<body>
<h1>Інформація із $_SERVER</h1>

<dl>
    <?php foreach ($serverInfo as $label => $value): ?>
        <dt><strong><?= escape($label) ?></strong></dt>
        <dd><?= escape($value) ?></dd>
    <?php endforeach; ?>
</dl>

<p><a href="index.php">На головну</a></p>
</body>
</html>
