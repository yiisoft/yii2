<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\sqlite;

use yii\base\NotSupportedException;
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
        switch ($this->getTypeCategory()) {
            case self::CATEGORY_PK:
                $format = '{type}{check}';
                break;
            case self::CATEGORY_NUMERIC:
                $format = '{type}{length}{unsigned}{notnull}{unique}{check}{default}';
                break;
            default:
                $format = '{type}{length}{notnull}{unique}{check}{default}';
        }

        return $this->buildCompleteString($format);
    }

    /**
     * @inheritdoc
     * @param string $comment
     * @throws NotSupportedException
     */
    public function comment($comment)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * @inheritdoc
     */
    protected function buildCommentString()
    {
        return '';
    }
}
