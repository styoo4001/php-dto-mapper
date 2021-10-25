<?php

require_once "src/RequestCommand.php";
require_once "src/CommandObject.php";
require_once "src/DataTransferObjectMapper.php";

// formData from Http Request
$_REQUEST = [
    'name' => 'styoo',
    'age' => '35',
    'money' => '1,000',
    'plus_money' => '1,000',
    '  skill  ' => ['server' => 'php', 'client' => 'js'],
    'example' => ['example1' => 500, 'example2' => 'string']

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
        echo $user->getMoney()->getAmount() === 1000;
        echo $user->getPlusMoney()->getAmount() === 2000;
        echo $user->getAllowNull() === null;
        echo $user->getSkill()->getServer() === 'php';
        echo $user->getExample()->getNumber() === 1500;

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

/**
 * Class User
 * @namingConvention camel
 */
class User
{
    private string $name;
    private int $age;
    private Money $money;
    private PlusMoney $plusMoney;
    private Skill $skill;
    private Example $example;
    private ?string $allowNull;

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
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

