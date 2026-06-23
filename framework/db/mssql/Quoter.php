<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use function str_replace;

/**
 * Provides connection-free helpers for embedding values as Transact-SQL string literals when building MSSQL SQL.
 *
 * Unlike {@see \yii\db\Schema::quoteValue()}, the helpers never open a database connection and never wrap the value in
 * quotes, so they are safe to call while {@see QueryBuilder} assembles dynamic SQL ahead of execution.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
class Quoter
{
    /**
     * Escapes single quotes in a value for embedding as a Transact-SQL string literal.
     *
     * Doubles every single quote (`'` becomes `''`) without adding the surrounding quote characters, so the result can
     * be placed inside `'...'` or `N'...'`.
     *
     * Usage example:
     * ```php
     * $literal = \yii\db\mssql\Quoter::escapeLiteralValue("O'Brien"); // O''Brien
     * $sql = "DECLARE @name SYSNAME = N'{$literal}'";
     * ```
     *
     * @param string $value Value to escape.
     *
     * @return string Value with single quotes doubled, ready to embed inside a quoted literal.
     */
    public static function escapeLiteralValue(string $value): string
    {
        return str_replace("'", "''", $value);
    }
}
