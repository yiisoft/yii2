<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\providers;

/**
 * Data provider for {@see \yiiunit\framework\db\mssql\QueryBuilderUnionTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class QueryBuilderUnionProvider
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function rawUnionLimitZero(): array
    {
        return [
            'DISTINCT preserved' => [
                'SELECT DISTINCT id FROM table2',
                <<<SQL
                (SELECT TOP (0) [id] FROM [table1])
                UNION ( SELECT DISTINCT TOP (0) id FROM table2 )
                SQL,
            ],
            'existing TOP replaced' => [
                'SELECT TOP (5) id FROM table2',
                <<<SQL
                (SELECT TOP (0) [id] FROM [table1])
                UNION ( SELECT TOP (0) id FROM table2 )
                SQL,
            ],
            'plain SELECT' => [
                'SELECT id FROM table2',
                <<<SQL
                (SELECT TOP (0) [id] FROM [table1])
                UNION ( SELECT TOP (0) id FROM table2 )
                SQL,
            ],
        ];
    }
}
