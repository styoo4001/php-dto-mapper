<?php

namespace Styoo4001\PhpDtoMapper\Dto;

class Money
{
    private int $amount;

    /**
     * Money constructor.
     */
    public function __construct(int $amount)
    {
        $this->amount = $amount;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }
}
