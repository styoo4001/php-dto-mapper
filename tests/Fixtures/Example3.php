<?php

namespace Tests\Fixtures;

class Example3
{
    private int $number;

    private string $string;

    private ?string $thirdArg;

    public function __construct(int $firstArgument, string $secondArgument = 'defaultValue', ?string $thirdArgument = null)
    {
        $this->number = $firstArgument + 1000;
        $this->string = $secondArgument;
        $this->thirdArg = $thirdArgument;
    }

    public function getString(): string
    {
        return $this->string;
    }

    public function getThirdArg(): ?string
    {
        return $this->thirdArg;
    }
}
