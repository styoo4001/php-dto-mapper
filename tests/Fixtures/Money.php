<?php

namespace Tests\Fixtures;

class Money
{
    private int $amount;

    public function getAmount(): int
    {
        return $this->amount;
    }
}
