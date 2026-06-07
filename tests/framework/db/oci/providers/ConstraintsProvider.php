<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci\providers;

use yii\db\CheckConstraint;
use yii\db\Constraint;
use yiiunit\framework\db\AnyValue;

/**
 * Data provider for {@see \yiiunit\framework\db\oci\SchemaConstraintsTest} test cases.
 */
final class ConstraintsProvider extends \yiiunit\base\db\providers\ConstraintsProvider
{
    /**
     * @return array<string, array{string, string, Constraint|bool|array<array-key, mixed>|null}>
     */
    public static function constraints(): array
    {
        $result = parent::constraints();

        $result['1: check'][2][0]->expression = '"C_check" <> \'\'';
        $result['1: check'][2][] = new CheckConstraint(
            [
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_id'],
                'expression' => '"C_id" IS NOT NULL',
            ],
        );
        $result['1: check'][2][] = new CheckConstraint(
            [
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_not_null'],
                'expression' => '"C_not_null" IS NOT NULL',
            ],
        );
        $result['1: check'][2][] = new CheckConstraint(
            [
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_unique'],
                'expression' => '"C_unique" IS NOT NULL',
            ],
        );
        $result['1: check'][2][] = new CheckConstraint(
            [
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_default'],
                'expression' => '"C_default" IS NOT NULL',
            ],
        );
        $result['2: check'][2][] = new CheckConstraint(
            [
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_id_1'],
                'expression' => '"C_id_1" IS NOT NULL',
            ],
        );
        $result['2: check'][2][] = new CheckConstraint(
            [
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_id_2'],
                'expression' => '"C_id_2" IS NOT NULL',
            ],
        );
        $result['3: foreign key'][2][0]->foreignSchemaName = AnyValue::getInstance();
        $result['3: foreign key'][2][0]->onUpdate = null;
        $result['3: index'][2] = [];
        $result['3: check'][2][] = new CheckConstraint(
            [
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_fk_id_1'],
                'expression' => '"C_fk_id_1" IS NOT NULL',
            ],
        );
        $result['3: check'][2][] = new CheckConstraint(
            [
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_fk_id_2'],
                'expression' => '"C_fk_id_2" IS NOT NULL',
            ],
        );
        $result['3: check'][2][] = new CheckConstraint(
            [
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_id'],
                'expression' => '"C_id" IS NOT NULL',
            ],
        );
        $result['4: check'][2][] = new CheckConstraint(
            [
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_id'],
                'expression' => '"C_id" IS NOT NULL',
            ],
        );
        $result['4: check'][2][] = new CheckConstraint(
            [
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_col_2'],
                'expression' => '"C_col_2" IS NOT NULL',
            ],
        );

        return $result;
    }
}
