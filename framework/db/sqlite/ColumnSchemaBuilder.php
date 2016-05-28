<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\sqlite;

use yii\db\ColumnSchemaBuilder as AbstractColumnSchemaBuilder;

/**
 * ColumnSchemaBuilder is the schema builder for Sqlite databases.
 *
 * @author Chris Harris <chris@buckshotsoftware.com>
 * @since 2.0.8
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
        $format = '{type}{length}';
        if ($this->getTypeCategory() === self::CATEGORY_NUMERIC) {
            $format .= '{unsigned}';
        }
        $format .= '{notnull}';

        if ($this->primaryKey) {
            $format .= '{primarykey}';
        } else {
            $format .= '{unique}';
        }

        $format .= '{check}{default}{append}';
        return $this->buildCompleteString($format);
    }
}
