<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\BaseObject;
use yii\helpers\StringHelper;

/**
 * ColumnSchema class describes the metadata of a column in a database table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ColumnSchema extends BaseObject
{
    /**
     * @var string name of this column (without quotes).
     */
    public $name = '';
    /**
     * @var bool whether this column can be null.
     */
    public $allowNull = false;
    /**
     * @var string abstract type of this column. Possible abstract types include:
     * char, string, text, boolean, smallint, integer, bigint, float, decimal, datetime,
     * timestamp, time, date, binary, and money.
     */
    public $type = '';
    /**
     * @var string the PHP type of this column. Possible PHP types include:
     * `string`, `boolean`, `integer`, `double`, `array`.
     */
    public $phpType = '';
    /**
     * @var string the DB type of this column. Possible DB types vary according to the type of DBMS.
     */
    public $dbType = '';
    /**
     * @var mixed default value of this column
     */
    public $defaultValue = null;
    /**
     * @var array|null enumerable values. This is set only if the column is declared to be an enumerable type.
     */
    public $enumValues = null;
    /**
     * @var int|null display size of the column.
     */
    public $size = null;
    /**
     * @var int|null precision of the column data, if it is numeric.
     */
    public $precision = null;
    /**
     * @var int|null scale of the column data, if it is numeric.
     */
    public $scale = null;
    /**
     * @var bool|null whether this column is a primary key
     */
    public $isPrimaryKey = null;
    /**
     * @var bool whether this column is auto-incremental
     */
    public $autoIncrement = false;
    /**
     * @var bool whether this column is unsigned. This is only meaningful
     * when [[type]] is `smallint`, `integer` or `bigint`.
     */
    public $unsigned = false;
    /**
     * @var string|null comment of this column. Not all DBMS support this.
     */
    public $comment = null;

    /**
     * Converts the input value according to [[phpType]] after retrieval from the database.
     * If the value is null or an [[Expression]], it will not be converted.
     * @param mixed $value input value
     * @return mixed converted value
     */
    public function phpTypecast($value)
    {
        return $this->typecast($value);
    }

    /**
     * Converts a raw column default value from the database metadata to its PHP representation.
     *
     * The base implementation delegates to {@see phpTypecast()}. Driver-specific subclasses (MySQL, MSSQL, Oracle)
     * override this method to normalize vendor-specific default formats such as `CURRENT_TIMESTAMP`, bit literals, or
     * quote-wrapped expressions before delegating to `phpTypecast()`.
     *
     * @param mixed $value Raw default value from the database schema metadata.
     *
     * @return mixed Converted PHP value.
     *
     * @since 22.0
     */
    public function defaultPhpTypecast($value)
    {
        return $this->phpTypecast($value);
    }

    /**
     * Converts the input value according to [[type]] and [[dbType]] for use in a db query.
     * If the value is null or an [[Expression]], it will not be converted.
     * @param mixed $value input value
     * @return mixed converted value. This may also be an array containing the value as the first element
     * and the PDO type as the second element.
     */
    public function dbTypecast($value)
    {
        // the default implementation does the same as casting for PHP, but it should be possible
        // to override this with annotation of explicit PDO type.
        return $this->typecast($value);
    }

    /**
     * Returns whether this column uses the given abstract type.
     *
     * @param string $type Abstract column type.
     *
     * @return bool Whether this column uses the given abstract type.
     *
     * @since 22.0
     */
    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Converts the input value according to [[phpType]] after retrieval from the database.
     * If the value is null or an [[Expression]], it will not be converted.
     * @param mixed $value input value
     * @return mixed converted value
     * @since 2.0.3
     */
    protected function typecast($value)
    {
        if (
            $value === ''
            && !in_array(
                $this->type,
                [
                    Schema::TYPE_TEXT,
                    Schema::TYPE_STRING,
                    Schema::TYPE_BINARY,
                    Schema::TYPE_CHAR
                ],
                true
            )
        ) {
            return null;
        }

        if (
            $value === null
            || gettype($value) === $this->phpType
            || $value instanceof ExpressionInterface
            || $value instanceof Query
        ) {
            return $value;
        }

        if (
            is_array($value)
            && count($value) === 2
            && isset($value[1])
            && in_array($value[1], $this->getPdoParamTypes(), true)
        ) {
            return new PdoValue($value[0], $value[1]);
        }

        switch ($this->phpType) {
            case 'resource':
            case 'string':
                if (is_resource($value)) {
                    return $value;
                }
                if (is_float($value)) {
                    // ensure type cast always has . as decimal separator in all locales
                    return StringHelper::floatToString($value);
                }
                if (
                    is_numeric($value)
                    && ColumnSchemaBuilder::CATEGORY_NUMERIC === ColumnSchemaBuilder::$typeCategoryMap[$this->type]
                ) {
                    // https://github.com/yiisoft/yii2/issues/14663
                    return $value;
                }

                if (is_object($value) && $value instanceof \BackedEnum) {
                    return (string) $value->value;
                }

                return (string) $value;
            case 'integer':
                if (is_object($value) && $value instanceof \BackedEnum) {
                    return (int) $value->value;
                }
                return (int) $value;
            case 'boolean':
                // treating a 0 bit value as false too
                // https://github.com/yiisoft/yii2/issues/9006
                return (bool) $value && $value !== "\0" && strtolower($value) !== 'false';
            case 'double':
                return (float) $value;
        }

        return $value;
    }

    /**
     * @return int[] array of numbers that represent possible PDO parameter types
     */
    private function getPdoParamTypes()
    {
        return [\PDO::PARAM_BOOL, \PDO::PARAM_INT, \PDO::PARAM_STR, \PDO::PARAM_LOB, \PDO::PARAM_NULL, \PDO::PARAM_STMT];
    }
}
