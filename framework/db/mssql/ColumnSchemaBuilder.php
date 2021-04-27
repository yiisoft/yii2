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
 * @author Chris Harris <chris@buckshotsoftware.com>
 * @since 2.0.8
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
        switch ($this->getTypeCategory()) {
            case self::CATEGORY_PK:
                $format = '{type}{check}{comment}{append}';
                break;
            default:
                $format = $this->format;
        }

        return $this->buildCompleteString($format);
    }

    /**
     * Changes default format string to MSSQL ALTER COMMAND
     */
    public function isAlterColumn()
    {
        $this->format = '{type}{length}{notnull}{append}';
    }

    /**
     * Getting the `Default` value for constraint
     * @return string|Expression|null
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
     * @return string|null
     */
    public function getCheckValue()
    {
        return $this->check !== null ? "{$this->check}" : null;
    }

    /**
     * @return bool
     */
    public function isUnique()
    {
        return $this->isUnique;
    }
}
