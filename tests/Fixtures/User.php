<?php

namespace Tests\Fixtures;

use DateTime;

/**
 * Class User
 * Inheritance is only necessary when used as a command object.
 * class User extends CommandObject
 *
 * @namingConvention camel
 */
class User
{
    private string $name;

    /** @ParamName(age) */
    private int $age;

    private DateTime $birthDay;

    /** @Column(name="eyesight") */
    private float $eyesight;

    private Money $money;

    private array $correctArray;

    private array $inCorrectArray;

    /** @var Money[] @namespace Tests\Fixtures\Money */
    private array $moneyList;

    private PlusMoney $plusMoney;

    /** @var PlusMoney[] @namespace Tests\Fixtures\PlusMoney */
    private array $plusMoneyList;

    private Skill $skill;

    /** @var Skill[] @namespace Tests\Fixtures\Skill */
    private array $skillList;

    private Example $example;

    /** @var Example[] @namespace Tests\Fixtures\Example */
    private array $exampleList;

    private ?string $allowNull;

    private Example2 $example2;

    private Example2 $example21;

    private Example3 $example3;

    private Example3 $example31;

    private Example3 $example4;

    private Example3 $example41;

    public function getName(): string
    {
        return $this->name;
    }

    public function getEyesight(): float
    {
        return $this->eyesight;
    }

    public function getBirthDay(): DateTime
    {
        return $this->birthDay;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getCorrectArray(): array
    {
        return $this->correctArray;
    }

    public function getInCorrectArray(): array
    {
        return $this->inCorrectArray;
    }

    /**
     * @return Money[]
     */
    public function getMoneyList(): array
    {
        return $this->moneyList;
    }

    /**
     * @return PlusMoney[]
     */
    public function getPlusMoneyList(): array
    {
        return $this->plusMoneyList;
    }

    /**
     * @return Skill[]
     */
    public function getSkillList(): array
    {
        return $this->skillList;
    }

    public function getMoney(): Money
    {
        return $this->money;
    }

    public function getPlusMoney(): PlusMoney
    {
        return $this->plusMoney;
    }

    public function getSkill(): Skill
    {
        return $this->skill;
    }

    public function getExample(): Example
    {
        return $this->example;
    }

    /**
     * @return Example[]
     */
    public function getExampleList(): array
    {
        return $this->exampleList;
    }

    public function getExample2(): Example2
    {
        return $this->example2;
    }

    public function getExample21(): Example2
    {
        return $this->example21;
    }

    public function getExample3(): Example3
    {
        return $this->example3;
    }

    public function getExample31(): Example3
    {
        return $this->example31;
    }

    public function getExample4(): Example3
    {
        return $this->example4;
    }

    public function getExample41(): Example3
    {
        return $this->example41;
    }

    public function getAllowNull(): ?string
    {
        return $this->allowNull;
    }

    protected function rule(): array
    {
        return ['name' => ['required'], 'age' => ['required', 'int']];
    }
}
