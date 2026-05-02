<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\type\providers;

/**
 * Data provider for {@see \yiiunit\framework\db\mssql\type\VarbinaryTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class VarbinaryProvider
{
    public static function varbinaryStringValue(): array
    {
        return [
            'ascii' => ['hello'],
            'binary nulls' => ["\x00\x01\x02"],
            'empty string' => [''],
            'multibyte' => ['héllo'],
            'serialized payload' => ['a:1:{s:13:"template";s:1:"1";}'],
        ];
    }
}
