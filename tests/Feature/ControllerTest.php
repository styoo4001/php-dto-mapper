<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Controller;

final class ControllerTest extends TestCase
{
    public function test_form_data_from_form(): void
    {
        $this->expectNotToPerformAssertions();

        $_REQUEST = [
            'name' => 'styoo',
            'age' => 35,
            'birthDay' => '2021-01-01 15:00:02',
            'eyesight' => '0.8',
            'correctArray' => [1, 2, 3],
            'inCorrectArray' => 1,
            'money' => '1,000',
            'moneyList' => ['1,000', '2,000'],
            'plus_money' => '1,000',
            'plus_money_list' => ['1,000', '2,000'],
            '  skill  ' => ['server' => 'php', 'client' => 'js'],
            'skillList' => [
                ['server' => 'php5', 'client' => 'js'],
                ['server' => 'php7', 'client' => 'js'],
            ],
            'example' => ['example1' => 500, 'example2' => 'string'],
            'exampleList' => [
                ['example1' => 500, 'example2' => 'string'],
                ['example1' => 1000, 'example2' => 'string'],
            ],
            'example2' => ['secondArgument' => 'example', 'firstArgument' => 1000], // If there is a key value, the order is not important. like namedArgument on php 8 //
            'example21' => [1000, 'example'],
            'example3' => ['firstArgument' => 1000, 'thirdArgument' => 'third'],
            'example31' => [1000],
            'example4' => ['firstArgument' => 1000, 'secondArgument' => 'customValue'],
            'example41' => [1000, 'customValue', 'third'],

        ];

        $class = new Controller();

        $class->example();
    }
}
