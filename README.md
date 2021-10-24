# What is PHP-DataTransferObject-Mapper?

This mapper is useful when converting data in an array into DTO.

It automatically manages the type when using the type hint in the latest PHP version (7.4 or higher) environment, so that developers do not care about the type.

External libraries are not required and only operate with PHP APIs.

If there is a provider layer in the framework, you can apply this mapper to implement it like a Command Object.

# How to use

f you use it simply for data mapping, you only need to use one data transfer mapper.php file.

If you want to use it like a command object, use the whole thing.


```
<?php
// formData from Http Request
$_REQUEST = [
    'name' => 'styoo',
    'age' => '35'
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
    }

    // If there is a provider layer in the framework,
    // you can apply this mapper to implement it like a Command Object.
    public function index(User $user)
    {
        if ($user->hasErrors()) throw new Exception(var_export($user->getErrors(), true));

        $user->getName() === 'styoo';
        $user->getAge() === 35;
    }
}

// Inheritance is only necessary when used as a command object.
class User extends CommandObject
{
    private string $name;
    private int $age;

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    protected function rule(): array
    {
        return ['name' => ['required'], 'age' => ['required', 'int']];
    }
}
```
