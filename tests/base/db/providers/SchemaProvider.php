<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db\providers;

use PDO;

/**
 * Data provider for {@see \yiiunit\base\db\BaseSchema} test cases.
 */
class SchemaProvider
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function getTableSchema(): array
    {
        return [
            'plain name' => ['profile', 'profile'],
            'prefix placeholder' => ['{{%profile}}', 'profile'],
        ];
    }

    /**
     * @return array<array{array<int, bool>}>
     */
    public static function pdoAttributes(): array
    {
        return [
            [[PDO::ATTR_EMULATE_PREPARES => true]],
            [[PDO::ATTR_EMULATE_PREPARES => false]],
        ];
    }
}
