<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\db\ColumnSchemaBuilder as AbstractColumnSchemaBuilder;
use yii\db\Expression;

/**
 * ColumnSchemaBuilder is the schema builder for MSSQL databases.
 *
 * @property-read string|null $checkValue The `CHECK` constraint for the column.
 * @property-read string|Expression|null $defaultValue Default value of the column.
 *
 * @author Valerii Gorbachev <darkdef@gmail.com>
 * @since 2.0.42
 */
class ColumnSchemaBuilder extends AbstractColumnSchemaBuilder
{
    protected $format = '{type}{length}{notnull}{unique}{default}{check}{append}';


    /**
     * Builds the full string for the column's schema.
     * @return string
     */
    public function __toString()
    {
        if ($this->getTypeCategory() === self::CATEGORY_PK) {
            $format = '{type}{check}{comment}{append}';
        } else {
            $format = $this->format;
        }

        return $this->buildCompleteString($format);
    }

    /**
     * Changes default format string to MSSQL ALTER COMMAND.
     */
    public function setAlterColumnFormat()
    {
        $this->format = '{type}{length}{notnull}{append}';
    }

    /**
     * Getting the `Default` value for constraint
     * @return string|Expression|null default value of the column.
     */
    public function getDefaultValue()
    {
        if ($this->default instanceof Expression) {
            return $this->default;
        }

        return $this->buildDefaultValue();
    }

    /**
     * Get the `Check` value for constraint
     * @return string|null the `CHECK` constraint for the column.
     */
    public function getCheckValue()
    {
        return $this->check !== null ? (string) $this->check : null;
    }

    /**
     * @return bool whether the column values should be unique. If this is `true`, a `UNIQUE` constraint will be added.
     */
    public function isUnique()
    {
        return $this->isUnique;
    }
}
