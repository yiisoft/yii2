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
 * @author Vasenin Matvey <vaseninm@gmail.com>
 * @since 2.0.5
 */
abstract class SchemaBuilder extends Object
{
    /**
     * @var string
     */
    protected $schema = null;
    /**
     * @var integer
     */
    protected $length = null;
    /**
     * @var boolean
     */
    protected $isNotNull = null;
    /**
     * @var string
     */
    protected $check = null;
    /**
     * @var string
     */
    protected $default = null;

    /**
     * Specify type of field as primary key
     *
     * @param integer $length
     * @return SchemaBuilder
     */
    public static function primaryKey($length = null)
    {
        return static::createDefault(Schema::TYPE_PK, $length);
    }

    /**
     * Specify type of field as big primary key
     *
     * @param integer $length
     * @return SchemaBuilder
     */
    public static function bigPrimaryKey($length = null)
    {
        return static::createDefault(Schema::TYPE_BIGPK, $length);
    }

    /**
     * Specify type of field as string
     *
     * @param integer $length
     * @return SchemaBuilder
     */
    public static function string($length = null)
    {
        return static::createDefault(Schema::TYPE_STRING, $length);
    }

    /**
     * Specify type of field as text
     *
     * @param integer $length
     * @return SchemaBuilder
     */
    public static function text($length = null)
    {
        return static::createDefault(Schema::TYPE_TEXT, $length);
    }

    /**
     * Specify type of field as smallint
     *
     * @param integer $length
     * @return SchemaBuilder
     */
    public static function smallInteger($length = null)
    {
        return static::createDefault(Schema::TYPE_SMALLINT, $length);
    }

    /**
     * Specify type of field as integer
     *
     * @param integer $length
     * @return SchemaBuilder
     */
    public static function integer($length = null)
    {
        return static::createDefault(Schema::TYPE_INTEGER, $length);
    }

    /**
     * Specify type of field as bigint
     *
     * @param integer $length
     * @return SchemaBuilder
     */
    public static function bigInteger($length = null)
    {
        return static::createDefault(Schema::TYPE_BIGINT, $length);
    }

    /**
     * Specify type of field as float
     *
     * @param integer $precision
     * @param integer $scale
     * @return SchemaBuilder
     */
    public static function float($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_FLOAT, $precision, $scale);
    }

    /**
     * Specify type of field as double
     *
     * @param integer $precision
     * @param integer $scale
     * @return SchemaBuilder
     */
    public static function double($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_DOUBLE, $precision, $scale);
    }

    /**
     * Specify type of field as decimal
     *
     * @param integer $precision
     * @param integer $scale
     * @return SchemaBuilder
     */
    public static function decimal($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_DECIMAL, $precision, $scale);
    }

    /**
     * Specify type of field as datetime
     *
     * @return SchemaBuilder
     */
    public static function dateTime($length = null)
    {
        return static::createDefault(Schema::TYPE_DATETIME, $length);
    }

    /**
     * Specify type of field as timestamp
     *
     * @return SchemaBuilder
     */
    public static function timestamp($length = null)
    {
        return static::createDefault(Schema::TYPE_TIMESTAMP, $length);
    }

    /**
     * Specify type of field as time
     *
     * @return SchemaBuilder
     */
    public static function time($length = null)
    {
        return static::createDefault(Schema::TYPE_TIME, $length);
    }

    /**
     * Specify type of field as date
     *
     * @return SchemaBuilder
     */
    public static function date($length = null)
    {
        return static::createDefault(Schema::TYPE_DATE, $length);
    }

    /**
     * Specify type of field as binary
     *
     * @param integer $length
     * @return SchemaBuilder
     */
    public static function binary($length = null)
    {
        return static::createDefault(Schema::TYPE_BINARY, $length);
    }

    /**
     * Specify type of field as boolean
     *
     * @param integer $length
     * @return SchemaBuilder
     */
    public static function boolean($length = null)
    {
        return static::createDefault(Schema::TYPE_BOOLEAN, $length);
    }

    /**
     * Specify type of field as money
     *
     * @param integer $precision
     * @param integer $scale
     * @return SchemaBuilder
     */
    public static function money($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_MONEY, $precision, $scale);
    }

   /**
    * @return SchemaBuilder
    */
    public function notNull()
    {
        $this->isNotNull = true;

        return $this;
    }

    public function __call($name, $arguments)
    {
        if ($name === 'default') {
            return call_user_func_array([$this, '_default'], $arguments);
        }
    }

    /**
     * @param string $check
     * @return SchemaBuilder
     */
    public function check($check)
    {
        $this->check = $check;

        return $this;
    }

    public function __toString()
    {
        return
            $this->schema .
            $this->getLengthString() .
            $this->getNullString() .
            $this->getDefaultString() .
            $this->getCheckString();
    }

    /**
     * @param mixed $default
     * @return SchemaBuilder
     */
    protected function _default($default = null)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return string
     */
    protected function getLengthString()
    {
        return ($this->length !== null ? "({$this->length})" : '');
    }

    /**
     * @return string
     */
    protected function getNullString()
    {
        return ($this->isNotNull === true ? ' NOT NULL' : '');
    }

    /**
     * @return string
     */
    protected function getDefaultString()
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
     * @return string
     */
    protected function getCheckString()
    {
        return ($this->check !== null ? " CHECK ({$this->check})" : '');
    }

    /**
     * @param string $type
     * @param integer $length
     * @return SchemaBuilder
     */
    protected static function createDefault($type, $length = null)
    {
        $object = new static;

        $object->schema = $type;
        $object->length = $length;

        return $object;
    }

    /**
     * @param string $type
     * @param integer $precision
     * @param integer $scale
     * @return SchemaBuilder
     */
    protected static function createNumeric($type, $precision = null, $scale = null)
    {
        $object = new static;

        $object->schema = $type;

        if ($precision !== null) {
            $object->length = $precision . ($scale !== null ? ",$scale" : '');
        }

        return $object;
    }
}
