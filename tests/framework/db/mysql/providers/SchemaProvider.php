<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql\providers;

/**
 * Data provider for {@see \yiiunit\framework\db\mysql\SchemaQuoteTest} test cases.
 */
final class SchemaProvider extends \yiiunit\base\db\providers\SchemaProvider
{
    /**
     * @return list<array{string, string}>
     */
    public static function quoteValue(): array
    {
        return [
            ['string', "'string'"],
            ["It's interesting", "'It\\'s interesting'"],
        ];
    }

    /**
     * @return list<array{string, string}>
     */
    public static function quoteSimpleTableName(): array
    {
        return [
            ...parent::quoteSimpleTableName(),
            ['a`b', 'a`b'],
        ];
    }
}
