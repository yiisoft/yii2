<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql\type;

use yii\db\mysql\Schema;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * @group db
 * @group mysql
 */
class TinyIntTest extends DatabaseTestCase
{
    protected $driverName = 'mysql';

    public function testTinyIntUnsigned()
    {
        $db = $this->getConnection(true);
        $schema = $db->getSchema();
        $tableName = '{{%tinyint}}';

        if ($db->getTableSchema($tableName)) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        $db->createCommand()->createTable(
            $tableName,
            [
                'id' => $schema->createColumnSchemaBuilder(Schema::TYPE_PK),
                'state' => 'tinyint(1) unsigned DEFAULT 0',
            ]
        )->execute();

        // test type `integer`
        $column = $db->getTableSchema($tableName)->getColumn('state');

        // test type `boolean`
        $column = $db->getTableSchema($tableName)->getColumn('state');
        $this->assertSame('boolean', $column->phpType);

        $db->createCommand()->insert($tableName, ['state' => 255])->execute();
        $this->assertTrue($column->phpTypecast('255'));
    }
}
