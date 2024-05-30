<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use yii\db\mysql\ColumnSchemaBuilder;
use yii\db\mysql\Schema;

/**
 * ColumnSchemaBuilderTest tests ColumnSchemaBuilder for MySQL.
 * @group db
 * @group mysql
 */
class ColumnSchemaBuilderTest extends \yiiunit\framework\db\ColumnSchemaBuilderTest
{
    protected $driverName = 'mysql';

    /**
     * @param string $type
     * @param int $length
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
                ['comment', 'test'],
            ]],
            // https://github.com/yiisoft/yii2/issues/11945 # TODO: real test against database
            ['string(50) NOT NULL COMMENT \'Property name\' COLLATE ascii_general_ci', Schema::TYPE_STRING, 50, [
                ['comment', 'Property name'],
                ['append', 'COLLATE ascii_general_ci'],
                ['notNull']
            ]],
        ];
    }
}
