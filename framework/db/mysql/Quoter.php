<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mysql;

use function str_replace;

/**
 * Provides connection-free helpers for quoting MySQL values when building SQL.
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
     * Escapes the special characters in a value for embedding as a MySQL string literal.
     *
     * Backslash-escapes the MySQL special characters without adding the surrounding quote characters, so the result
     * can be placed inside `'...'`.
     *
     * Usage example:
     * ```php
     * $literal = \yii\db\mysql\Quoter::escapeLiteralValue("O'Brien"); // O\'Brien
     * $sql = "ALTER TABLE `profile` COMMENT '{$literal}'";
     * ```
     *
     * @param string $value Value to escape.
     *
     * @return string Value with MySQL special characters escaped, ready to embed inside a quoted literal.
     */
    public static function escapeLiteralValue(string $value): string
    {
        return str_replace(
            ['\\', "\x00", "\n", "\r", "'", '"', "\x1a"],
            ['\\\\', '\\0', '\\n', '\\r', "\'", '\"', '\\Z'],
            $value,
        );
    }
}
