<?php

require __DIR__ . '/common.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['session_expired'])) {
        header('Location: index.php', true, 303);
        exit;
    }

    $token = postText('csrf_token');

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Помилка перевірки форми. Оновіть сторінку.');
    }

    $action = postText('action');

    switch ($action) {
        case 'save_name':
            $name = postText('user_name');

            if ($name === '' || strlen($name) > 200) {
                $error = 'Введіть ім’я довжиною до 200 байтів.';
                break;
            }

            setcookie(
                'user_name',
                $name,
                ['expires' => time() + 7 * 24 * 60 * 60]
                + $cookieOptions
            );
            break;

        case 'delete_name':
            setcookie(
                'user_name',
                '',
                ['expires' => time() - 3600] + $cookieOptions
            );
            break;

        case 'login':
            $login = postText('login');
            $password = $_POST['password'] ?? '';

            if ($login !== 'student' || $password !== '12345') {
                $error = 'Неправильний логін або пароль.';
                break;
            }

            session_regenerate_id(true);

            $_SESSION['user'] = $login;
            break;

        case 'logout':
            $_SESSION = [];

            setcookie(
                session_name(),
                '',
                ['expires' => time() - 3600] + $cookieOptions
            );

            session_destroy();

            header('Location: index.php', true, 303);
            exit;

        case 'add_product':
            $id = filter_var(
                postText('product_id'),
                FILTER_VALIDATE_INT
            );

            if ($id === false || !isset($products[$id])) {
                $error = 'Товар не знайдено.';
                break;
            }

            $quantity = $_SESSION['cart'][$id] ?? 0;

            if ($quantity >= 99) {
                $error = 'Можна додати не більше 99 одиниць товару.';
                break;
            }

            $_SESSION['cart'][$id] = $quantity + 1;

            setcookie(
                'previous_cart',
                json_encode($_SESSION['cart']),
                ['expires' => time() + 7 * 24 * 60 * 60]
                + $cookieOptions
            );
            break;

        case 'clear_cart':
            $_SESSION['cart'] = [];

            setcookie(
                'previous_cart',
                '',
                ['expires' => time() - 3600] + $cookieOptions
            );
            break;

        default:
            $error = 'Невідома дія.';
    }

    if ($error === '') {
        header('Location: index.php', true, 303);
        exit;
    }
}

$name = $_COOKIE['user_name'] ?? '';
$name = is_string($name) ? $name : '';

$sessionExpired = $_SESSION['session_expired'] ?? false;
unset($_SESSION['session_expired']);

$csrfToken = $_SESSION['csrf_token'];

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Лабораторна робота №2</title>
    <style>
        body {
            max-width: 850px;
            margin: 30px auto;
            padding: 0 16px;
            font-family: Arial, sans-serif;
            line-height: 1.5;
        }

        section {
            margin-bottom: 24px;
            padding: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        input, button {
            margin: 4px;
            padding: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .error { color: #b00020; }
        .notice { color: #805000; }
    </style>
</head>
<body>
<h1>Лабораторна робота №2</h1>

<?php if ($error !== ''): ?>
    <p class="error"><?= escape($error) ?></p>
<?php endif; ?>

<?php if ($sessionExpired): ?>
    <p class="notice">
        Попередню сесію завершено через понад 5 хвилин
        бездіяльності.
    </p>
<?php endif; ?>

<section>
    <h2>1. Робота з cookie</h2>

    <?php if ($name !== ''): ?>
        <p>Вітаємо, <?= escape($name) ?>!</p>
    <?php else: ?>
        <p>Ім’я ще не збережено.</p>
    <?php endif; ?>

    <form method="post" action="index.php">
        <input type="hidden" name="csrf_token"
               value="<?= escape($csrfToken) ?>">
        <input type="hidden" name="action" value="save_name">

        <label for="user_name">Ім’я:</label>
        <input id="user_name" name="user_name" type="text"
               value="<?= escape($name) ?>" maxlength="50" required>

        <button type="submit">Зберегти на 7 днів</button>
    </form>

    <form method="post" action="index.php">
        <input type="hidden" name="csrf_token"
               value="<?= escape($csrfToken) ?>">
        <input type="hidden" name="action" value="delete_name">
        <button type="submit">Видалити cookie з ім’ям</button>
    </form>
</section>

<section>
    <h2>2. Робота із сесією</h2>

    <?php if (isset($_SESSION['user'])): ?>
        <p>
            Вітаємо,
            <strong><?= escape($_SESSION['user']) ?></strong>!
        </p>

        <form method="post" action="index.php">
            <input type="hidden" name="csrf_token"
                   value="<?= escape($csrfToken) ?>">
            <input type="hidden" name="action" value="logout">
            <button type="submit">Вихід</button>
        </form>
    <?php else: ?>
        <p>Навчальний обліковий запис: student / 12345.</p>

        <form method="post" action="index.php">
            <input type="hidden" name="csrf_token"
                   value="<?= escape($csrfToken) ?>">
            <input type="hidden" name="action" value="login">

            <label for="login">Логін:</label>
            <input id="login" name="login" type="text"
                   autocomplete="username" required>

            <br>

            <label for="password">Пароль:</label>
            <input id="password" name="password" type="password"
                   autocomplete="current-password" required>

            <br>

            <button type="submit">Увійти</button>
        </form>
    <?php endif; ?>
</section>

<section>
    <h2>3. Інформація із $_SERVER</h2>

    <form method="post" action="server.php">
        <button type="submit">Показати інформацію про запит</button>
    </form>

    <p>
        <a href="server.php">Відкрити server.php методом GET</a>
        — відбудеться перенаправлення на головну.
    </p>
</section>

<section>
    <h2>4. Корзина покупок</h2>

    <h3>Каталог</h3>

    <?php foreach ($products as $id => $product): ?>
        <form method="post" action="index.php">
            <input type="hidden" name="csrf_token"
                   value="<?= escape($csrfToken) ?>">
            <input type="hidden" name="action" value="add_product">
            <input type="hidden" name="product_id" value="<?= $id ?>">

            <?= escape($product['name']) ?>
            — <?= $product['price'] ?> грн

            <button type="submit">Додати</button>
        </form>
    <?php endforeach; ?>

    <h3>Поточна корзина: $_SESSION</h3>

    <?php if ($_SESSION['cart'] === []): ?>
        <p>Корзина порожня.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Товар</th>
                <th>Кількість</th>
                <th>Сума</th>
            </tr>

            <?php $total = 0; ?>

            <?php foreach ($_SESSION['cart'] as $id => $quantity): ?>
                <?php
                $subtotal = $products[$id]['price'] * $quantity;
                $total += $subtotal;
                ?>
                <tr>
                    <td><?= escape($products[$id]['name']) ?></td>
                    <td><?= $quantity ?></td>
                    <td><?= $subtotal ?> грн</td>
                </tr>
            <?php endforeach; ?>
        </table>

        <p><strong>Разом: <?= $total ?> грн</strong></p>
    <?php endif; ?>

    <form method="post" action="index.php">
        <input type="hidden" name="csrf_token"
               value="<?= escape($csrfToken) ?>">
        <input type="hidden" name="action" value="clear_cart">
        <button type="submit">Очистити корзину</button>
    </form>

    <h3>Товари попереднього сеансу: cookie</h3>

    <?php if ($_SESSION['previous_cart'] === []): ?>
        <p>Збережених товарів немає.</p>
    <?php else: ?>
        <ul>
            <?php foreach (
                $_SESSION['previous_cart'] as $id => $quantity
            ): ?>
                <li>
                    <?= escape($products[$id]['name']) ?>
                    — <?= $quantity ?> шт.
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<section>
    <h2>5. Час активності сесії</h2>

    <p>
        Сесія завершується після понад 300 секунд бездіяльності.
        Перевірка виконується під час наступного запиту.
    </p>

    <p>
        Остання активність, Unix timestamp:
        <?= $_SESSION['last_activity'] ?>
    </p>
</section>
</body>
</html>
