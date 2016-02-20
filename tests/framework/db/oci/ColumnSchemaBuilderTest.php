<?php
namespace yiiunit\framework\db\oci;

use yii\db\oci\ColumnSchemaBuilder;
use yii\db\Schema;
use \yiiunit\framework\db\ColumnSchemaBuilderTest as BaseColumnSchemaBuilderTest;

/**
 * ColumnSchemaBuilderTest tests ColumnSchemaBuilder for Oracle
 */
class ColumnSchemaBuilderTest extends BaseColumnSchemaBuilderTest
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