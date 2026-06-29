<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\db\Expression;
use yii\db\ExpressionInterface;
use yii\db\PdoValue;

use function bin2hex;
use function in_array;
use function get_resource_type;
use function is_resource;
use function is_string;
use function preg_match;
use function sprintf;
use function str_replace;
use function str_starts_with;
use function strlen;
use function strtolower;
use function stream_get_contents;
use function unpack;

/**
 * Represents the metadata of a column in a Microsoft SQL Server database table.
 *
 * @since 2.0.23
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * @var bool whether this column is a computed column
     * @since 2.0.39
     */
    public $isComputed = false;

    /**
     * Renders a `rowversion`/`timestamp` value as an 8-byte binary literal (`0x...`) for WHERE comparisons, and
     * converts `varbinary` strings to `CONVERT(VARBINARY(MAX), 0x...)` expressions to avoid conversion errors.
     *
     * @see https://github.com/yiisoft/yii2/issues/9653
     * @see https://github.com/yiisoft/yii2/issues/12599
     */
    public function dbTypecast($value)
    {
        if ($this->isRowVersion() && $value !== null && !$value instanceof ExpressionInterface) {
            $hex = is_string($value) && strlen($value) === 8
                ? bin2hex($value) // raw 8-byte binary value.
                : sprintf('%016x', (int) $value); // integer representation from `phpTypecast()`.

            return new Expression("0x{$hex}");
        }

        if ($this->isVarbinary()) {
            if ($value instanceof PdoValue && $value->getType() === \PDO::PARAM_LOB) {
                $pdoValue = $value->getValue();

                if (is_string($pdoValue) || $pdoValue === null) {
                    $value = $pdoValue;
                }
            }

            if (is_string($value)) {
                return new Expression('CONVERT(VARBINARY(MAX), 0x' . bin2hex($value) . ')');
            }

            if ($value === null && $this->allowNull) {
                return new Expression('CAST(NULL AS VARBINARY(MAX))');
            }
        }

        return parent::dbTypecast($value);
    }

    /**
     * {@inheritdoc}
     *
     * Decodes a `rowversion`/`timestamp` token to its integer value, and reads `varbinary` streams into strings.
     */
    public function phpTypecast($value)
    {
        if ($value === null) {
            return null;
        }

        if ($this->isRowVersion()) {
            if (is_resource($value) && get_resource_type($value) === 'stream') {
                $value = stream_get_contents($value);
            }

            return is_string($value) && strlen($value) === 8
                ? unpack('J', $value)[1]
                : $value;
        }

        if ($this->isVarbinary() && is_resource($value) && get_resource_type($value) === 'stream') {
            return stream_get_contents($value);
        }

        return parent::phpTypecast($value);
    }

    /**
     * Converts a MSSQL column default value to its PHP representation.
     *
     * Handles MSSQL-specific default formats:
     * - `null` or `(NULL)` to `null`.
     * - `CURRENT_TIMESTAMP` on `timestamp` columns to `null` (server-managed value).
     * - `('value')` / `(N'value')` string wrappers to the unwrapped literal with escaped quotes resolved.
     * - `((number))` numeric wrapper to the unwrapped numeric string.
     * - expression defaults such as `(getdate())` or `(newid())` to `null` (server-computed, not a literal).
     *
     * @param mixed $value Default value in MSSQL `column_default` format.
     *
     * @return mixed Converted value.
     *
     * @since 2.0.24
     */
    public function defaultPhpTypecast($value)
    {
        if ($value === null || $value === '(NULL)') {
            return null;
        }

        if ($this->type === Schema::TYPE_TIMESTAMP && $value === 'CURRENT_TIMESTAMP') {
            return null;
        }

        if (is_string($value)) {
            // String defaults: ('value') or unicode (N'value'); unwrap and resolve escaped single quotes.
            if (preg_match("/^\(N?'(.*)'\)$/s", $value, $matches) === 1) {
                $value = str_replace("''", "'", $matches[1]);
            } elseif (preg_match('/^\(\((.+)\)\)$/s', $value, $matches) === 1) {
                // Numeric defaults: ((`0`)), ((`42`)), ((`3.14`)).
                $value = $matches[1];
            } else {
                // Expression defaults: (`getdate()`), (`newid()`); not representable as a PHP literal.
                return null;
            }
        }

        return parent::defaultPhpTypecast($value);
    }

    /**
     * Returns the SQL type declaration for this column inside an OUTPUT clause temp table.
     *
     * Preserves the reflected declaration for variable-length types (`varchar`, `nvarchar`, `varbinary`), keeps the
     * legacy `(MAX)` fallback for bare variable-length declarations, appends the declared size for fixed-length types
     * (`char`, `nchar`, `binary`), and maps `timestamp` to `varbinary(8)` or `binary(8)`.
     *
     * @return string SQL type declaration.
     */
    public function getOutputColumnDeclaration(): string
    {
        if ($this->dbType === Schema::TYPE_TIMESTAMP) {
            return $this->allowNull ? 'varbinary(8)' : 'binary(8)';
        }

        $dbType = $this->dbType;

        if (in_array($dbType, ['varchar', 'nvarchar', 'varbinary'], true)) {
            $dbType .= '(MAX)';
        } elseif (in_array($dbType, ['char', 'nchar', 'binary'], true)) {
            $dbType .= "($this->size)";
        }

        return $dbType;
    }

    /**
     * Returns whether this column is a SQL Server `rowversion` (legacy synonym `timestamp`) auto-versioning column.
     *
     * @see https://learn.microsoft.com/en-us/sql/t-sql/data-types/rowversion-transact-sql
     */
    public function isRowVersion(): bool
    {
        return $this->isType(Schema::TYPE_TIMESTAMP);
    }

    /**
     * Whether this column is an MSSQL `varbinary` column.
     */
    private function isVarbinary(): bool
    {
        return $this->isType(Schema::TYPE_BINARY) && str_starts_with(strtolower((string) $this->dbType), 'varbinary');
    }
}
