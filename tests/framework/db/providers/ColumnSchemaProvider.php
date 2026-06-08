<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\providers;

use PDO;
use yii\db\Schema;

/**
 * Data provider for {@see \yiiunit\framework\db\ColumnSchemaTest} test cases.
 */
final class ColumnSchemaProvider
{
    /**
     * @return array<string, array{mixed}>
     */
    public static function booleanFalsy(): array
    {
        return [
            'integer 0' => [0],
            'null byte' => ["\0"],
            'string 0' => ['0'],
            'string FALSE' => ['FALSE'],
            'string False' => ['False'],
            'string false' => ['false'],
        ];
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function booleanTruthy(): array
    {
        return [
            'integer 1' => [1],
            'string 1' => ['1'],
            'string TRUE' => ['TRUE'],
            'string yes' => ['yes'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function emptyStringPreserved(): array
    {
        return [
            'binary' => [Schema::TYPE_BINARY],
            'char' => [Schema::TYPE_CHAR],
            'string' => [Schema::TYPE_STRING],
            'text' => [Schema::TYPE_TEXT],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function emptyStringToNull(): array
    {
        return [
            'bigint' => [Schema::TYPE_BIGINT],
            'boolean' => [Schema::TYPE_BOOLEAN],
            'date' => [Schema::TYPE_DATE],
            'datetime' => [Schema::TYPE_DATETIME],
            'decimal' => [Schema::TYPE_DECIMAL],
            'float' => [Schema::TYPE_FLOAT],
            'integer' => [Schema::TYPE_INTEGER],
            'money' => [Schema::TYPE_MONEY],
            'smallint' => [Schema::TYPE_SMALLINT],
            'time' => [Schema::TYPE_TIME],
            'timestamp' => [Schema::TYPE_TIMESTAMP],
        ];
    }

    /**
     * @return array<string, array{mixed, int}>
     */
    public static function pdoValue(): array
    {
        return [
            'PARAM_BOOL' => [true, PDO::PARAM_BOOL],
            'PARAM_INT' => [42, PDO::PARAM_INT],
            'PARAM_LOB' => ['binary', PDO::PARAM_LOB],
            'PARAM_NULL' => [null, PDO::PARAM_NULL],
            'PARAM_STR' => ['hello', PDO::PARAM_STR],
        ];
    }
}
