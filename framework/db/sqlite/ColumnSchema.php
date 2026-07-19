<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\sqlite;

use yii\db\Expression;

use function is_string;
use function preg_match;
use function str_replace;
use function strcasecmp;
use function strtoupper;
use function trim;

/**
 * Represents the metadata of a column in a SQLite database table.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * Converts a SQLite column default value to its PHP representation.
     *
     * Handles SQLite-specific default formats reported by `PRAGMA table_info`:
     * - `null`, empty strings, and bare or parenthesized `NULL` to `null`.
     * - `CURRENT_DATE`, `CURRENT_TIME`, and `CURRENT_TIMESTAMP` to {@see Expression}.
     * - single, double, backtick, or bracket-quoted string literals to their unquoted PHP value.
     * - signed decimal and scientific numeric literals through {@see \yii\db\ColumnSchema::phpTypecast()}.
     * - `TRUE` and `FALSE` through {@see \yii\db\ColumnSchema::phpTypecast()} as `1` and `0` respectively.
     * - bareword defaults to their literal text value, matching SQLite's non-parenthesized `DEFAULT word` behavior.
     * - everything else to an executable {@see Expression}, including functions, operators, BLOB literals, and
     *   hexadecimal integers whose meaning is only preserved when parsed as SQL text.
     *
     * Branch order is significant: `CURRENT_*` and `TRUE`/`FALSE` must precede the bareword fallthrough, or they would
     * reflect as string literals. `PRAGMA table_info` strips one outer parenthesis level; paren-tolerant branches exist
     * only for literals that are also legal inside a parenthesized default expression.
     *
     * @link https://www.sqlite.org/lang_createtable.html#the_default_clause
     *
     * @param mixed $value Default value in SQLite `dflt_value` format.
     *
     * @return mixed Converted value.
     *
     * @since 22.0
     */
    public function defaultPhpTypecast($value)
    {
        if (is_string($value)) {
            $value = trim($value);

            if ($value === '' || preg_match('/^\(*\s*NULL\s*\)*$/i', $value) === 1) {
                return null;
            }

            if (preg_match('/^\(*\s*(CURRENT_DATE|CURRENT_TIME|CURRENT_TIMESTAMP)\s*\)*$/i', $value, $matches) === 1) {
                return new Expression(strtoupper($matches[1]));
            }

            if (preg_match("/^\(*\s*'((?:''|[^'])*)'\s*\)*$/", $value, $matches) === 1) {
                $value = str_replace("''", "'", $matches[1]);
            } elseif (preg_match('/^\(*\s*"((?:""|[^"])*)"\s*\)*$/', $value, $matches) === 1) {
                $value = str_replace('""', '"', $matches[1]);
            } elseif (preg_match('/^`((?:``|[^`])*)`$/', $value, $matches) === 1) {
                $value = str_replace('``', '`', $matches[1]);
            } elseif (preg_match('/^\[([^]]*)]$/', $value, $matches) === 1) {
                $value = $matches[1];
            } elseif (
                preg_match(
                    '/^\(*\s*([+-]?(?:\d+(?:\.\d*)?|\.\d+)(?:e[+-]?\d+)?)\s*\)*$/i',
                    $value,
                    $matches,
                ) === 1
            ) {
                $value = $matches[1];
            } elseif (preg_match('/^\(*\s*(TRUE|FALSE)\s*\)*$/i', $value, $matches) === 1) {
                $value = strcasecmp($matches[1], 'TRUE') === 0 ? 1 : 0;
            } elseif (preg_match('/^[\p{L}_$][\p{L}\p{N}_$]*$/u', $value) !== 1) {
                return new Expression($value);
            }
        }

        return parent::defaultPhpTypecast($value);
    }
}
