<?php

declare(strict_types=1);

$logPath = __DIR__ . '/log.txt';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST['message'] ?? '';
    $message = is_string($input) ? trim($input) : '';

    if ($message === '' || strlen($message) > 4000 || !preg_match('//u', $message)) {
        http_response_code(400);
        $error = 'Введіть непорожній текст у кодуванні UTF-8 розміром до 4000 байтів.';
    } else {
        $entry = $message . PHP_EOL;
        if (@file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX) === strlen($entry)) {
            header('Location: text.php?saved=1', true, 303);
            exit;
        }
        http_response_code(500);
        $error = 'Не вдалося записати текст у журнал.';
    }
}

$content = '';
if (is_file($logPath)) {
    $log = @fopen($logPath, 'rb');
    if ($log !== false) {
        if (flock($log, LOCK_SH)) {
            $content = stream_get_contents($log);
            flock($log, LOCK_UN);
        } else {
            $content = false;
        }
        fclose($log);
    } else {
        $content = false;
    }

    if ($content === false && $error === '') {
        http_response_code(500);
        $error = 'Не вдалося прочитати журнал.';
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Журнал записів</title>
</head>
<body>
<h1>Журнал записів</h1>
<?php if ($error !== ''): ?>
    <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php elseif (($_GET['saved'] ?? '') === '1'): ?>
    <p>Текст додано до log.txt.</p>
<?php endif; ?>

<?php if ($content === ''): ?>
    <p>У журналі ще немає записів.</p>
<?php elseif ($content !== false): ?>
    <pre><?= htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></pre>
<?php endif; ?>

<p><a href="index.html">Повернутися до форми</a></p>
</body>
</html>
