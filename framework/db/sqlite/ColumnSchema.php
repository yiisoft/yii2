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
use function str_replace;
use function strcasecmp;
use function strlen;
use function substr;
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
     * - `null`, empty string, or the `NULL` literal (any case) to `null`.
     * - `CURRENT_TIMESTAMP` (any case) on `timestamp` columns to {@see Expression}.
     * - single/double-quote-wrapped string defaults (`'value'`, `"value"`) to the unwrapped literal, resolving doubled
     *   quotes (`''` to `'`, `""` to `"`).
     * - non-string values and everything else delegate to {@see \yii\db\ColumnSchema::defaultPhpTypecast()}.
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

            if ($value === '' || strcasecmp($value, 'NULL') === 0) {
                return null;
            }

            // `CURRENT_TIMESTAMP` is a case-independent keyword (consistency with all driver).
            if ($this->type === Schema::TYPE_TIMESTAMP && strcasecmp($value, 'CURRENT_TIMESTAMP') === 0) {
                return new Expression('CURRENT_TIMESTAMP');
            }

            // quote-wrapped defaults: 'value' / "value" -> unwrapped, resolving doubled quotes ('' -> ', "" -> ").
            if (strlen($value) > 1 && ($value[0] === "'" || $value[0] === '"') && $value[-1] === $value[0]) {
                $quote = $value[0];
                $value = str_replace($quote . $quote, $quote, substr($value, 1, -1));
            }
        }

        return parent::defaultPhpTypecast($value);
    }
}
