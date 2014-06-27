<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use yii\base\Object;
use yii\db\Expression;

/**
 * ColumnSchema class describes the metadata of a column in a Sphinx index.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ColumnSchema extends Object
{
    /**
     * @var string name of this column (without quotes).
     */
    public $name;
    /**
     * @var string abstract type of this column. Possible abstract types include:
     * string, text, boolean, smallint, integer, bigint, float, decimal, datetime,
     * timestamp, time, date, binary, and money.
     */
    public $type;
    /**
     * @var string the PHP type of this column. Possible PHP types include:
     * string, boolean, integer, double.
     */
    public $phpType;
    /**
     * @var string the DB type of this column. Possible DB types vary according to the type of DBMS.
     */
    public $dbType;
    /**
     * @var boolean whether this column is a primary key
     */
    public $isPrimaryKey;
    /**
     * @var boolean whether this column is an attribute
     */
    public $isAttribute;
    /**
     * @var boolean whether this column is a indexed field
     */
    public $isField;
    /**
     * @var boolean whether this column is a multi value attribute (MVA)
     */
    public $isMva;

    /**
     * Converts the input value according to [[phpType]] after retrieval from the database.
     * If the value is null or an [[Expression]], it will not be converted.
     * @param mixed $value input value
     * @return mixed converted value
     */
    public function phpTypecast($value)
    {
        if ($value === null || gettype($value) === $this->phpType || $value instanceof Expression) {
            return $value;
        }
        if ($value === '' && $this->type !== Schema::TYPE_STRING) {
            return null;
        }
        switch ($this->phpType) {
            case 'string':
                return is_resource($value) ? $value : (string) $value;
            case 'integer':
                return (integer) $value;
            case 'boolean':
                return (boolean) $value;
            case 'double':
                return (double) $value;
        }

        return $value;
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
        // the default implementation does the same as casting for PHP but it should be possible
        // to override this with annotation of explicit PDO type.
        return $this->phpTypecast($value);
    }
}
