<?php

namespace Tests\Fixtures;

use Exception;
use Styoo4001\PhpDtoMapper\DataTransferObjectMapper;

class Controller
{
    // converting array data into DTO.
    public function example()
    {
        $mapper = new DataTransferObjectMapper();
        /** @var User $user */
        $user = $mapper->mapping($_REQUEST, User::class)->getClass();
        if ($mapper->hasErrors()) {
            throw new Exception(var_export($mapper->getErrors(), true));
        }
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
        echo $user->getExample3()->getString() === 'defaultValue';
        echo $user->getExample31()->getString() === 'defaultValue';
        echo $user->getExample3()->getThirdArg() === 'third';
        echo $user->getExample31()->getThirdArg() === null;

        echo $user->getExample4()->getString() === 'customValue';
        echo $user->getExample41()->getString() === 'customValue';
        echo $user->getExample4()->getThirdArg() === null;
        echo $user->getExample41()->getThirdArg() === 'third';

        echo var_export($user->getBirthDay(), true);
    }

    // If there is a provider layer in the framework,
    // you can apply this mapper to implement it like a Command Object.
    public function index(User $user)
    {
        /** @disregard P1013 */
        if ($user->hasErrors()) {
            /** @disregard P1013 */
            throw new Exception(var_export($user->getErrors(), true));
        }

        $user->getName() === 'styoo';
        $user->getAge() === 35;
        $user->getMoney()->getAmount() === 1000;

    }
}
