<?php

require_once "src/RequestCommand.php";
require_once "src/CommandObject.php";
require_once "src/DataTransferObjectMapper.php";

// formData from Http Request
$_REQUEST = [
    'name' => 'styoo',
    'age' => '35',
    'birthDay' => '2021-01-01 15:00:02',
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
    ],
    'example2' => ['secondArgument' => "example", 'firstArgument' => 1000], // If there is a key value, the order is not important. like namedArgument on php 8 //
    'example21' => [1000, "example"],
    'example3' => ['firstArgument' => 1000, 'thirdArgument' => "third"],
    'example31' => [1000],
    'example4' => ['firstArgument' => 1000, "secondArgument" => "customValue"],
    'example41' => [1000, "customValue", "third"],

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

        echo $user->getExample2()->getNumber() === 2000;
        echo $user->getExample21()->getNumber() === 2000;
        echo $user->getExample3()->getString() === "defaultValue";
        echo $user->getExample31()->getString() === "defaultValue";
        echo $user->getExample3()->getThirdArg() === "third";
        echo $user->getExample31()->getThirdArg() === null;

        echo $user->getExample4()->getString() === "customValue";
        echo $user->getExample41()->getString() === "customValue";
        echo $user->getExample4()->getThirdArg() === null;
        echo $user->getExample41()->getThirdArg() === "third";

        echo var_export($user->getBirthDay(),true);
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
    private DateTime $birthDay;
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

    /**
     * @return Example2
     */
    public function getExample2(): Example2
    {
        return $this->example2;
    }

    /**
     * @return Example2
     */
    public function getExample21(): Example2
    {
        return $this->example21;
    }

    /**
     * @return Example3
     */
    public function getExample3(): Example3
    {
        return $this->example3;
    }

    /**
     * @return Example3
     */
    public function getExample31(): Example3
    {
        return $this->example31;
    }

    /**
     * @return Example3
     */
    public function getExample4(): Example3
    {
        return $this->example4;
    }

    /**
     * @return Example3
     */
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

class Example3
{
    private int $number;
    private string $string;
    private ?string $thirdArg;

    public function __construct(int $firstArgument, string $secondArgument = "defaultValue", ?string $thirdArgument = null)
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
