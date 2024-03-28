<?php

namespace Tests\Fixtures;

class PlusMoney
{
    private int $amount;

    public function __construct(int $amount)
    {
        $this->amount = $amount + 1000;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }
}
