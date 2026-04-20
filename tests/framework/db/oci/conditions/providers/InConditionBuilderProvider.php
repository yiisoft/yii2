<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci\conditions\providers;

use yii\db\conditions\InCondition;
use yii\db\Expression;
use yii\db\Query;

/**
 * Data provider for Oracle IN/NOT IN condition builder test cases.
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
        ];
    }
}
