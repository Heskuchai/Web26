<?php

session_name('Session');

$cookieOptions = [
    'path' => rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/',
    'secure' => !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
];

session_set_cookie_params([
        'lifetime' => 0,
    ] + $cookieOptions);

session_start();

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');

$now = time();

if (
    isset($_SESSION['last_activity'])
    && $now - $_SESSION['last_activity'] > 300
) {
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['session_expired'] = true;
}

$_SESSION['last_activity'] = $now;

$products = [
    1 => ['name' => 'Блокнот', 'price' => 80],
    2 => ['name' => 'Ручка', 'price' => 25],
    3 => ['name' => 'Олівець', 'price' => 15],
];

function escape(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function postText(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? trim($value) : '';
}

function readPreviousCart(array $products): array
{
    $raw = $_COOKIE['previous_cart'] ?? '';

    if (!is_string($raw)) {
        return [];
    }

    $data = json_decode($raw, true);

    if (!is_array($data)) {
        return [];
    }

    $cart = [];

    foreach ($products as $id => $product) {
        $quantity = $data[$id] ?? 0;

        if (
            is_int($quantity)
            && $quantity >= 1
            && $quantity <= 99
        ) {
            $cart[$id] = $quantity;
        }
    }

    return $cart;
}

if (!isset($_SESSION['previous_cart'])) {
    $_SESSION['previous_cart'] = readPreviousCart($products);
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
