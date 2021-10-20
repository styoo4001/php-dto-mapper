<?php

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
            'userName' => ['required', "xss_clean"],
        ];
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @return int
     */
    public function getUserAge(): int
    {
        return $this->userAge;
    }

    /**
     * @return float
     */
    public function getEyesight(): float
    {
        return $this->eyesight;
    }

    /**
     * @return array
     */
    public function getSkill(): array
    {
        return $this->skill;
    }

    /**
     * @return bool
     */
    public function isMarried(): bool
    {
        return $this->married;
    }

    /**
     * @return string|null
     */
    public function getNullable(): ?string
    {
        return $this->nullable;
    }

    /**
     * @return DateTime
     */
    public function getUserBirth(): DateTime
    {
        return $this->userBirth;
    }

    /**
     * @return Money
     */
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