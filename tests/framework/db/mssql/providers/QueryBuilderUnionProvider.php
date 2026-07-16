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
            'ALL preserved' => [
                'SELECT ALL id FROM table2',
                <<<SQL
                (SELECT TOP (0) [id] FROM [table1])
                UNION ( SELECT ALL TOP (0) id FROM table2 )
                SQL,
            ],
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
            'legacy numeric TOP replaced' => [
                'SELECT TOP 10 id FROM table2',
                <<<SQL
                (SELECT TOP (0) [id] FROM [table1])
                UNION ( SELECT TOP (0) id FROM table2 )
                SQL,
            ],
            'nested parentheses in TOP replaced' => [
                'SELECT TOP ((5)) id FROM table2',
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
            'TOP PERCENT preserved' => [
                'SELECT DISTINCT TOP ((5)) PERCENT id FROM table2',
                <<<SQL
                (SELECT TOP (0) [id] FROM [table1])
                UNION ( SELECT DISTINCT TOP (0) PERCENT id FROM table2 )
                SQL,
            ],
            'TOP with expression replaced' => [
                'SELECT TOP (ABS(5)) id FROM table2',
                <<<SQL
                (SELECT TOP (0) [id] FROM [table1])
                UNION ( SELECT TOP (0) id FROM table2 )
                SQL,
            ],
        ];
    }
}
