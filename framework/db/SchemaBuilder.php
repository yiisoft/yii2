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
 * SchemaBuilder is the class help to define DB's schema types.
 *
 * For example you may use the following code inside your migration files:
 *
 * ```php
 * $this->createTable('table', [
 *   'name' => Schema::string(64)->notNull(),
 *   'type' => Schema::integer()->notNull()->default(10),
 *   'description' => Schema::text(),
 *   'rule_name' => Schema::string(64),
 *   'data' => Schema::text(),
 *   'created_at' => Schema::integer(),
 *   'updated_at' => Schema::integer(),
 *]);
 * ```
 *
 * @method SchemaBuilder default($default = null) see [[SchemaBuilder::_default()]] for more info
 *
 * @author Vasenin Matvey <vaseninm@gmail.com>
 * @since 2.0.5
 */
abstract class SchemaBuilder extends Object
{
    /**
     * @var string column type
     */
    protected $type;

    /**
     * @var integer column size
     */
    protected $length;

    /**
     * @var boolean whether value is not nullable
     */
    protected $isNotNull = false;

    /**
     * @var boolean whether value should be unique
     */
    protected $isUnique = false;

    /**
     * @var string check value of column
     */
    protected $check;

    /**
     * @var mixed default value of column
     */
    protected $default;

    /**
     * Makes column a primary key
     *
     * @param integer $length
     *
     * @return self
     */
    public static function primaryKey($length = null)
    {
        return static::create(Schema::TYPE_PK, $length);
    }

    /**
     * Makes column a big primary key
     *
     * @param integer $length
     *
     * @return self
     */
    public static function bigPrimaryKey($length = null)
    {
        return static::create(Schema::TYPE_BIGPK, $length);
    }

    /**
     * Makes column a string
     *
     * @param integer $length
     *
     * @return self
     */
    public static function string($length = null)
    {
        return static::create(Schema::TYPE_STRING, $length);
    }

    /**
     * Makes column a text
     *
     * @param integer $length
     *
     * @return self
     */
    public static function text($length = null)
    {
        return static::create(Schema::TYPE_TEXT, $length);
    }

    /**
     * Makes column a smallint
     *
     * @param integer $length
     *
     * @return self
     */
    public static function smallInteger($length = null)
    {
        return static::create(Schema::TYPE_SMALLINT, $length);
    }

    /**
     * Makes column a integer
     *
     * @param integer $length
     *
     * @return self
     */
    public static function integer($length = null)
    {
        return static::create(Schema::TYPE_INTEGER, $length);
    }

    /**
     * Makes column a bigint
     *
     * @param integer $length
     *
     * @return self
     */
    public static function bigInteger($length = null)
    {
        return static::create(Schema::TYPE_BIGINT, $length);
    }

    /**
     * Makes column a float
     *
     * @param integer $precision
     * @param integer $scale
     *
     * @return self
     */
    public static function float($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_FLOAT, $precision, $scale);
    }

    /**
     * Makes column a double
     *
     * @param integer $precision
     * @param integer $scale
     *
     * @return self
     */
    public static function double($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_DOUBLE, $precision, $scale);
    }

    /**
     * Makes column a decimal
     *
     * @param integer $precision
     * @param integer $scale
     *
     * @return self
     */
    public static function decimal($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_DECIMAL, $precision, $scale);
    }

    /**
     * Makes column a datetime
     *
     * @param integer $length
     *
     * @return self
     */
    public static function dateTime($length = null)
    {
        return static::create(Schema::TYPE_DATETIME, $length);
    }

    /**
     * Makes column a timestamp
     *
     * @param integer $length
     *
     * @return self
     */
    public static function timestamp($length = null)
    {
        return static::create(Schema::TYPE_TIMESTAMP, $length);
    }

    /**
     * Makes column a time
     *
     * @param integer $length
     *
     * @return self
     */
    public static function time($length = null)
    {
        return static::create(Schema::TYPE_TIME, $length);
    }

    /**
     * Makes column a date
     *
     * @return self
     */
    public static function date()
    {
        return static::create(Schema::TYPE_DATE);
    }

    /**
     * Makes column a binary
     *
     * @param integer $length
     *
     * @return self
     */
    public static function binary($length = null)
    {
        return static::create(Schema::TYPE_BINARY, $length);
    }

    /**
     * Makes column a boolean
     *
     * @param integer $length
     *
     * @return self
     */
    public static function boolean($length = null)
    {
        return static::create(Schema::TYPE_BOOLEAN, $length);
    }

    /**
     * Makes column a money
     *
     * @param integer $precision
     * @param integer $scale
     *
     * @return self
     */
    public static function money($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_MONEY, $precision, $scale);
    }

    /**
     * Makes column not nullable
     *
     * @return $this
     */
    public function notNull()
    {
        $this->isNotNull = true;

        return $this;
    }

    /**
     * Makes column unique
     *
     * @return $this
     */
    public function unique()
    {
        $this->isUnique = true;

        return $this;
    }

    /**
     * Calls the named method which is not a class method.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when an unknown method is being invoked.
     *
     * @param string $name the method name
     * @param array $arguments method parameters
     *
     * @return mixed the method return value
     */
    public function __call($name, $arguments)
    {
        if ($name === 'default') {
            return call_user_func_array([$this, '_default'], $arguments);
        }

        return parent::__call($name, $arguments);
    }

    /**
     * Specify check value for the column
     *
     * @param string $check
     *
     * @return $this
     */
    public function check($check)
    {
        $this->check = $check;

        return $this;
    }

    /**
     * Build full string for create the column's schema
     *
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
     * Specify default value for the column
     *
     * @param mixed $default
     *
     * @return $this
     */
    protected function _default($default = null)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Returns string with length of column
     *
     * @return string
     */
    protected function buildLengthString()
    {
        return ($this->length !== null ? "({$this->length})" : '');
    }

    /**
     * Returns string with NOT NULL if isNotNull is true, otherwise returns
     * empty string
     *
     * @return string
     */
    protected function buildNotNullString()
    {
        return $this->isNotNull === true ? ' NOT NULL' : '';
    }

    /**
     * Returns string with UNIQUE if isUnique is true, otherwise returns
     * empty string
     *
     * @return string
     */
    protected function buildUniqueString()
    {
        return $this->isUnique === true ? ' UNIQUE' : '';
    }

    /**
     * Returns string with default value of column
     *
     * @return string
     */
    protected function buildDefaultString()
    {
        $string = '';

        if ($this->default !== null) {
            $string .= ' DEFAULT ';
            switch (gettype($this->default)) {
                case 'integer':
                case 'double':
                    $string .= $this->default;
                    break;
                case 'boolean':
                    $string .= $this->default ? 'TRUE' : 'FALSE';
                    break;
                default:
                    $string .= "'{$this->default}'";
            }
        }

        return $string;
    }

    /**
     * Returns check value of column
     *
     * @return string
     */
    protected function buildCheckString()
    {
        return ($this->check !== null ? " CHECK ({$this->check})" : '');
    }

    /**
     * Create schema builder for types with length
     *
     * @param string $type schema of column
     * @param integer $length length of column
     * @return self
     */
    protected static function create($type, $length = null)
    {
        $object = new static;

        $object->type = $type;
        $object->length = $length;

        return $object;
    }

    /**
     * Create schema builder for numeric types types with precision and scale
     *
     * @param string $type schema of column
     * @param integer $precision precision of column
     * @param integer $scale scale of column
     * @return self
     */
    protected static function createNumeric($type, $precision = null, $scale = null)
    {
        $length = null;

        if ($precision !== null) {
            $length = $precision . ($scale !== null ? ",$scale" : '');
        }

        return self::create($type, $length);
    }
}
