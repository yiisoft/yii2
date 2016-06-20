<?php

namespace yiiunit\framework\db\pgsql;

use yii\db\ColumnSchemaBuilder;
use yii\db\Schema;

/**
 * ColumnSchemaBuilderTest tests ColumnSchemaBuilder for Oracle
 * @group db
 * @group pgsql
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
