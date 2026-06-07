<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite\providers;

/**
 * Data provider for {@see \yiiunit\framework\db\sqlite\SchemaTest} test cases.
 */
final class SchemaProvider extends \yiiunit\base\db\providers\SchemaProvider
{
    /**
     * @return list<array{string, string}>
     */
    public static function quoteTableName(): array
    {
        return [
            ['`test`', '`test`'],
            ['`test`.`test`', '`test`.`test`'],
            ['test', '`test`'],
            ['test.`test`.test', '`test`.`test`.`test`'],
            ['test.test', '`test`.`test`'],
            ['test.test.test', '`test`.`test`.`test`'],
        ];
    }
}
