<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\oci;

use PDO;
use yii\db\Expression;
use yii\db\PdoValue;

use function is_resource;
use function is_string;
use function preg_match;
use function str_replace;
use function trim;

/**
 * Represents the metadata of a column in an Oracle database table.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * {@inheritdoc}
     *
     * Converts strings and streams for Oracle `BLOB` columns to locator-bound values.
     */
    public function dbTypecast($value)
    {
        if ($this->type === Schema::TYPE_BINARY && $this->dbType === 'BLOB') {
            if ($value instanceof PdoValue) {
                $inner = $value->getValue();

                if ($value->getType() === PDO::PARAM_LOB && (is_string($inner) || is_resource($inner))) {
                    return new LobValue($this->name, $inner);
                }

                return parent::dbTypecast($value);
            }

            if (is_string($value) || is_resource($value)) {
                return new LobValue($this->name, $value);
            }
        }

        return parent::dbTypecast($value);
    }

    /**
     * Converts an Oracle column default value to its PHP representation.
     *
     * Handles Oracle-specific default formats:
     * - `null`, empty/whitespace strings, and bare or parenthesized `NULL` to `null`.
     * - regular and national string literals (`'value'` / `N'value'`) to their unquoted value, resolving doubled
     *   single quotes (`''` to `'`). Oracle treats an empty string literal as `null`.
     * - signed, decimal, scientific, `BINARY_FLOAT`, and `BINARY_DOUBLE` numeric literals through
     *   {@see \yii\db\ColumnSchema::phpTypecast()}.
     * - everything else to an executable {@see Expression}, including datetime literals, function calls, operator
     *   expressions, alternative-quoted strings, and sequence `NEXTVAL` defaults.
     *
     * @param mixed $value Default value in Oracle `DATA_DEFAULT` format.
     *
     * @return mixed Converted value.
     *
     * @since 22.0
     */
    public function defaultPhpTypecast($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);

            if ($value === '' || preg_match('/^(?:NULL|\(NULL\))$/i', $value) === 1) {
                return null;
            }

            if (preg_match("/^N?'((?:''|[^'])*)'$/is", $value, $matches) === 1) {
                $value = str_replace("''", "'", $matches[1]);

                if ($value === '') {
                    return null;
                }
            } elseif (preg_match('/^([+-]?(?:\d+(?:\.\d*)?|\.\d+)(?:e[+-]?\d+)?)[fd]?$/i', $value, $matches) === 1) {
                $value = $matches[1];
            } else {
                return new Expression($value);
            }
        }

        return parent::defaultPhpTypecast($value);
    }
}
