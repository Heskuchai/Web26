<?php

declare(strict_types=1);

const REPORT_TTL = 600;

function generateReport(): string
{
    sleep(3);
    $names = ['Анна', 'Богдан', 'Ірина', 'Максим', 'Олена'];
    $generatedAt = gmdate('Y-m-d H:i:s') . ' UTC';
    $html = '<!doctype html><html lang="uk"><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Звіт про замовлення</title>'
        . '<link rel="stylesheet" href="asset.php?type=css">'
        . '<main><a href="index.html">До лабораторної</a>'
        . '<h1>Звіт про замовлення</h1><p>Згенеровано: ' . $generatedAt . '</p>'
        . '<table><thead><tr><th>№</th><th>Ім’я</th><th>Сума, грн</th>'
        . '<th>Дата</th></tr></thead><tbody>';

    for ($i = 1; $i <= 1000; $i++) {
        $name = $names[array_rand($names)];
        $amount = number_format(random_int(1000, 500000) / 100, 2, '.', '');
        $date = gmdate('Y-m-d', time() - random_int(0, 30) * 86400);
        $html .= "<tr><td>$i</td><td>$name</td><td>$amount</td><td>$date</td></tr>";
    }

    return $html . '</tbody></table></main></html>';
}

function loadReport(string $directory): array
{
    if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Cannot create the report cache directory.');
    }

    $file = $directory . '/report.html';
    $lock = @fopen($directory . '/report.lock', 'c');
    if ($lock === false) {
        throw new RuntimeException('Cannot open the report cache lock.');
    }

    try {
        if (!flock($lock, LOCK_EX)) {
            throw new RuntimeException('Cannot lock the report cache.');
        }

        clearstatcache(true, $file);
        $mtime = is_file($file) ? filemtime($file) : false;
        if ($mtime !== false && time() - $mtime < REPORT_TTL) {
            $cached = @file_get_contents($file);
            if ($cached !== false && $cached !== '') {
                return ['HIT', $cached];
            }
        }

        $html = generateReport();
        $temporary = tempnam($directory, 'report-');
        if ($temporary === false) {
            throw new RuntimeException('Cannot create the temporary report.');
        }

        try {
            if (@file_put_contents($temporary, $html) !== strlen($html)
                || !@rename($temporary, $file)
            ) {
                throw new RuntimeException('Cannot save the report cache.');
            }
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }

        return ['MISS', $html];
    } finally {
        fclose($lock);
    }
}

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
$start = hrtime(true);

try {
    [$state, $html] = loadReport(dirname(__DIR__, 2) . '/storage/app/lab04');
    header("X-Report-Cache: $state");
    $duration = (hrtime(true) - $start) / 1e6;
    header(sprintf('Server-Timing: report;dur=%.2F;desc="%s"', $duration, $state));
    echo $html;
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    echo 'Не вдалося підготувати звіт.';
}