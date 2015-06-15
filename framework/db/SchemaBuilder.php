<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use Yii;
use yii\base\Object;

class SchemaBuilder extends Object
{
    protected $schema = null;
    protected $length = null;
    protected $isNull = null;
    protected $check = null;
    protected $default = null;

    public static function pk($length = null)
    {
        return self::createDefault(Schema::TYPE_PK, $length);
    }

    public static function bigPk($length = null)
    {
        return self::createDefault(Schema::TYPE_BIGPK, $length);
    }

    public static function string($length = null)
    {
        return self::createDefault(Schema::TYPE_STRING, $length);
    }

    public static function text($length = null)
    {
        return self::createDefault(Schema::TYPE_TEXT, $length);
    }

    public static function smallint($length = null)
    {
        return self::createDefault(Schema::TYPE_SMALLINT, $length);
    }

    public static function integer($length = null)
    {
        return self::createDefault(Schema::TYPE_INTEGER, $length);
    }

    public static function bigint($length = null)
    {
        return self::createDefault(Schema::TYPE_BIGINT, $length);
    }

    public static function float($precision = null, $scale = null)
    {
        return self::createNumeric(Schema::TYPE_FLOAT, $precision, $scale);
    }

    public static function double($precision = null, $scale = null)
    {
        return self::createNumeric(Schema::TYPE_DOUBLE, $precision, $scale);
    }

    public static function decimal($precision = null, $scale = null)
    {
        return self::createNumeric(Schema::TYPE_DECIMAL, $precision, $scale);
    }

    public static function datetime()
    {
        return self::createDefault(Schema::TYPE_DATETIME);
    }

    public static function timestamp()
    {
        return self::createDefault(Schema::TYPE_TIMESTAMP);
    }

    public static function time()
    {
        return self::createDefault(Schema::TYPE_TIME);
    }

    public static function date()
    {
        return self::createDefault(Schema::TYPE_DATE);
    }

    public static function binary($length = null)
    {
        return self::createDefault(Schema::TYPE_BINARY, $length);
    }

    public static function boolean($length = null)
    {
        return self::createDefault(Schema::TYPE_BOOLEAN, $length);
    }

    public static function money($precision = null, $scale = null)
    {
        return self::createNumeric(Schema::TYPE_MONEY, $precision, $scale);
    }

    public function notNull()
    {
        $this->isNull = false;

        return $this;
    }

    public function null()
    {
        $this->isNull = true;

        return $this;
    }

    public function setDefault($default = null)
    {
        $this->default = $default;

        return $this;
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
            ($this->length !== null ? "({$this->length})" : '') .
            ($this->isNull !== null ? ($this->isNull ? ' NULL' : ' NOT NULL') : '') .
            ($this->default !== null ? ' DEFAULT ' . (is_numeric($this->default) ? $this->default : "'{$this->default}'") : '') .
            ($this->check !== null ? " CHECK ({$this->check})" : '');
    }

    private static function createDefault($type, $length = null)
    {
        $object = new self();

        $object->schema = $type;
        $object->length = $length;

        return $object;
    }

    private static function createNumeric($type, $precision = null, $scale = null)
    {
        $object = new self();

        $object->schema = $type;

        if ($precision !== null) {
            $object->length = $precision . ($scale !== null ? ",$scale" : '');
        }

        return $object;
    }
}
