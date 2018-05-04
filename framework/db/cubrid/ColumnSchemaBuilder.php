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
     * {@inheritdoc}
     */
    protected function buildUnsignedString()
    {
        return $this->isUnsigned ? ' UNSIGNED' : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function buildAfterString()
    {
        return $this->after !== null ?
            ' AFTER ' . $this->db->quoteColumnName($this->after) :
            '';
    }

    /**
     * {@inheritdoc}
     */
    protected function buildFirstString()
    {
        return $this->isFirst ? ' FIRST' : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function buildCommentString()
    {
        return $this->comment !== null ? ' COMMENT ' . $this->db->quoteValue($this->comment) : '';
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        switch ($this->getTypeCategory()) {
            case self::CATEGORY_PK:
                $format = '{type}{check}{comment}{append}{pos}';
                break;
            case self::CATEGORY_NUMERIC:
                $format = '{type}{length}{unsigned}{notnull}{unique}{default}{check}{comment}{append}{pos}';
                break;
            default:
                $format = '{type}{length}{notnull}{unique}{default}{check}{comment}{append}{pos}';
        }

        return $this->buildCompleteString($format);
    }
}
