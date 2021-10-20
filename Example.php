<?php


class ExampleController
{

    public function index(UserDto $user)
    {
        if ($user->hasError()) {
            var_dump($user->getErrors());
        }
        echo $user->getUserName();
    }

    public function simpleArrayToDto()
    {
        $data = [
            'userName'=> '',
            'userAge' => '35',
            'money' => '100',
            'moneyList' => [ 5, 100, 200],
            'nullable' => null,
            'skill' => ['php', 'javascript'],
            'eyesight' => '0.85',
            'married' => 0,
            'userBirth' => '19870301'
        ];
        $mapper = new DataTransferObjectMapper();
        $mapper->mapping($data,UserDto::class);
        if($mapper->hasErrors()){
            var_dump($mapper->getErrors());
        }
        /** @var UserDto $user */
        $user = $mapper->getClass();

        echo $user->getUserName();
    }



}