<?php
namespace yiiunit\framework\db\cubrid;

use yii\db\cubrid\ColumnSchemaBuilder;
use yii\db\Schema;
use \yiiunit\framework\db\ColumnSchemaBuilderTest as BaseColumnSchemaBuilderTest;

/**
 * ColumnSchemaBuilderTest tests ColumnSchemaBuilder for Cubrid
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
