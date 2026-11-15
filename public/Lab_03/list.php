<?php

declare(strict_types=1);

$directory = __DIR__ . '/uploads';
$entries = is_dir($directory) ? @scandir($directory) : [];
$files = [];

if ($entries === false) {
    http_response_code(500);
} else {
    foreach ($entries as $name) {
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (in_array($extension, ['png', 'jpg', 'jpeg'], true) && is_file($directory . '/' . $name)) {
            $files[] = $name;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Список завантажених файлів</title>
</head>
<body>
<h1>Список завантажених файлів</h1>
<?php if ($entries === false): ?>
    <p role="alert">Не вдалося прочитати папку uploads.</p>
<?php elseif ($files === []): ?>
    <p>Завантажених файлів ще немає.</p>
<?php else: ?>
    <ul>
        <?php foreach ($files as $name): ?>
            <li>
                <a href="uploads/<?= rawurlencode($name) ?>" download>
                    <?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<p><a href="index.html">Повернутися до форми</a></p>
</body>
</html>
