<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql\conditions\providers;

use yii\db\ArrayExpression;
use yii\db\conditions\InCondition;
use yii\db\Expression;
use yii\db\JsonExpression;
use yii\db\Query;

/**
 * Data provider for PostgreSQL IN/NOT IN condition builder test cases.
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
            'composite in with subquery' => [
                ['in', ['id', 'name'], (new Query())->select(['id', 'name'])->from('users')->where(['active' => 1])],
                <<<SQL
                ([[id]], [[name]]) IN (SELECT [[id]], [[name]] FROM [[users]] WHERE [[active]]=:qp0)
                SQL,
                [':qp0' => 1],
            ],
            'composite in with subquery and expression column' => [
                new InCondition(
                    [new Expression('id')],
                    'in',
                    (new Query())->select('id')->from('users')->where(['active' => 1]),
                ),
                <<<SQL
                ([[id]]) IN (SELECT [[id]] FROM [[users]] WHERE [[active]]=:qp0)
                SQL,
                [':qp0' => 1],
            ],
            'composite not in with subquery' => [
                ['not in', ['id', 'name'], (new Query())->select(['id', 'name'])->from('users')->where(['active' => 1])],
                <<<SQL
                ([[id]], [[name]]) NOT IN (SELECT [[id]], [[name]] FROM [[users]] WHERE [[active]]=:qp0)
                SQL,
                [':qp0' => 1],
            ],
            'composite in with json expression value' => [
                ['in', ['id', 'name'], [['id' => new JsonExpression(['x' => 1]), 'name' => 'foo']]],
                <<<SQL
                (([[id]] = :qp0 AND [[name]] = :qp1))
                SQL,
                [':qp0' => '{"x":1}', ':qp1' => 'foo'],
            ],
            'composite in with array expression value' => [
                ['in', ['id', 'name'], [['id' => new ArrayExpression([1], 'integer'), 'name' => 'foo']]],
                <<<SQL
                (([[id]] = ARRAY[:qp0]::integer[] AND [[name]] = :qp1))
                SQL,
                [':qp0' => 1, ':qp1' => 'foo'],
            ],
            'in condition object with json expression column and scalar' => [
                new InCondition(new JsonExpression(['a' => 1]), 'in', 1),
                <<<SQL
                :qp1=:qp0
                SQL,
                [':qp0' => 1, ':qp1' => '{"a":1}'],
            ],
            'in condition object with array expression column and scalar' => [
                new InCondition(new ArrayExpression([1, 2], 'integer'), 'in', 1),
                <<<SQL
                ARRAY[:qp1, :qp2]::integer[]=:qp0
                SQL,
                [':qp0' => 1, ':qp1' => 1, ':qp2' => 2],
            ],
        ];
    }
}
