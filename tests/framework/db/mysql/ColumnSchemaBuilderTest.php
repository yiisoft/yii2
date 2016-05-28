<?php
namespace yiiunit\framework\db\mysql;

use yii\db\mysql\ColumnSchemaBuilder;
use yii\db\Schema;
use yiiunit\framework\db\ColumnSchemaBuilderTest as BaseColumnSchemaBuilderTest;

/**
 * ColumnSchemaBuilderTest tests ColumnSchemaBuilder for MySQL
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
            ['string(32) NOT NULL UNIQUE', Schema::TYPE_STRING, 32, [
                ['unsigned'],
                ['notNull'],
                ['unique'],
            ]],
            [
                'integer UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
                Schema::TYPE_INTEGER, null,
                [
                    ['primaryKey'],
                    ['unsigned']
                ]
            ],
            ['string(32) NOT NULL PRIMARY KEY', Schema::TYPE_STRING, 32, [
                ['primaryKey']
            ]],
        ];
    }
}
