<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use Yii;
use yii\base\Object;

/**
 * SchemaBuilder helps to define database schema types using a PHP interface.
 *
 * For example you may use the following code inside your migration files:
 *
 * ```php
 * $this->createTable('example_table', [
 *   'id' => Schema::primaryKey(),
 *   'name' => Schema::string(64)->notNull(),
 *   'type' => Schema::integer()->notNull()->defaultValue(10),
 *   'description' => Schema::text(),
 *   'rule_name' => Schema::string(64),
 *   'data' => Schema::text(),
 *   'created_at' => Schema::integer(),
 *   'updated_at' => Schema::integer(),
 * ]);
 * ```
 *
 * @author Vasenin Matvey <vaseninm@gmail.com>
 * @since 2.0.6
 */
abstract class SchemaBuilder extends Object
{
    /**
     * @var string the column type definition such as INTEGER, VARCHAR, DATETIME, etc.
     */
    protected $type;
    /**
     * @var integer column size or precision definition. This is what goes into the parenthesis after
     * the column type.
     */
    protected $length;
    /**
     * @var boolean whether the column is not nullable. If this is `true`, a `NOT NULL` constraint will be added.
     */
    protected $isNotNull = false;
    /**
     * @var boolean whether the column values should be unique. If this is `true`, a `UNIQUE` constraint will be added.
     */
    protected $isUnique = false;
    /**
     * @var string the `CHECK` constraint for the column.
     */
    protected $check;
    /**
     * @var mixed default value of the column.
     */
    protected $default;


    /**
     * Creates a primary key column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function primaryKey($length = null)
    {
        return static::create(Schema::TYPE_PK, $length);
    }

    /**
     * Creates a big primary key column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function bigPrimaryKey($length = null)
    {
        return static::create(Schema::TYPE_BIGPK, $length);
    }

    /**
     * Creates a string column.
     * @param integer $length column size definition i.e. the maximum string length.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function string($length = null)
    {
        return static::create(Schema::TYPE_STRING, $length);
    }

    /**
     * Creates a text column.
     * @param integer $length column size definition i.e. the maximum text length.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function text($length = null)
    {
        return static::create(Schema::TYPE_TEXT, $length);
    }

    /**
     * Creates a smallint column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function smallInteger($length = null)
    {
        return static::create(Schema::TYPE_SMALLINT, $length);
    }

    /**
     * Creates an integer column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function integer($length = null)
    {
        return static::create(Schema::TYPE_INTEGER, $length);
    }

    /**
     * Creates a bigint column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function bigInteger($length = null)
    {
        return static::create(Schema::TYPE_BIGINT, $length);
    }

    /**
     * Creates a float column.
     * @param integer $precision TODO
     * @param integer $scale column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function float($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_FLOAT, $precision, $scale);
    }

    /**
     * Creates a double column.
     * @param integer $precision TODO
     * @param integer $scale column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function double($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_DOUBLE, $precision, $scale);
    }

    /**
     * Creates a decimal column.
     * @param integer $precision TODO
     * @param integer $scale column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function decimal($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_DECIMAL, $precision, $scale);
    }

    /**
     * Creates a datetime column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function dateTime($length = null)
    {
        return static::create(Schema::TYPE_DATETIME, $length);
    }

    /**
     * Creates a timestamp column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function timestamp($length = null)
    {
        return static::create(Schema::TYPE_TIMESTAMP, $length);
    }

    /**
     * Creates a time column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function time($length = null)
    {
        return static::create(Schema::TYPE_TIME, $length);
    }

    /**
     * Creates a date column.
     * @return static the column instance which can be further customized.
     */
    public static function date()
    {
        return static::create(Schema::TYPE_DATE);
    }

    /**
     * Creates a binary column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function binary($length = null)
    {
        return static::create(Schema::TYPE_BINARY, $length);
    }

    /**
     * Creates a boolean column.
     * @param integer $length column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function boolean($length = null)
    {
        return static::create(Schema::TYPE_BOOLEAN, $length);
    }

    /**
     * Creates a money column.
     * @param integer $precision TODO
     * @param integer $scale column size or precision definition.
     * This parameter will be ignored if not supported by the DBMS.
     * @return static the column instance which can be further customized.
     */
    public static function money($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_MONEY, $precision, $scale);
    }

    /**
     * Adds a `NOT NULL` constraint to the column.
     * @return $this
     */
    public function notNull()
    {
        $this->isNotNull = true;
        return $this;
    }

    /**
     * Adds a `UNIQUE` constraint to the column.
     * @return $this
     */
    public function unique()
    {
        $this->isUnique = true;
        return $this;
    }

    /**
     * Sets a `CHECK` constraint for the column.
     * @param string $check the SQL of the `CHECK` constraint to be added.
     * @return $this
     */
    public function check($check)
    {
        $this->check = $check;
        return $this;
    }

    /**
     * Specify the default value for the column.
     * @param mixed $default the default value.
     * @return $this
     */
    public function defaultValue($default)
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Build full string for create the column's schema
     * @return string
     */
    public function __toString()
    {
        return
            $this->type .
            $this->buildLengthString() .
            $this->buildNotNullString() .
            $this->buildUniqueString() .
            $this->buildDefaultString() .
            $this->buildCheckString();
    }

    /**
     * Builds the length/precision part of the column.
     * @return string
     */
    protected function buildLengthString()
    {
        return ($this->length !== null ? "({$this->length})" : '');
    }

    /**
     * Builds the not null constraint for the column.
     * @return string returns 'NOT NULL' if [[isNotNull]] is true, otherwise it returns an empty string.
     */
    protected function buildNotNullString()
    {
        return $this->isNotNull === true ? ' NOT NULL' : '';
    }

    /**
     * Builds the unique constraint for the column.
     * @return string returns string 'UNIQUE' if [[isUnique]] is true, otherwise it returns an empty string.
     */
    protected function buildUniqueString()
    {
        return $this->isUnique === true ? ' UNIQUE' : '';
    }

    /**
     * Builds the default value specification for the column.
     * @return string string with default value of column.
     */
    protected function buildDefaultString()
    {
        if ($this->default === null) {
            return '';
        }

        $string = ' DEFAULT ';
        switch (gettype($this->default)) {
            case 'integer':
                $string .= (string) $this->default;
                break;
            case 'double':
                // ensure type cast always has . as decimal separator in all locales
                $string .= str_replace(',', '.', (string) $this->default);
                break;
            case 'boolean':
                $string .= $this->default ? 'TRUE' : 'FALSE';
                break;
            default:
                $string .= "'{$this->default}'";
        }

        return $string;
    }

    /**
     * Builds the check constraint for the column.
     * @return string a string containing the CHECK constraint.
     */
    protected function buildCheckString()
    {
        return $this->check !== null ? " CHECK ({$this->check})" : '';
    }

    /**
     * Create schema builder instance for types with length.
     *
     * @param string $type type of the column. See [[$type]].
     * @param integer|string $length length or precision of the column. See [[$length]].
     * @return static
     */
    protected static function create($type, $length = null)
    {
        $object = new static;

        $object->type = $type;
        $object->length = $length;

        return $object;
    }

    /**
     * Create schema builder for numeric types with precision and scale.
     *
     * @param string $type type of the column. See [[$type]].
     * @param integer $precision precision of the column.
     * @param integer $scale scale of the column.
     * @return static
     */
    protected static function createNumeric($type, $precision = null, $scale = null)
    {
        $length = null;

        if ($precision !== null) {
            $length = $precision . ($scale !== null ? ",$scale" : '');
        }

        return static::create($type, $length);
    }
}
