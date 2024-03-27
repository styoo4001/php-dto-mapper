# PHP-DataTransferObject-Mapper

[![code-style](https://github.com/styoo4001/php-dto-mapper/actions/workflows/code-style.yml/badge.svg)](https://github.com/styoo4001/php-dto-mapper/actions/workflows/code-style.yml)
[![run-tests](https://github.com/styoo4001/php-dto-mapper/actions/workflows/run-tests.yml/badge.svg)](https://github.com/styoo4001/php-dto-mapper/actions/workflows/run-tests.yml)

## Update 1.10

### 1. multi array bind with create Class

```php
if $userList = [
    ['user_nm' => 'A' , 'age' => 15],
    ['user_nm' => 'B' , 'age' => 16],
];
/** @var User[] */
public array $userList;

// then  get_class($this->userList[0]) === User::class;
                
```

### 2. @separator

```php
@separator
// if ?numbers="123,456,789"
/** @var int[] @separator ","  */
public array $numbers;

// then  $numbers = [123,456,789];
// if /** @var string[] @separator ","  */
// then  $numbers = ['123','456','789'];
```

### 3. @ParamName

```php
// if ?number="123"
/** @ParamName("number") or @ParamName(number) or @ParamName('number')   */
public int $no;

// then  $this->no = 123;
```

### 4. @Column('name=')

```php
// if $db = [ 'usr_nm' => 'seungtae.yoo' ];

/** @Column('usr_nm') or @Column(anyString = 'user_nm')   */
public string $userName;

// then  $this->userName = "seungtae.yoo";
```

## What is PHP-DataTransferObject-Mapper?

This mapper is useful when converting data in an array into DTO.

It automatically manages the type when using the type hint in the latest PHP version (7.4 or higher) environment, so that developers do not care about the type.

External libraries are not required and only operate with PHP APIs.

If there is a provider layer in the framework, you can apply this mapper to implement it like a Command Object.

## How to use

f you use it simply for data mapping, you only need to use one data transfer mapper.php file.

If you want to use it like a command object, use the whole thing.

```php
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

## Testing

```shell
composer test
```

And you can see below:

```shell
> ./vendor/bin/phpunit tests
PHPUnit 11.0.8 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.2.16
Configuration: /Users/cable8mm/Sites/php-dto-mapper/phpunit.xml.dist

1111111111php5php71000200020003000150020001111111111\DateTime::__set_state(array(
   'date' => '2021-01-01 15:00:02.000000',
   'timezone_type' => 3,
   'timezone' => 'UTC',
)).                                                                   1 / 1 (100%)

Time: 00:00.007, Memory: 8.00 MB

OK (1 test, 0 assertions)
```

## Formatting

```shell
composer lint
# Modify all files to comply with the PSR-12.

composer inspect
# Inspect all files to ensure compliance with PSR-12.
```
