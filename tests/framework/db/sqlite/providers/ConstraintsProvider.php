<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite\providers;

use yii\db\Constraint;
use yiiunit\framework\db\AnyValue;

/**
 * Data provider for {@see \yiiunit\framework\db\sqlite\SchemaConstraintsTest} test cases.
 */
final class ConstraintsProvider extends \yiiunit\base\db\providers\ConstraintsProvider
{
    /**
     * @return array<string, array{string, string, Constraint|bool|array<array-key, mixed>|null}>
     */
    public static function constraints(): array
    {
        $result = parent::constraints();

        $result['1: primary key'][2]->name = null;
        $result['1: check'][2][0]->columnNames = null;
        $result['1: check'][2][0]->expression = '"C_check" <> \'\'';
        $result['1: unique'][2][0]->name = AnyValue::getInstance();
        $result['1: index'][2][1]->name = AnyValue::getInstance();
        $result['2: primary key'][2]->name = null;
        $result['2: unique'][2][0]->name = AnyValue::getInstance();
        $result['2: index'][2][2]->name = AnyValue::getInstance();
        $result['3: foreign key'][2][0]->name = null;
        $result['3: index'][2] = [];
        $result['4: primary key'][2]->name = null;
        $result['4: unique'][2][0]->name = AnyValue::getInstance();
        $result['5: primary key'] = [
            'T_upsert', 'primaryKey',
            new Constraint(
                [
                    'name' => AnyValue::getInstance(),
                    'columnNames' => ['id'],
                ],
            ),
        ];

        return $result;
    }
}
