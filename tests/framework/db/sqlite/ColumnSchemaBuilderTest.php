<?php
namespace yiiunit\framework\db\sqlite;

use yii\db\sqlite\ColumnSchemaBuilder;
use yii\db\Schema;
use \yiiunit\framework\db\ColumnSchemaBuilderTest as BaseColumnSchemaBuilderTest;

/**
 * ColumnSchemaBuilderTest tests ColumnSchemaBuilder for SQLite
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
            ['integer UNSIGNED', Schema::TYPE_INTEGER, null, [
                ['unsigned'],
            ]],
            ['integer(10) UNSIGNED', Schema::TYPE_INTEGER, 10, [
                ['unsigned'],
            ]],
        ];
    }
}