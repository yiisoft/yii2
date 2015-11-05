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
 * ColumnSchemaBuilder helps to define database schema types using a PHP interface.
 *
 * See [[SchemaBuilderTrait]] for more detailed description and usage examples.
 *
 * @author Vasenin Matvey <vaseninm@gmail.com>
 * @since 2.0.6
 */
class ColumnSchemaBuilder extends Object
{
    /**
     * @var string the column type definition such as INTEGER, VARCHAR, DATETIME, etc.
     */
    protected $type;
    /**
     * @var integer|string|array column size or precision definition. This is what goes into the parenthesis after
     * the column type. This can be either a string, an integer or an array. If it is an array, the array values will
     * be joined into a string separated by comma.
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
     * @var boolean whether the column values should be unsigned. If this is `true`, a `UNSIGNED` constraint will be added.
     */
    protected $isUnsigned = false;
    /**
     * @var string the `CHECK` constraint for the column.
     */
    protected $check;
    /**
     * @var mixed default value of the column.
     */
    protected $default;
    /**
     * @var Schema $schema Used schema
     */
    protected $schema;


    /**
     * Create a column schema builder instance giving the type and value precision.
     *
     * @param Schema $schema Used schema
     * @param string $type type of the column. See [[$type]].
     * @param integer|string|array $length length or precision of the column. See [[$length]].
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct(Schema $schema, $type, $length = null, $config = [])
    {
        $this->schema = $schema;
        $this->type = $type;
        $this->length = $length;
        parent::__construct($config);
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
     * Adds a `UNSIGNED` constraint to the column.
     * @return $this
     */
    public function unsigned()
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
            $this->buildUnsignedString() .
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
        if ($this->length === null || $this->length === []) {
            return '';
        }
        if (is_array($this->length)) {
            $this->length = implode(',', $this->length);
        }
        return "({$this->length})";
    }

    /**
     * Builds the not null constraint for the column.
     * @return string returns 'NOT NULL' if [[isNotNull]] is true, otherwise it returns an empty string.
     */
    protected function buildNotNullString()
    {
        return $this->isNotNull ? ' NOT NULL' : '';
    }

    /**
     * Builds the unique constraint for the column.
     * @return string returns string 'UNIQUE' if [[isUnique]] is true, otherwise it returns an empty string.
     */
    protected function buildUniqueString()
    {
        return $this->isUnique ? ' UNIQUE' : '';
    }

    /**
     * Builds the unsigned constraint for the column.
     * @return string returns string 'UNSIGNED' if [[isUnsigned]] is true, otherwise it returns an empty string.
     */
    protected function buildUnsignedString()
    {
        return $this->isUnsigned && in_array($this->type, $this->schema->unsignedTypes, true) ? ' UNSIGNED' : '';
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
                $string .= $this->schema->quoteValue($this->default);
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
}
