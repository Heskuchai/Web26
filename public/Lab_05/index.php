<?php

declare(strict_types=1);

require_once __DIR__ . '/SavingsAccount.php';

function formatBalance(BankAccount $account): string
{
    $amount = $account->getBalance();
    return sprintf('%d.%02d %s', intdiv($amount, 100), $amount % 100, $account->getCurrency());
}

function depositTo(AccountInterface $account, int $amount): void
{
    $account->deposit($amount);
}

function withdrawFrom(AccountInterface $account, int $amount): void
{
    $account->withdraw($amount);
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$bankUsd = new BankAccount('USD');
$bankEur = new BankAccount('EUR');
$savingsUsd = new SavingsAccount('USD');
$savingsEur = new SavingsAccount('EUR');

$scenarios = [
    ['Проведені операції', [
        ['Звичайний USD', $bankUsd, 'Поповнення на 100.00 USD', fn () => depositTo($bankUsd, 10000)],
        ['Звичайний USD', $bankUsd, 'Зняття 35.50 USD', fn () => withdrawFrom($bankUsd, 3550)],
        ['Звичайний EUR', $bankEur, 'Поповнення на 80.00 EUR', fn () => depositTo($bankEur, 8000)],
        ['Звичайний EUR', $bankEur, 'Зняття всього балансу', fn () => withdrawFrom($bankEur, 8000)],
        ['Накопичувальний USD', $savingsUsd, 'Поповнення на 200.00 USD', fn () => depositTo($savingsUsd, 20000)],
        ['Накопичувальний USD', $savingsUsd, 'Нарахування 5 %', $savingsUsd->applyInterest(...)],
        ['Накопичувальний USD', $savingsUsd, 'Зняття 15.00 USD', fn () => withdrawFrom($savingsUsd, 1500)],
        ['Накопичувальний EUR', $savingsEur, 'Нарахування 5 % на нульовий баланс', $savingsEur->applyInterest(...)],
        ['Накопичувальний EUR', $savingsEur, 'Поповнення на 12.30 EUR', fn () => depositTo($savingsEur, 1230)],
        ['Накопичувальний EUR', $savingsEur, 'Нарахування 5 % з округленням', $savingsEur->applyInterest(...)],
    ]],
    ['Відхилені операції', [
        ['Звичайний USD', $bankUsd, 'Зняття 65.00 USD', fn () => withdrawFrom($bankUsd, 6500)],
        ['Накопичувальний USD', $savingsUsd, 'Поповнення на -10.00 USD', fn () => depositTo($savingsUsd, -1000)],
        ['Накопичувальний EUR', $savingsEur, 'Зняття -1.00 EUR', fn () => withdrawFrom($savingsEur, -100)],
        ['Звичайний EUR', $bankEur, 'Зняття 0.00 EUR', fn () => withdrawFrom($bankEur, 0)],
    ]],
];
?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Лабораторна робота №5</title>
    <style>
        body { margin: 0; background: #f3f5f8; color: #172337; font: 16px/1.5 system-ui, sans-serif; }
        main { max-width: 1040px; margin: 28px auto; padding: 0 20px; }
        h1 { margin: 0; font-size: 32px; }
        h2 { margin: 20px 0 8px; font-size: 21px; }
        p { margin: 2px 0 0; color: #44546a; }
        .table-scroll { overflow-x: auto; background: white; border: 1px solid #d2dce8; border-radius: 9px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 7px 12px; border-bottom: 1px solid #e2e9f0; }
        th { background: #e8eef6; }
        tbody tr:last-child td { border-bottom: 0; }
        td:nth-child(3), td:nth-child(4) { white-space: nowrap; font-variant-numeric: tabular-nums; }
        .ok { color: #17613b; }
        .error { color: #922f28; }
    </style>
</head>
<body>
<main>
    <h1>Банківські рахунки</h1>
    <?php foreach ($scenarios as [$title, $operations]): ?>
        <section>
            <h2><?= escape($title) ?></h2>
            <div class="table-scroll">
                <table>
                    <thead>
                    <tr><th>Рахунок</th><th>Операція</th><th>До</th><th>Після</th><th>Результат</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($operations as [$name, $account, $label, $operation]): ?>
                        <?php
                        $before = formatBalance($account);
                        try {
                            $operation();
                            $status = 'ok';
                            $message = 'Виконано';
                        } catch (Exception $exception) {
                            $status = 'error';
                            $message = $exception->getMessage();
                        }
                        ?>
                        <tr>
                            <td><?= escape($name) ?></td>
                            <td><?= escape($label) ?></td>
                            <td><?= escape($before) ?></td>
                            <td><?= escape(formatBalance($account)) ?></td>
                            <td class="<?= $status ?>"><?= escape($message) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endforeach; ?>
</main>
</body>
</html>
