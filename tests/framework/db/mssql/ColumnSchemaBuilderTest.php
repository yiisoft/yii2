<?php

namespace yiiunit\framework\db\mssql;

use yii\db\ColumnSchemaBuilder;
use yii\db\Schema;

/**
 * ColumnSchemaBuilderTest tests ColumnSchemaBuilder for MSSQL
 * @group db
 * @group mssql
 */
class ColumnSchemaBuilderTest extends \yiiunit\framework\db\ColumnSchemaBuilderTest
{
    /**
     * @param string $type
     * @param integer $length
     * @return ColumnSchemaBuilder
     */
    public function getColumnSchemaBuilder($type, $length = null)
    {
        return new ColumnSchemaBuilder($type, $length);
    }
}
