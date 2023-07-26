<?php

namespace yiiunit\framework\db;

use yiiunit\data\ar\ActiveRecord;

abstract class BaseActiveRecordTest extends DatabaseTestCase
{
    protected function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
    }

    public function provideArrayValueWithChange()
    {
        return [
            'not an associative array with data change' => [
                [1, 2, 3],
                [1, 3, 2],
            ],

            'associative array with data change case 1' => [
                ['pineapple' => 2, 'apple' => 5, 'banana' => 1],
                ['apple' => 5, 'pineapple' => 1, 'banana' => 3],
            ],
            'associative array with data change case 2' => [
                ['pineapple' => 2, 'apple' => 5, 'banana' => 1],
                ['pineapple' => 2, 'apple' => 3, 'banana' => 1],
            ],

            'filling an empty array' => [
                [],
                ['pineapple' => 3, 'apple' => 1, 'banana' => 1],
            ],
            'zeroing the array' => [
                ['pineapple' => 3, 'apple' => 1, 'banana' => 17],
                [],
            ],
        ];
    }
}
