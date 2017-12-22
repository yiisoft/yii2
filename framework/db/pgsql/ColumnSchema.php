<?php

namespace yii\db\pgsql;

use yii\db\ArrayExpression;
use yii\db\ExpressionInterface;

/**
 * Class ColumnSchema
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
     * {@inheritdoc}
     */
    public function dbTypecast($value)
    {
        if ($this->dimension > 0 && !$value instanceof ExpressionInterface) {
            return new ArrayExpression($value, $this->dbType, $this->dimension);
        }

        return $this->typecast($value);
    }

    /**
     * @inheritdoc
     */
    public function phpTypecast($value)
    {
        if ($this->dimension > 0) {
            if (!is_array($value)) {
                $value = ArrayConverter::toPhp($value, $this->delimiter);
            }
            if (is_array($value)) {
                array_walk_recursive($value, function (&$val, $key) {
                    $val = $this->phpTypecastValue($val);
                });
            }

            return $value;
        }

        return $this->phpTypecastValue($value);
    }

    /**
     * Converts the input value according to [[phpType]] after retrieval from the database.
     * @param mixed $value input value
     * @return mixed converted value
     */
    public function phpTypecastValue($value)
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
            case Schema::TYPE_BIT:
                return bindec($value);
            case Schema::TYPE_BINARY:
                return is_string($value) && strncmp($value, '\\x', 2) === 0 ? pack('H*', substr($value, 2)) : $value;
            case Schema::TYPE_JSON:
                return json_decode($value, true);
            case Schema::TYPE_TIMESTAMP:
            case Schema::TYPE_TIME:
            case Schema::TYPE_DATE:
            case Schema::TYPE_DATETIME:
                return new \DateTime($value);
            case Schema::TYPE_COMPOSITE:
                return $this->phpTypecastComposite($value);
        }

        return parent::phpTypecast($value);
    }
}
