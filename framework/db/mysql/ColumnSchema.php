<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mysql;

use yii\db\Expression;
use yii\db\ExpressionInterface;
use yii\db\JsonExpression;

use function in_array;
use function is_string;
use function strtr;

/**
 * Represents the metadata of a column in a MySQL database table.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14.1
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * @var bool whether MySQL reports the column default as an expression (`DEFAULT_GENERATED`).
     *
     * @since 22.0
     */
    public $isDefaultExpression = false;

    /**
     * {@inheritdoc}
     */
    public function dbTypecast($value)
    {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof ExpressionInterface) {
            return $value;
        }

        if ($this->dbType === Schema::TYPE_JSON) {
            return new JsonExpression($value, $this->type);
        }

        return $this->typecast($value);
    }

    /**
     * {@inheritdoc}
     */
    public function phpTypecast($value)
    {
        if ($value === null) {
            return null;
        }

        if ($this->type === Schema::TYPE_JSON) {
            return json_decode($value, true);
        }

        return parent::phpTypecast($value);
    }

    /**
     * Converts a MySQL column default value to its PHP representation.
     *
     * Handles MySQL-specific default value formats:
     * - `null` to `null`.
     * - `CURRENT_TIMESTAMP` / `current_timestamp()` on temporal columns (`timestamp`, `datetime`, `date`, `time`) to an
     *   {@see Expression}, preserving any declared fractional-seconds precision such as `CURRENT_TIMESTAMP(3)`.
     * - expression defaults flagged through {@see $isDefaultExpression} to an {@see Expression}.
     * - `json` defaults without the flag to their decoded value when the string is valid JSON, or to an
     *   {@see Expression} otherwise — MariaDB reports expression-form defaults without metadata.
     * - `text` defaults without the flag to an {@see Expression}, preserving MariaDB's expression-form SQL.
     * - bit defaults (`b'...'`) when `$dbType` starts with `bit` to their integer value via `bindec()`.
     * - everything else delegates to {@see phpTypecast()}.
     *
     * Branch order is significant: MySQL also flags plain `CURRENT_TIMESTAMP` defaults as `DEFAULT_GENERATED`, so the
     * normalization above must win over the generic expression wrapping.
     *
     * @param mixed $value default value in the format reported by `SHOW FULL COLUMNS`.
     *
     * @return mixed converted value.
     *
     * @since 22.0
     */
    public function defaultPhpTypecast($value)
    {
        if ($value === null) {
            return null;
        }

        if (
            is_string($value)
            && in_array($this->type, ['timestamp', 'datetime', 'date', 'time'], true)
            && preg_match('/^current_timestamp(?:\(([0-9]*)\))?$/i', $value, $matches)
        ) {
            $precision = $matches[1] ?? '';

            return new Expression('CURRENT_TIMESTAMP' . ($precision !== '' ? "({$precision})" : ''));
        }

        if ($this->isDefaultExpression && is_string($value)) {
            return new Expression(strtr($value, ['\\\\' => '\\', "\\'" => "'"]));
        }

        if ($this->type === Schema::TYPE_JSON && is_string($value)) {
            $decoded = json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE
                ? $decoded
                : new Expression($value);
        }

        if ($this->type === Schema::TYPE_TEXT && is_string($value)) {
            return new Expression($value);
        }

        if (is_string($value) && strncasecmp($this->dbType, 'bit', 3) === 0) {
            return bindec(trim($value, "b'"));
        }

        return $this->phpTypecast($value);
    }
}
