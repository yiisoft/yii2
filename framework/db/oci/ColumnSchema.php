<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\oci;

use yii\db\Expression;
use yii\db\PdoValue;

use function is_string;
use function preg_match;
use function str_replace;
use function stripos;
use function strlen;
use function substr;
use function trim;
use function uniqid;

/**
 * Represents the metadata of a column in an Oracle database table.
 *
 * Extends {@see \yii\db\ColumnSchema} with Oracle-specific handling: wraps `string` values for `BLOB` columns in
 * `TO_BLOB(UTL_RAW.CAST_TO_RAW())` expressions, and normalizes `DATA_DEFAULT` metadata (`CURRENT_TIMESTAMP`,
 * server-managed timestamps, quote-wrapped literals) into PHP values.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * {@inheritdoc}
     *
     * Wraps `string` values for Oracle `BLOB` columns in `TO_BLOB(UTL_RAW.CAST_TO_RAW(:placeholder))` expressions so
     * PDO does not bind them directly as LONG values.
     */
    public function dbTypecast($value)
    {
        if ($this->type === Schema::TYPE_BINARY && $this->dbType === 'BLOB') {
            if ($value instanceof PdoValue) {
                if ($value->getType() === \PDO::PARAM_LOB && is_string($value->getValue())) {
                    return $this->createBlobExpression($value->getValue());
                }

                return parent::dbTypecast($value);
            }

            if (is_string($value)) {
                return $this->createBlobExpression($value);
            }
        }

        return parent::dbTypecast($value);
    }

    /**
     * Converts an Oracle column default value to its PHP representation.
     *
     * Handles Oracle-specific default formats:
     * - `null`, empty/whitespace string, or `'NULL'` to `null`.
     * - `CURRENT_TIMESTAMP[(precision)]` on `timestamp` columns to {@see Expression}, preserving precision.
     * - server-managed timestamp defaults (`SYSTIMESTAMP`, `LOCALTIMESTAMP`, `TIMESTAMP 'literal'`,
     *   `to_timestamp(...)`) on `timestamp` columns to `null`.
     * - single-quote-wrapped string defaults (`'value'`) to the unwrapped literal, resolving doubled single quotes
     *   (`''` to `'`).
     * - everything else delegates to {@see \yii\db\ColumnSchema::defaultPhpTypecast()}.
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

        $value = trim((string) $value);

        if ($value === '' || $value === 'NULL') {
            return null;
        }

        // `CURRENT_TIMESTAMP[(precision)]` on timestamp columns -> Expression (consistency with MySQL driver).
        if (
            $this->type === Schema::TYPE_TIMESTAMP
            && preg_match('/^current_timestamp(?:\(([0-9]*)\))?$/i', $value, $matches) === 1
        ) {
            $precision = $matches[1] ?? '';

            return new Expression('CURRENT_TIMESTAMP' . ($precision !== '' ? "({$precision})" : ''));
        }

        // server-managed timestamp defaults: `SYSTIMESTAMP`, `LOCALTIMESTAMP`, `TIMESTAMP` 'literal', to_timestamp(...).
        if ($this->type === Schema::TYPE_TIMESTAMP && stripos($value, 'timestamp') !== false) {
            return null;
        }

        // single-quote-wrapped string defaults: 'value' -> value, resolving doubled quotes ('' -> ').
        if (strlen($value) > 2 && $value[0] === "'" && $value[-1] === "'") {
            $value = str_replace("''", "'", substr($value, 1, -1));
        }

        return parent::defaultPhpTypecast($value);
    }

    /**
     * Creates an Oracle BLOB expression for a PHP `string` value.
     *
     * @param string $value Value to bind.
     *
     * @return Expression Oracle BLOB expression.
     */
    private function createBlobExpression(string $value): Expression
    {
        $placeholder = 'qp' . str_replace('.', '', uniqid('', true));

        return new Expression(
            "TO_BLOB(UTL_RAW.CAST_TO_RAW(:{$placeholder}))",
            [":{$placeholder}" => $value],
        );
    }
}
