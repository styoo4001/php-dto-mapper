<?php

require_once "src/RequestCommand.php";
require_once "src/CommandObject.php";
require_once "src/DataTransferObjectMapper.php";

// formData from Http Request
$_REQUEST = [
    'name' => 'styoo',
    'age' => '35',
    'eyesight' => '0.8',
    'correctArray' => [1, 2, 3],
    'inCorrectArray' => 1,
    'money' => '1,000',
    'moneyList' => ["1,000", "2,000"],
    'plus_money' => '1,000',
    'plus_money_list' => ["1,000", "2,000"],
    '  skill  ' => ['server' => 'php', 'client' => 'js'],
    'skillList' => [
        ['server' => 'php5', 'client' => 'js'],
        ['server' => 'php7', 'client' => 'js']
    ],
    'example' => ['example1' => 500, 'example2' => 'string'],
    'exampleList' => [
        ['example1' => 500, 'example2' => 'string'],
        ['example1' => 1000, 'example2' => 'string']
    ]
];

$class = new Controller();

$class->example();

class Controller
{
    // converting array data into DTO.
    public function example()
    {
        $mapper = new DataTransferObjectMapper();
        /** @var User $user */
        $user = $mapper->mapping($_REQUEST, User::class)->getClass();
        if ($mapper->hasErrors()) throw new Exception(var_export($mapper->getErrors(), true));
        echo $user->getName() === 'styoo';
        echo $user->getAge() === 35;
        echo $user->getCorrectArray() === [1, 2, 3];
        echo $user->getInCorrectArray() === [1];
        echo $user->getEyesight() === 0.8;
        echo $user->getMoney()->getAmount() === 1000;
        echo $user->getPlusMoney()->getAmount() === 2000;
        echo $user->getAllowNull() === null;
        echo $user->getSkill()->getServer() === 'php';
        echo $user->getExample()->getNumber() === 1500;

        foreach ($user->getSkillList() as $skill) {
            echo $skill->getServer();
        }
        foreach ($user->getMoneyList() as $money) {
            echo $money->getAmount();
        }
        foreach ($user->getPlusMoneyList() as $plusMoney) {
            echo $plusMoney->getAmount();
        }
        foreach ($user->getExampleList() as $example) {
            echo $example->getNumber();
        }

    }

    // If there is a provider layer in the framework,
    // you can apply this mapper to implement it like a Command Object.
    public function index(User $user)
    {
        if ($user->hasErrors()) throw new Exception(var_export($user->getErrors(), true));

        $user->getName() === 'styoo';
        $user->getAge() === 35;
        $user->getMoney()->getAmount() === 1000;

    }
}

// Inheritance is only necessary when used as a command object.
//class User extends CommandObject

/**
 * Class User
 * @namingConvention camel
 */
class User
{
    private string $name;
    private int $age;
    private float $eyesight;
    private Money $money;

    private array $correctArray;
    private array $inCorrectArray;

    /** @var Money[] @namespace Money */
    private array $moneyList;
    private PlusMoney $plusMoney;

    /** @var PlusMoney[] @namespace PlusMoney */
    private array $plusMoneyList;

    private Skill $skill;
    /** @var Skill[] @namespace Skill */
    private array $skillList;

    private Example $example;
    /** @var Example[] @namespace Example */
    private array $exampleList;
    private ?string $allowNull;

    public function getName(): string
    {
        return $this->name;
    }

    public function getEyesight(): float
    {
        return $this->eyesight;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @return array
     */
    public function getCorrectArray(): array
    {
        return $this->correctArray;
    }

    /**
     * @return array
     */
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


    public function getAllowNull(): ?string
    {
        return $this->allowNull;
    }

    protected function rule(): array
    {
        return ['name' => ['required'], 'age' => ['required', 'int']];
    }
}

class Money
{
    private int $amount;

    public function getAmount(): int
    {
        return $this->amount;
    }
}

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

class Skill
{
    private string $client;
    private string $server;

    public function getServer(): string
    {
        return $this->server;
    }

}

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

