<?php

namespace yiiunit\framework\db\cubrid;

use yii\db\cubrid\ColumnSchemaBuilder;
use yii\db\Schema;

/**
 * ColumnSchemaBuilderTest tests ColumnSchemaBuilder for Cubrid
 * @group db
 * @group cubrid
 */
class ColumnSchemaBuilderTest extends \yiiunit\framework\db\ColumnSchemaBuilderTest
{
    public $driverName = 'cubrid';

    /**
     * @param string $type
     * @param integer $length
     * @return ColumnSchemaBuilder
     */
    public function getColumnSchemaBuilder($type, $length = null)
    {
        return new ColumnSchemaBuilder($type, $length, $this->getConnection());
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
            ['integer(10) COMMENT \'test\'', Schema::TYPE_INTEGER, 10, [
                ['comment', 'test']
            ]],
        ];
    }
}
