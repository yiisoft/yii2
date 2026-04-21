<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db\conditions\providers;

use Generator;
use yii\db\conditions\InCondition;
use yii\db\Expression;
use yii\db\Query;
use yiiunit\data\base\TraversableObject;

/**
 * Data provider for IN/NOT IN condition builder test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
class InConditionBuilderProvider
{
    public static function buildCondition(): array
    {
        return [
            'in with value list and subquery value' => [
                ['in', 'id', [1, 2, (new Query())->select('three')->from('digits')]],
                <<<SQL
                [[id]] IN (:qp0, :qp1, (SELECT [[three]] FROM [[digits]]))
                SQL,
                [':qp0' => 1, ':qp1' => 2],
            ],
            'not in with value list' => [
                ['not in', 'id', [1, 2, 3]],
                <<<SQL
                [[id]] NOT IN (:qp0, :qp1, :qp2)
                SQL,
                [':qp0' => 1, ':qp1' => 2, ':qp2' => 3],
            ],
            'in with subquery' => [
                ['in', 'id', (new Query())->select('id')->from('users')->where(['active' => 1])],
                <<<SQL
                [[id]] IN (SELECT [[id]] FROM [[users]] WHERE [[active]]=:qp0)
                SQL,
                [':qp0' => 1],
            ],
            'not in with subquery' => [
                ['not in', 'id', (new Query())->select('id')->from('users')->where(['active' => 1])],
                <<<SQL
                [[id]] NOT IN (SELECT [[id]] FROM [[users]] WHERE [[active]]=:qp0)
                SQL,
                [':qp0' => 1],
            ],
            'in with scalar' => [
                ['in', 'id', 1],
                <<<SQL
                [[id]]=:qp0
                SQL,
                [':qp0' => 1],
            ],
            'in with single-item array' => [
                ['in', 'id', [1]],
                <<<SQL
                [[id]]=:qp0
                SQL,
                [':qp0' => 1],
            ],
            'in with single-item traversable' => [
                ['in', 'id', new TraversableObject([1])],
                <<<SQL
                [[id]]=:qp0
                SQL,
                [':qp0' => 1],
            ],
            'composite in' => [
                ['in', ['id', 'name'], [['id' => 1, 'name' => 'oy']]],
                <<<SQL
                (([[id]] = :qp0 AND [[name]] = :qp1))
                SQL,
                [':qp0' => 1, ':qp1' => 'oy'],
            ],
            'composite in with multiple rows' => [
                ['in', ['id', 'name'], [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']]],
                <<<SQL
                (([[id]] = :qp0 AND [[name]] = :qp1) OR ([[id]] = :qp2 AND [[name]] = :qp3))
                SQL,
                [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar'],
            ],
            'composite in with expression column' => [
                ['in', [new Expression('id'), 'name'], [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']]],
                <<<SQL
                (([[id]] = :qp0 AND [[name]] = :qp1) OR ([[id]] = :qp2 AND [[name]] = :qp3))
                SQL,
                [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar'],
            ],
            'composite in with expression value' => [
                ['in', ['id', 'name'], [['id' => new Expression('42'), 'name' => 'foo']]],
                <<<SQL
                (([[id]] = 42 AND [[name]] = :qp0))
                SQL,
                [':qp0' => 'foo'],
            ],
            'composite in with null' => [
                ['in', ['id', 'name'], [['id' => 1, 'name' => null]]],
                <<<SQL
                (([[id]] = :qp0 AND [[name]] IS NULL))
                SQL,
                [':qp0' => 1],
            ],
            'composite not in' => [
                ['not in', ['id', 'name'], [['id' => 1, 'name' => 'oy']]],
                <<<SQL
                (([[id]] <> :qp0 OR [[name]] <> :qp1))
                SQL,
                [':qp0' => 1, ':qp1' => 'oy'],
            ],
            'composite not in with multiple rows' => [
                ['not in', ['id', 'name'], [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']]],
                <<<SQL
                (([[id]] <> :qp0 OR [[name]] <> :qp1) AND ([[id]] <> :qp2 OR [[name]] <> :qp3))
                SQL,
                [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar'],
            ],
            'composite not in with expression column' => [
                ['not in', [new Expression('id'), 'name'], [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']]],
                <<<SQL
                (([[id]] <> :qp0 OR [[name]] <> :qp1) AND ([[id]] <> :qp2 OR [[name]] <> :qp3))
                SQL,
                [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar'],
            ],
            'composite not in with null' => [
                ['not in', ['id', 'name'], [['id' => 1, 'name' => null]]],
                <<<SQL
                (([[id]] <> :qp0 OR [[name]] IS NOT NULL))
                SQL,
                [':qp0' => 1],
            ],
            'composite in (just one column)' => [
                ['in', ['id'], [['id' => 1, 'name' => 'Name1'], ['id' => 2, 'name' => 'Name2']]],
                <<<SQL
                [[id]] IN (:qp0, :qp1)
                SQL,
                [':qp0' => 1, ':qp1' => 2],
            ],
            'composite in using array objects (just one column)' => [
                ['in', new TraversableObject(['id']), new TraversableObject([
                    ['id' => 1, 'name' => 'Name1'],
                    ['id' => 2, 'name' => 'Name2'],
                ])],
                <<<SQL
                [[id]] IN (:qp0, :qp1)
                SQL,
                [':qp0' => 1, ':qp1' => 2],
            ],
            'hash condition with traversable values' => [
                ['id' => new TraversableObject([1, 2])],
                <<<SQL
                [[id]] IN (:qp0, :qp1)
                SQL,
                [':qp0' => 1, ':qp1' => 2],
            ],
            'in with traversable values' => [
                ['in', 'id', new TraversableObject([1, 2, 3])],
                <<<SQL
                [[id]] IN (:qp0, :qp1, :qp2)
                SQL,
                [':qp0' => 1, ':qp1' => 2, ':qp2' => 3],
            ],
            'in with traversable values including null' => [
                ['in', 'id', new TraversableObject([1, null])],
                <<<SQL
                [[id]]=:qp0 OR [[id]] IS NULL
                SQL,
                [':qp0' => 1],
            ],
            'in with traversable values including multiple and null' => [
                ['in', 'id', new TraversableObject([1, 2, null])],
                <<<SQL
                [[id]] IN (:qp0, :qp1) OR [[id]] IS NULL
                SQL,
                [':qp0' => 1, ':qp1' => 2],
            ],
            'not in with traversable values including null' => [
                ['not in', 'id', new TraversableObject([1, null])],
                <<<SQL
                [[id]]<>:qp0 AND [[id]] IS NOT NULL
                SQL,
                [':qp0' => 1],
            ],
            'not in with traversable values including multiple and null' => [
                ['not in', 'id', new TraversableObject([1, 2, null])],
                <<<SQL
                [[id]] NOT IN (:qp0, :qp1) AND [[id]] IS NOT NULL
                SQL,
                [':qp0' => 1, ':qp1' => 2],
            ],
            'in with traversable null only' => [
                ['in', 'id', new TraversableObject([null])],
                <<<SQL
                [[id]] IS NULL
                SQL,
                [],
            ],
            'not in with traversable null only' => [
                ['not in', 'id', new TraversableObject([null])],
                <<<SQL
                [[id]] IS NOT NULL
                SQL,
                [],
            ],
            'not in expression with traversable null only' => [
                ['not in', new Expression('id'), new TraversableObject([null])],
                <<<SQL
                [[id]] IS NOT NULL
                SQL,
                [],
            ],
            'composite in using array objects' => [
                ['in', new TraversableObject(['id', 'name']), new TraversableObject([
                    ['id' => 1, 'name' => 'oy'],
                    ['id' => 2, 'name' => 'yo'],
                ])],
                <<<SQL
                (([[id]] = :qp0 AND [[name]] = :qp1) OR ([[id]] = :qp2 AND [[name]] = :qp3))
                SQL,
                [':qp0' => 1, ':qp1' => 'oy', ':qp2' => 2, ':qp3' => 'yo'],
            ],
            'composite in with generator columns' => [
                new InCondition(
                    self::generatorFrom(['id', 'name']),
                    'in',
                    [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']],
                ),
                <<<SQL
                (([[id]] = :qp0 AND [[name]] = :qp1) OR ([[id]] = :qp2 AND [[name]] = :qp3))
                SQL,
                [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar'],
            ],
            'in with generator values' => [
                new InCondition('id', 'in', self::generatorFrom([1, 2, 3])),
                <<<SQL
                [[id]] IN (:qp0, :qp1, :qp2)
                SQL,
                [':qp0' => 1, ':qp1' => 2, ':qp2' => 3],
            ],
            'in condition object with scalar' => [
                new InCondition('id', 'in', 1),
                <<<SQL
                [[id]]=:qp0
                SQL,
                [':qp0' => 1],
            ],
            'in condition object with expression column and scalar' => [
                new InCondition(new Expression('id'), 'in', 1),
                <<<SQL
                [[id]]=:qp0
                SQL,
                [':qp0' => 1],
            ],
            'in condition object with query column and scalar' => [
                new InCondition((new Query())->select('id')->from('users'), 'in', 1),
                <<<SQL
                (SELECT [[id]] FROM [[users]])=:qp0
                SQL,
                [':qp0' => 1],
            ],
            'in condition object with single-item array' => [
                new InCondition('id', 'in', [1]),
                <<<SQL
                [[id]]=:qp0
                SQL,
                [':qp0' => 1],
            ],
            'not in condition object with scalar' => [
                new InCondition('id', 'not in', 1),
                <<<SQL
                [[id]]<>:qp0
                SQL,
                [':qp0' => 1],
            ],
            'not in condition object with single-item array' => [
                new InCondition('id', 'not in', [1]),
                <<<SQL
                [[id]]<>:qp0
                SQL,
                [':qp0' => 1],
            ],
            'in condition object with two-item array' => [
                new InCondition('id', 'in', [1, 2]),
                <<<SQL
                [[id]] IN (:qp0, :qp1)
                SQL,
                [':qp0' => 1, ':qp1' => 2],
            ],
            'not in condition object with two-item array' => [
                new InCondition('id', 'not in', [1, 2]),
                <<<SQL
                [[id]] NOT IN (:qp0, :qp1)
                SQL,
                [':qp0' => 1, ':qp1' => 2],
            ],
        ];
    }

    private static function generatorFrom(array $items): Generator
    {
        yield from $items;
    }
}
