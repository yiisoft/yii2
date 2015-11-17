<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mysql;

use yii\db\ColumnSchemaBuilder as AbstractColumnSchemaBuilder;

/**
 * ColumnSchemaBuilder is the schema builder for MySQL databases.
 *
 * @author Sergey Kasatkin <spam@onsky.ru>
 * @since 2.0.7
 */
class ColumnSchemaBuilder extends AbstractColumnSchemaBuilder
{
    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return
            $this->type .
            $this->buildLengthString() .
            $this->buildDefaultString() .
            $this->buildNotNullString() .
            $this->buildCheckString() .
            $this->buildCommentString();
    }
}
