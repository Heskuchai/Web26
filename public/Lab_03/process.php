<?php

declare(strict_types=1);

const MAX_IMAGE_BYTES = 2 * 1024 * 1024;
const IMAGE_TYPES = [
    'png' => IMAGETYPE_PNG,
    'jpg' => IMAGETYPE_JPEG,
    'jpeg' => IMAGETYPE_JPEG,
];

function rejectUpload(string $message, int $status = 400): never
{
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="uk">
    <head><meta charset="UTF-8"><title>Помилка завантаження</title></head>
    <body>
    <h1>Помилка завантаження</h1>
    <p>{$message}</p>
    <p><a href="index.html">Повернутися до форми</a></p>
    </body>
    </html>
    HTML;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    rejectUpload('Скористайтеся формою завантаження.', 405);
}

if ($_FILES === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > MAX_IMAGE_BYTES) {
    rejectUpload('Розмір файлу перевищує 2 МБ.', 413);
}

$file = $_FILES['image'] ?? [];
$error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
if ($error !== UPLOAD_ERR_OK) {
    match ($error) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => rejectUpload('Розмір файлу перевищує 2 МБ.', 413),
        UPLOAD_ERR_NO_FILE => rejectUpload('Оберіть зображення для завантаження.'),
        default => rejectUpload('Не вдалося отримати файл. Спробуйте ще раз.'),
    };
}

$tempPath = $file['tmp_name'];
if (!is_uploaded_file($tempPath)) {
    rejectUpload('PHP не підтвердив завантаження файлу через HTTP.');
}

$size = filesize($tempPath);
if ($size === false || $size === 0) {
    rejectUpload('Файл порожній або його розмір недоступний.');
}
if ($size > MAX_IMAGE_BYTES) {
    rejectUpload('Розмір файлу перевищує 2 МБ.', 413);
}

$originalName = $file['name'];
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
if (!isset(IMAGE_TYPES[$extension])) {
    rejectUpload('Дозволені лише файли PNG, JPG та JPEG.');
}

$image = @getimagesize($tempPath);
if ($image === false || $image[2] !== IMAGE_TYPES[$extension]) {
    rejectUpload('Вміст файлу не відповідає його розширенню.');
}


$stem = pathinfo($originalName, PATHINFO_FILENAME);
$stem = trim(preg_replace('/[^\p{L}\p{N}_-]+/u', '-', $stem) ?? '', '-_');
preg_match('/^.{1,44}/u', $stem, $prefix);
$stem = $prefix[0] ?? 'image';
if (preg_match('/^(CON|PRN|AUX|NUL|COM[0-9¹²³]|LPT[0-9¹²³])$/iu', $stem)) {
    $stem = 'image-' . $stem;
}

$directory = __DIR__ . '/uploads';
if (!is_dir($directory) && !@mkdir($directory, 0755) && !is_dir($directory)) {
    rejectUpload('Не вдалося створити папку uploads.', 500);
}

$lock = @fopen($directory . '/.upload.lock', 'c');
if ($lock === false || !flock($lock, LOCK_EX)) {
    rejectUpload('Не вдалося відкрити сховище для запису.', 500);
}


$savedName = $stem . '.' . $extension;
$duplicate = file_exists($directory . '/' . $savedName);
while (file_exists($directory . '/' . $savedName)) {
    $savedName = $stem . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
}
$moved = @move_uploaded_file($tempPath, $directory . '/' . $savedName);
flock($lock, LOCK_UN);
fclose($lock);

if (!$moved) {
    rejectUpload('Не вдалося зберегти файл у папці uploads.', 500);
}

$safeOriginalName = htmlspecialchars($originalName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$safeSavedName = htmlspecialchars($savedName, ENT_QUOTES, 'UTF-8');
$sizeInKb = number_format($size / 1024, 2, ',', ' ');
$downloadUrl = 'uploads/' . rawurlencode($savedName);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Зображення завантажено</title>
</head>
<body>
<h1>Зображення завантажено</h1>
<?php if ($duplicate): ?>
    <p>Файл із таким іменем уже існує. До нового імені додано суфікс.</p>
<?php endif; ?>
<dl>
    <dt>Початкове ім’я</dt><dd><?= $safeOriginalName ?></dd>
    <dt>Ім’я у сховищі</dt><dd><?= $safeSavedName ?></dd>
    <dt>Тип</dt><dd><?= $image['mime'] ?></dd>
    <dt>Розмір</dt><dd><?= $sizeInKb ?> КБ</dd>
</dl>
<p><a href="<?= $downloadUrl ?>" download>Скачати файл</a></p>
<p><a href="list.php">Переглянути список файлів</a></p>
<p><a href="index.html">Повернутися до форми</a></p>
</body>
</html>
