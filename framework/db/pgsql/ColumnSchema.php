<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use yii\db\ArrayExpression;
use yii\db\ExpressionInterface;
use yii\db\JsonExpression;

/**
 * Class ColumnSchema for PostgreSQL database.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * @var int the dimension of array. Defaults to 0, means this column is not an array.
     */
    public $dimension = 0;
    /**
     * @var bool whether the column schema should OMIT using JSON support feature.
     * You can use this property to make upgrade to Yii 2.0.14 easier.
     * Default to `false`, meaning JSON support is enabled.
     *
     * @since 2.0.14.1
     * @deprecated Since 2.0.14.1 and will be removed in 2.1.
     */
    public $disableJsonSupport = false;
    /**
     * @var bool whether the column schema should OMIT using PgSQL Arrays support feature.
     * You can use this property to make upgrade to Yii 2.0.14 easier.
     * Default to `false`, meaning Arrays support is enabled.
     *
     * @since 2.0.14.1
     * @deprecated Since 2.0.14.1 and will be removed in 2.1.
     */
    public $disableArraySupport = false;
    /**
     * @var bool whether the Array column value should be unserialized to an [[ArrayExpression]] object.
     * You can use this property to make upgrade to Yii 2.0.14 easier.
     * Default to `true`, meaning arrays are unserialized to [[ArrayExpression]] objects.
     *
     * @since 2.0.14.1
     * @deprecated Since 2.0.14.1 and will be removed in 2.1.
     */
    public $deserializeArrayColumnToArrayExpression = true;


    /**
     * {@inheritdoc}
     */
    public function dbTypecast($value)
    {
        if ($value instanceof ExpressionInterface) {
            return $value;
        }

        if (!$this->disableArraySupport && $this->dimension > 0) {
            return new ArrayExpression($value, $this->dbType, $this->dimension);
        }
        if (!$this->disableJsonSupport && in_array($this->dbType, [Schema::TYPE_JSON, Schema::TYPE_JSONB], true)) {
            return new JsonExpression($value, $this->type);
        }

        return $this->typecast($value);
    }

    /**
     * {@inheritdoc}
     */
    public function phpTypecast($value)
    {
        if (!$this->disableArraySupport && $this->dimension > 0) {
            if (!is_array($value)) {
                $value = $this->getArrayParser()->parse($value);
            }
            if (is_array($value)) {
                array_walk_recursive($value, function (&$val, $key) {
                    $val = $this->phpTypecastValue($val);
                });
            }

            return $this->deserializeArrayColumnToArrayExpression
                ? new ArrayExpression($value, $this->dbType, $this->dimension)
                : $value;
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

        switch ($this->type) {
            case Schema::TYPE_BOOLEAN:
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
                return $this->disableJsonSupport ? $value : json_decode($value, true);
        }

        return parent::phpTypecast($value);
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
