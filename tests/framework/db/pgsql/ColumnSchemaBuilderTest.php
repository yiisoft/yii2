<?php
namespace yiiunit\framework\db\pgsql;

use yii\db\pgsql\ColumnSchemaBuilder;
use yii\db\Schema;
use \yiiunit\framework\db\ColumnSchemaBuilderTest as BaseColumnSchemaBuilderTest;

/**
 * ColumnSchemaBuilderTest tests ColumnSchemaBuilder for PostgreSQL
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
    public function unsignedProvider()
    {
        return [
            ['integer', Schema::TYPE_INTEGER, null, [
                ['unsigned'],
            ]],
            ['integer(10)', Schema::TYPE_INTEGER, 10, [
                ['unsigned'],
            ]],
        ];
    }
}