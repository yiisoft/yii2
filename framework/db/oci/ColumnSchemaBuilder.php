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
    protected function buildAfterString()
    {
        return empty($this->after)
            ? ''
            : ' AFTER ' . $this->db->quoteColumnName($this->after);
    }

    /**
     * @inheritdoc
     */
    protected function buildFirstString()
    {
        return $this->isFirst ? ' FIRST' : '';
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
            $format .= '{default}';
        }

        $format .= '{check}{pos}{append}';
        return $this->buildCompleteString($format);
    }
}
