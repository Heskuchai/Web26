<?php

declare(strict_types=1);

require_once __DIR__ . '/AccountInterface.php';

class BankAccount implements AccountInterface
{
    public const int MIN_BALANCE = 0;

    private int $balance = self::MIN_BALANCE;

    public function __construct(private readonly string $currency)
    {
        if (!in_array($currency, ['USD', 'EUR'], true)) {
            throw new InvalidArgumentException('Підтримуються лише валюти USD та EUR.');
        }
    }

    public function deposit(int $amount): void
    {
        $this->validateAmount($amount);
        if ($amount > PHP_INT_MAX - $this->balance) {
            throw new OverflowException('Сума перевищує допустимий баланс.');
        }

        $this->balance += $amount;
    }

    public function withdraw(int $amount): void
    {
        $this->validateAmount($amount);
        if ($amount > $this->balance - self::MIN_BALANCE) {
            throw new RuntimeException('Недостатньо коштів');
        }

        $this->balance -= $amount;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    private function validateAmount(int $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Сума має бути більшою за нуль.');
        }
    }
}
