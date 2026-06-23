<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strrpos;
use function substr;

/**
 * Provides connection-free helpers for quoting MSSQL values and identifiers when building SQL.
 *
 * Unlike {@see \yii\db\Schema::quoteValue()}, the helpers never open a database connection, so they are safe to call
 * while {@see QueryBuilder} assembles dynamic SQL ahead of execution.
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

    /**
     * Returns the trailing single-part identifier of a bracket-quoted qualified name, keeping its brackets.
     *
     * Splits on the last `].[` boundary; a name without that boundary is returned unchanged.
     *
     * Usage example:
     * ```php
     * \yii\db\mssql\Quoter::extractSimpleIdentifier('[schema].[table]'); // [table]
     * \yii\db\mssql\Quoter::extractSimpleIdentifier('[table]');          // [table]
     * ```
     *
     * @param string $name Bracket-quoted qualified identifier.
     *
     * @return string Trailing single-part identifier with brackets preserved.
     */
    public static function extractSimpleIdentifier(string $name): string
    {
        $pos = strrpos($name, '].[');

        if ($pos !== false) {
            return substr($name, $pos + 2);
        }

        return $name;
    }

    /**
     * Returns whether an identifier is already wrapped in MSSQL square brackets (`[identifier]`).
     *
     * Canonical predicate for an already bracket-quoted identifier; the value starts with `[` and ends with `]`.
     *
     * Usage example:
     * ```php
     * \yii\db\mssql\Quoter::isIdentifierBracketQuoted('[user]'); // true
     * \yii\db\mssql\Quoter::isIdentifierBracketQuoted('user');   // false
     * ```
     *
     * @param string $identifier Identifier to inspect.
     *
     * @return bool Whether the identifier is already bracket-quoted.
     */
    public static function isIdentifierBracketQuoted(string $identifier): bool
    {
        return str_starts_with($identifier, '[') && str_ends_with($identifier, ']');
    }
}
