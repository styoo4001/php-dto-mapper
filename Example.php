<?php
// formData from Http Request
$_REQUEST = [
    'name' => 'styoo',
    'age' => '35',
    'money' = '1,000'
];

class Controller
{
    // converting array data into DTO.
    public function example()
    {
        $mapper = new DataTransferObjectMapper();
        /** @var User $user */
        $user = $mapper->mapping($_REQUEST, User::class)->getClass();
        if ($mapper->hasErrors()) throw new Exception(var_export($mapper->getErrors(), true));
        $user->getName() === 'styoo';
        $user->getAge() === 35;
        $user->getMoney()->getAmount() === 1000;
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
class User extends CommandObject
{
    private string $name;
    private int $age;
    private Money $money;

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