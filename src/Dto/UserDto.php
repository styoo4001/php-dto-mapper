<?php

namespace Styoo4001\PhpDtoMapper\Dto;

use DateTime;
use Styoo4001\PhpDtoMapper\CommandObject;

/**
 * Class UserDto
 */
class UserDto extends CommandObject
{
    private string $userName;

    private int $userAge;

    private float $eyesight;

    private array $skill;

    private bool $married;

    private ?string $nullable;

    private DateTime $userBirth;

    private Money $money;

    /** @var Money[] @namespace Money */
    private array $moneyList;

    protected function rule(): array
    {
        return [
            'userName' => ['required', 'xss_clean'],
        ];
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getUserAge(): int
    {
        return $this->userAge;
    }

    public function getEyesight(): float
    {
        return $this->eyesight;
    }

    public function getSkill(): array
    {
        return $this->skill;
    }

    public function isMarried(): bool
    {
        return $this->married;
    }

    public function getNullable(): ?string
    {
        return $this->nullable;
    }

    public function getUserBirth(): DateTime
    {
        return $this->userBirth;
    }

    public function getMoney(): Money
    {
        return $this->money;
    }

    /**
     * @return Money[]
     */
    public function getMoneyList(): array
    {
        return $this->moneyList;
    }
}
