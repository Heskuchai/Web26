<?php

declare(strict_types=1);

interface AccountInterface
{
    public function deposit(int $amount): void;

    public function withdraw(int $amount): void;

    public function getBalance(): int;
}
