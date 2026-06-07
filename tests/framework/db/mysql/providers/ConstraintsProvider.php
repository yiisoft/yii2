<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql\providers;

use yii\db\Constraint;
use yiiunit\framework\db\AnyCaseValue;

/**
 * Data provider for {@see \yiiunit\framework\db\mysql\SchemaConstraintsTest} test cases.
 */
final class ConstraintsProvider extends \yiiunit\base\db\providers\ConstraintsProvider
{
    /**
     * @return array<string, array{string, string, Constraint|bool|array<array-key, mixed>|null}>
     */
    public static function constraints(): array
    {
        $result = parent::constraints();

        $result['1: check'][2][0]->columnNames = null;
        $result['1: check'][2][0]->expression = "`C_check` <> ''";
        $result['2: primary key'][2]->name = null;
        $result['3: foreign key'][2][0]->foreignTableName = new AnyCaseValue('T_constraints_2');

        return $result;
    }

    public static function prepareConstraintsExpected(
        bool $isMariaDb,
        string $tableName,
        string $type,
        mixed $expected,
    ): mixed {
        if ($isMariaDb || $type !== 'checks') {
            return $expected;
        }

        if ($tableName === 'T_constraints_1') {
            $expected[0]->expression = "(`C_check` <> _utf8mb4\\'\\')";
        }

        return $expected;
    }
}
