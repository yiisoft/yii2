<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql\providers;

/**
 * Data provider for {@see \yiiunit\framework\db\pgsql\SchemaTest} and {@see \yiiunit\framework\db\pgsql\SchemaQuoteTest}
 * test cases.
 */
final class SchemaProvider extends \yiiunit\base\db\providers\SchemaProvider
{
    /**
     * @return list<array{int|float}>
     */
    public static function bigintValue(): array
    {
        return [
            [8_817_806_877],
            [3_797_444_208],
            [3_199_585_540],
            [1_389_831_585],
            [922_337_203_685_477_580],
            [9_223_372_036_854_775_807],
            [-9_223_372_036_854_775_808.0],
        ];
    }

    /**
     * Extends the shared cases with a name embedding a double quote (the driver's own quote character), passed through
     * unchanged.
     *
     * @return list<array{string, string}>
     */
    public static function quoteSimpleTableName(): array
    {
        return [
            ...parent::quoteSimpleTableName(),
            ['a"b', 'a"b'],
        ];
    }
}
