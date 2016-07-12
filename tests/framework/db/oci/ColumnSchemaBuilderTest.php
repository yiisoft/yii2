<?php

namespace yiiunit\framework\db\oci;

use yii\db\oci\ColumnSchemaBuilder;
use yii\db\Schema;

/**
 * ColumnSchemaBuilderTest tests ColumnSchemaBuilder for Oracle
 * @group db
 * @group oci
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

    /**
     * @return array
     */
    public function typesProvider()
    {
        return [
            ['integer UNSIGNED', Schema::TYPE_INTEGER, null, [
                ['unsigned'],
            ]],
            ['integer(10) UNSIGNED', Schema::TYPE_INTEGER, 10, [
                ['unsigned'],
            ]],
        ];
    }
}
