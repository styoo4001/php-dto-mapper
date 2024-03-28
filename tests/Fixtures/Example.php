<?php

namespace Tests\Fixtures;

class Example
{
    private int $number;

    private string $string;

    public function __construct(array $data)
    {
        $this->number = $data['example1'] + 1000;
        $this->string = $data['example2'];
    }

    public function getNumber(): int
    {
        return $this->number;
    }
}
