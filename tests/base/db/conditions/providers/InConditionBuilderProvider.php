<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db\conditions\providers;

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
                ([[id]], [[name]]) IN ((:qp0, :qp1))
                SQL,
                [':qp0' => 1, ':qp1' => 'oy'],
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
                ([[id]], [[name]]) IN ((:qp0, :qp1), (:qp2, :qp3))
                SQL,
                [':qp0' => 1, ':qp1' => 'oy', ':qp2' => 2, ':qp3' => 'yo'],
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
}
