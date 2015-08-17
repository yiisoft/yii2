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
 * ColumnSchemaBuilder is the schema builder for Postgres databases.
 *
 * @author Vasenin Matvey <vaseninm@gmail.com>
 * @since 2.0.6
 */
class ColumnSchemaBuilder extends AbstractColumnSchemaBuilder
{
    /**
     * Specify the comment for the column.
     * @param string $comment the comment
     * @return $this
     * @throws NotSupportedException this is not supported by SQLite
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
