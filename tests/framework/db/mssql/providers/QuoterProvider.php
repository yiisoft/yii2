<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\providers;

/**
 * Data provider for {@see \yiiunit\framework\db\mssql\QuoterTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class QuoterProvider
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function escapeLiteralValue(): array
    {
        return [
            'already doubled quotes' => ["''", "''''"],
            'empty string' => ['', ''],
            'multiple single quotes' => ["a'b'c", "a''b''c"],
            'no single quote' => ['plain value', 'plain value'],
            'single quote' => ["O'Brien", "O''Brien"],
            'unicode content preserved' => ["héllo' wörld", "héllo'' wörld"],
        ];
    }
}
