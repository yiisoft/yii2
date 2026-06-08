<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use yii\db\ArrayExpression;
use yii\db\Expression;
use yii\db\ExpressionInterface;
use yii\db\JsonExpression;

use function bindec;
use function get_resource_type;
use function in_array;
use function is_array;
use function is_bool;
use function is_resource;
use function json_decode;
use function preg_match;
use function stream_get_contents;
use function strpos;
use function strtolower;
use function strtoupper;

/**
 * Represents the metadata of a column in a PostgreSQL database table.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * @var int the dimension of array. Defaults to 0, means this column is not an array.
     */
    public $dimension = 0;
    /**
     * @var string|null name of associated sequence if column is auto-incremental. `null` if no sequence.
     * @since 2.0.29
     */
    public $sequenceName = null;

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

        if ($this->dimension > 0) {
            return new ArrayExpression($value, $this->dbType, $this->dimension);
        }
        if (in_array($this->dbType, [Schema::TYPE_JSON, Schema::TYPE_JSONB], true)) {
            return new JsonExpression($value, $this->dbType);
        }

        return $this->typecast($value);
    }

    /**
     * {@inheritdoc}
     */
    public function phpTypecast($value)
    {
        if ($this->dimension > 0) {
            if (!is_array($value)) {
                $value = $this->getArrayParser()->parse($value);
            }
            if (is_array($value)) {
                array_walk_recursive($value, function (&$val, $key) {
                    $val = $this->phpTypecastValue($val);
                });
            } elseif ($value === null) {
                return null;
            }

            return $value;
        }

        return $this->phpTypecastValue($value);
    }

    /**
     * Casts $value after retrieving from the DBMS to PHP representation.
     *
     * @param string|null $value
     * @return bool|mixed|null
     */
    protected function phpTypecastValue($value)
    {
        if ($value === null) {
            return null;
        }

        // `bytea` columns are returned as streams by the PDO PGSQL driver; read them into a string for consumers such
        // as DbCache.
        if ($this->type === Schema::TYPE_BINARY && is_resource($value) && get_resource_type($value) === 'stream') {
            return stream_get_contents($value);
        }

        switch ($this->type) {
            case Schema::TYPE_BOOLEAN:
                if (is_bool($value)) {
                    return $value;
                }

                switch (strtolower($value)) {
                    case 't':
                    case 'true':
                        return true;
                    case 'f':
                    case 'false':
                        return false;
                }
                return (bool) $value;
            case Schema::TYPE_JSON:
                return json_decode($value, true);
        }

        return parent::phpTypecast($value);
    }

    /**
     * Converts a PostgreSQL column default value to its PHP representation.
     *
     * Handles PostgreSQL-specific default value formats:
     * - `null` to `null`.
     * - Temporal columns (`timestamp`, `date`, `time`) defaulting to `NOW()`, `CURRENT_TIMESTAMP`, `CURRENT_DATE`,
     *   `CURRENT_TIME`, or any function-call expression (containing `(`) to an {@see Expression}.
     * - Binary bit literals (`B'...'::`) to their integer value via `bindec()`.
     * - Quoted bit literals (`'...'::"bit"`) to their integer value via `bindec()`.
     * - Cast notation (`'value'::type`) to the unwrapped literal, delegated to {@see phpTypecast()}.
     * - `boolean` columns: `'true'` to `true`, anything else to `false`.
     * - Parenthesized values (`(value)::type`) to the unwrapped literal, with `NULL` detection, delegated to
     *   {@see phpTypecast()}.
     *
     * @param mixed $value Raw default value in PostgreSQL schema metadata format.
     *
     * @return mixed Converted PHP value.
     *
     * @since 22.0
     */
    public function defaultPhpTypecast($value)
    {
        if ($value === null) {
            return null;
        }

        if (
            in_array($this->type, [Schema::TYPE_TIMESTAMP, Schema::TYPE_DATE, Schema::TYPE_TIME], true)
            && (
                in_array(strtoupper($value), ['NOW()', 'CURRENT_TIMESTAMP', 'CURRENT_DATE', 'CURRENT_TIME'], true)
                || strpos($value, '(') !== false
            )
        ) {
            return new Expression($value);
        }

        if (preg_match("/^B'(.*?)'::/", $value, $matches)) {
            return bindec($matches[1]);
        }

        if (preg_match("/^'(\d+)'::\"bit\"$/", $value, $matches)) {
            return bindec($matches[1]);
        }

        if (preg_match("/^'(.*?)'::/", $value, $matches)) {
            return $this->phpTypecast($matches[1]);
        }

        if ($this->type === Schema::TYPE_BOOLEAN) {
            return $value === 'true';
        }

        // matches bare values, parenthesized values, and cast notation.
        preg_match('/^(\()?(.*?)(?(1)\))(?:::.+)?$/', $value, $matches);

        if ($matches[2] === 'NULL') {
            return null;
        }

        return $this->phpTypecast($matches[2]);
    }

    /**
     * Creates instance of ArrayParser
     *
     * @return ArrayParser
     */
    protected function getArrayParser()
    {
        static $parser = null;

        if ($parser === null) {
            $parser = new ArrayParser();
        }

        return $parser;
    }
}
