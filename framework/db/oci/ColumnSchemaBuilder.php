<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\oci;

use yii\db\ColumnSchemaBuilder as AbstractColumnSchemaBuilder;

/**
 * ColumnSchemaBuilder is the schema builder for Oracle databases.
 *
 * @author Vasenin Matvey <vaseninm@gmail.com>
 * @author Chris Harris <chris@buckshotsoftware.com>
 * @since 2.0.6
 */
class ColumnSchemaBuilder extends AbstractColumnSchemaBuilder
{
    /**
     * @inheritdoc
     */
    protected function buildUnsignedString()
    {
        return $this->isUnsigned ? ' UNSIGNED' : '';
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        switch ($this->getTypeCategory()) {
            case self::CATEGORY_PK:
                $format = '{type}{length}{check}{append}';
                break;
            case self::CATEGORY_NUMERIC:
                $format = '{type}{length}{unsigned}{default}{notnull}{check}{append}';
                break;
            default:
                $format = '{type}{length}{default}{notnull}{check}{append}';
        }

        return $this->buildCompleteString($format);
    }
}
