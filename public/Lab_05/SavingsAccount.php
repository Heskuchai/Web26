<?php

declare(strict_types=1);

require_once __DIR__ . '/BankAccount.php';

class SavingsAccount extends BankAccount
{
    private static int $interestRate = 5;

    public function applyInterest(): void
    {
        $balance = $this->getBalance();
        $rate = self::$interestRate;

        // Округлюємо до найближчого цента, половину цента — вгору.
        $interest = intdiv($balance, 100) * $rate
            + intdiv(($balance % 100) * $rate + 50, 100);

        if ($interest > 0) {
            $this->deposit($interest);
        }
    }
}
