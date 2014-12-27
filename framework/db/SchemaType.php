<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\Object;

/**
 * SchemaType is the class for easy define DB's shema types.
 */
class SchemaType extends Object
{
    const ATTR_BINARY = 'BINARY';
    const ATTR_UNSIGNED = 'UNSIGNED';
    const ATTR_UNSIGNED_ZEROFILL = 'UNSIGNED ZEROFILL';
    const ATTR_TIMESTAMP = 'ON UPDATE CURRENT_TIMESTAMP';

    /**
     * Type of column
     * @var string
     */
    private $_type;

    /**
     * Size of column
     * @var integer
     */
    private $_size;

    /**
     * Specify may column value be Null or not
     * @var boolean
     */
    private $_notNull;

    /**
     * Default value of column
     * @var string
     */
    private $_default;

    /**
     * Comment of column
     * @var string
     */
    private $_comment;

    /**
     * Attributes of column
     * @var string
     */
    private $_attributes;

    /**
     * Charset of column
     * @var string
     */
    private $_charset;

    /**
     * Specify column will auto incement value
     * @var boolean
     */
    private $_auto_increment;

    /**
     * Collate of column
     * @var string
     */
    private $_collate;

    /**
     * @param string  $type type of column
     * @param integer $size size of column
     */
    public function __construct($type, $size = null)
    {
        $this->_type = $type;
        $this->size($size);
    }

    /**
     * Trick for use keyword as class method
     */
    public function __call($name, $args)
    {
        if ($name == "default") {
            return call_user_func_array(array($this, "defaultValue"), $args);
        }
    }

    /**
     * Set size of column
     * @param  integer $size size of column
     * @return SchemaType
     */
    public function size($size)
    {
        $this->_size = $size;
        return $this;
    }

    /**
     * Specify column as not null
     * @return SchemaType
     */
    public function notNull()
    {
        $this->_notNull = true;
        return $this;
    }

    /**
     * Set default value for column
     * @param  mixed $value default value
     * @return SchemaType
     */
    public function defaultValue($value)
    {
        $valueType = gettype($value);
        if ($valueType == 'string') {
            $this->_default = "'" . $value . "'";
        } else {
            $this->_default = $value;
        }
        return $this;
    }

    /**
     * Set comment for column
     * @param  string $comment comment for column
     * @return SchemaType
     */
    public function comment($comment)
    {
        $this->_comment = $comment;
        return $this;
    }


    /**
     * Set attribute for column
     * @param  string $attribute attribute for column
     * @return SchemaType
     */
    public function attribute($attribute)
    {
        $this->_attributes = $attribute;
        return $this;
    }

    /**
     * Set charset and collate for column
     * @param  string $charset charset for column
     * @param  string $collate collate for column
     * @return SchemaType
     */
    public function charset($charset, $collate = null)
    {
        $this->_charset = $charset;
        $this->_collate = $collate;
        return $this;
    }

    /**
     * Specify column as not null
     * @return SchemaType
     */
    public function autoIncrement()
    {
        $this->_auto_increment = true;
        return $this;
    }

    /**
     * Return string interpritation of type
     * @return string
     */
    public function __toString()
    {
        $type = "";
        if ($this->_type == null) {
            return $type;
        }
        $type .= $this->_type;

        if ($this->_size != null) {
            $type .= "(" . $this->_size . ")";
        }

        if ($this->_attributes) {
            $type .= " " . $this->_attributes;
        }

        if ($this->_charset) {
            $type .= " CHARACTER SET " . $this->_charset;
        }

        if ($this->_collate) {
            $type .= " COLLATE " . $this->_collate;
        }

        if ($this->_notNull) {
            $type .= " NOT NULL";
        }

        if ($this->_default !== null) {
            $type .= " DEFAULT " . $this->_default;
        }

        if ($this->_auto_increment) {
            $type .= " AUTO_INCREMENT";
        }

        if ($this->_comment) {
            $type .= " COMMENT '" . $this->_comment . "'";
        }

        return $type;
    }
}
