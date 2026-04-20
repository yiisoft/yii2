<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite\conditions\providers;

use yii\db\Expression;
use yiiunit\data\base\TraversableObject;

/**
 * Data provider for SQLite IN/NOT IN condition builder test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class InConditionBuilderProvider extends \yiiunit\base\db\conditions\providers\InConditionBuilderProvider
{
    public static function buildCondition(): array
    {
        return [
            ...parent::buildCondition(),
            'composite in with multiple rows' => [
                [
                    'in',
                    ['id', 'name'],
                    [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']],
                ],
                <<<SQL
                (([[id]] = :qp0 AND [[name]] = :qp1) OR ([[id]] = :qp2 AND [[name]] = :qp3))
                SQL,
                [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar'],
            ],
            'composite in with expression column' => [
                [
                    'in',
                    [new Expression('id'), 'name'],
                    [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']],
                ],
                <<<SQL
                (([[id]] = :qp0 AND [[name]] = :qp1) OR ([[id]] = :qp2 AND [[name]] = :qp3))
                SQL,
                [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar'],
            ],
            'composite not in with multiple rows' => [
                [
                    'not in',
                    ['id', 'name'],
                    [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']],
                ],
                <<<SQL
                (([[id]] != :qp0 OR [[name]] != :qp1) AND ([[id]] != :qp2 OR [[name]] != :qp3))
                SQL,
                [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar'],
            ],
            'composite in' => [
                [
                    'in',
                    ['id', 'name'],
                    [['id' => 1, 'name' => 'oy']],
                ],
                <<<SQL
                (([[id]] = :qp0 AND [[name]] = :qp1))
                SQL,
                [':qp0' => 1, ':qp1' => 'oy'],
            ],
            'composite in using array objects' => [
                [
                    'in',
                    new TraversableObject(['id', 'name']),
                    new TraversableObject([['id' => 1, 'name' => 'oy'], ['id' => 2, 'name' => 'yo']]),
                ],
                <<<SQL
                (([[id]] = :qp0 AND [[name]] = :qp1) OR ([[id]] = :qp2 AND [[name]] = :qp3))
                SQL,
                [':qp0' => 1, ':qp1' => 'oy', ':qp2' => 2, ':qp3' => 'yo'],
            ],
        ];
    }
}
