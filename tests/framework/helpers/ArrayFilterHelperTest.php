<?php

namespace yiiunit\framework\helpers;

use yii\helpers\ArrayFilterHelper;
use yiiunit\TestCase;

/**
 * @group helpers
 */
class ArrayFilterHelperTest extends TestCase
{
    protected function data()
    {
        return [
            ['id' => 1,  'name' => 'Jakobe',   'age' => 29, 'gender' => 'm'],
            ['id' => 2,  'name' => 'Cedric',   'age' => 23, 'gender' => 'm'],
            ['id' => 3,  'name' => 'Adolphus', 'age' => 41, 'gender' => 'm'],
            ['id' => 4,  'name' => 'Arlen',    'age' => 51, 'gender' => 'm'],
            ['id' => 5,  'name' => 'Delwin',   'age' => 30, 'gender' => 'm'],
            ['id' => 6,  'name' => 'Lavera',   'age' => 43, 'gender' => 'f'],
            ['id' => 7,  'name' => 'Sharday',  'age' => 56, 'gender' => 'f'],
            ['id' => 8,  'name' => 'Leitha',   'age' => 39, 'gender' => 'f'],
            ['id' => 9,  'name' => 'Kyleigh',  'age' => 28, 'gender' => 'f'],
            ['id' => 10, 'name' => 'Leora',    'age' => 52, 'gender' => 'f'],
        ];
    }

    public function provider()
    {
        return [
            [
                [
                    ['>', 'id', 5],
                    ['NOT IN', 'id', [7, 9]]
                ],
                [6, 8, 10]
            ],
            [
                [
                    ['gender' => 'm'],
                    ['<', 'age', 30]
                ],
                [1, 2]
            ],
            [
                [
                    ['LIKE', 'name', 'L%', false]
                ],
                [6, 8, 10]
            ],
            [
                [
                    ['or', ['gender' => 'f'], ['id' => [3, 6]]]
                ],
                [3, 6, 7, 8, 9, 10]
            ],
            [
                [
                    [
                        'and',
                        ['BETWEEN', 'id', 4, 7],
                        ['NOT', ['gender' => 'f']]
                    ]
                ],
                [5]
            ],
        ];
    }

    /**
     * @dataProvider provider
     * @param $filters
     * @param $result
     */
    public function testFilters($filters, $result)
    {
        $filtered = ArrayFilterHelper::filterModels(self::data(), $filters);
        $this->assertCount(count($result), $filtered);
        foreach ($filtered as $item) {
            $this->assertContains($item['id'], $result);
        }
    }
}