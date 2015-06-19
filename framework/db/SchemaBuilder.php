<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 *
 * @author Vasenin Matvey <vaseninm@gmail.com>
 * @since 2.0.5
 */

namespace yii\db;

use Yii;
use yii\base\Object;

/**
 * @method SchemaBuilder default($default = null) see [[SchemaBuilder::_default()]] for more info
 */
abstract class SchemaBuilder extends Object
{
    protected $schema = null;
    protected $length = null;
    protected $isNotNull = null;
    protected $check = null;
    protected $default = null;

    public static function primaryKey($length = null)
    {
        return static::createDefault(Schema::TYPE_PK, $length);
    }

    public static function bigPrimaryKey($length = null)
    {
        return static::createDefault(Schema::TYPE_BIGPK, $length);
    }

    public static function string($length = null)
    {
        return static::createDefault(Schema::TYPE_STRING, $length);
    }

    public static function text($length = null)
    {
        return static::createDefault(Schema::TYPE_TEXT, $length);
    }

    public static function smallInteger($length = null)
    {
        return static::createDefault(Schema::TYPE_SMALLINT, $length);
    }

    public static function integer($length = null)
    {
        return static::createDefault(Schema::TYPE_INTEGER, $length);
    }

    public static function bigInteger($length = null)
    {
        return static::createDefault(Schema::TYPE_BIGINT, $length);
    }

    public static function float($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_FLOAT, $precision, $scale);
    }

    public static function double($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_DOUBLE, $precision, $scale);
    }

    public static function decimal($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_DECIMAL, $precision, $scale);
    }

    public static function dateTime($length = null)
    {
        return static::createDefault(Schema::TYPE_DATETIME, $length);
    }

    public static function timestamp($length = null)
    {
        return static::createDefault(Schema::TYPE_TIMESTAMP, $length);
    }

    public static function time($length = null)
    {
        return static::createDefault(Schema::TYPE_TIME, $length);
    }

    public static function date($length = null)
    {
        return static::createDefault(Schema::TYPE_DATE, $length);
    }

    public static function binary($length = null)
    {
        return static::createDefault(Schema::TYPE_BINARY, $length);
    }

    public static function boolean($length = null)
    {
        return static::createDefault(Schema::TYPE_BOOLEAN, $length);
    }

    public static function money($precision = null, $scale = null)
    {
        return static::createNumeric(Schema::TYPE_MONEY, $precision, $scale);
    }

    public function notNull()
    {
        $this->isNotNull = true;

        return $this;
    }

    public function __call($name, $arguments) {
        if ($name === 'default') {
            return call_user_func_array(array($this, '_default'), $arguments);
        }
    }

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

    protected function _default($default = null)
    {
        $this->default = $default;

        return $this;
    }

    protected function getLengthString()
    {
        return ($this->length !== null ? "({$this->length})" : '');
    }

    protected function getNullString()
    {
        return ($this->isNotNull === true ? ' NOT NULL' : '');
    }

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

    protected function getCheckString()
    {
        return ($this->check !== null ? " CHECK ({$this->check})" : '');
    }

    protected static function createDefault($type, $length = null)
    {
        $object = new static;

        $object->schema = $type;
        $object->length = $length;

        return $object;
    }

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
