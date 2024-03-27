<?php

namespace Tests\Fixtures;

class Example2
{
    private int $number;

    private string $string;

    public function __construct(int $firstArgument, string $secondArgument)
    {
        $this->number = $firstArgument + 1000;
        $this->string = $secondArgument;
    }

    public function getNumber(): int
    {
        return $this->number;
    }
}
