<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\cubrid;

use yii\db\ColumnSchemaBuilder as AbstractColumnSchemaBuilder;

/**
 * ColumnSchemaBuilder is the schema builder for Cubrid databases.
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
    protected function buildCommentString()
    {
        return empty($this->comment)
            ? ''
            : ' COMMENT ' . $this->db->quoteValue($this->comment);
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
            $format .= '{unique}{default}';
        }

        $format .= '{check}{comment}{pos}{append}';
        return $this->buildCompleteString($format);
    }
}
