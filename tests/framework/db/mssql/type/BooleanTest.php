<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\type;

use yii\db\mssql\Schema;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * @group db
 * @group mssql
 */
class BooleanTest extends DatabaseTestCase
{
    protected $driverName = 'sqlsrv';

    public function testBoolean(): void
    {
        $db = $this->getConnection(true);
        $schema = $db->getSchema();
        $tableName = '{{%boolean}}';

        if ($db->getTableSchema($tableName)) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        $db->createCommand()->createTable(
            $tableName,
            [
                'id' => $schema->createColumnSchemaBuilder(Schema::TYPE_PK),
                'bool_col' => $schema->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN),
            ]
        )->execute();

        // test type
        $column = $db->getTableSchema($tableName)->getColumn('bool_col');
        $this->assertSame('boolean', $column->phpType);

        // test value 0
        $db->createCommand()->insert($tableName, ['bool_col' => 0])->execute();
        $boolValue = $db->createCommand("SELECT bool_col FROM $tableName WHERE id = 1")->queryScalar();
        $this->assertEquals(0, $boolValue);

        // test php typecast
        $phpTypeCast = $column->phpTypecast($boolValue);
        $this->assertSame(false, $phpTypeCast);

        // test value 1
        $db->createCommand()->insert($tableName, ['bool_col' => 1])->execute();
        $boolValue = $db->createCommand("SELECT bool_col FROM $tableName WHERE id = 2")->queryScalar();
        $this->assertEquals(1, $boolValue);

        // test php typecast
        $phpTypeCast = $column->phpTypecast($boolValue);
        $this->assertSame(true, $phpTypeCast);

        // test value `false`
        $db->createCommand()->insert($tableName, ['bool_col' => false])->execute();
        $boolValue = $db->createCommand("SELECT bool_col FROM $tableName WHERE id = 3")->queryScalar();
        $this->assertEquals(0, $boolValue);

        // test php typecast
        $phpTypeCast = $column->phpTypecast($boolValue);
        $this->assertSame(false, $phpTypeCast);

        // test value `true`
        $db->createCommand()->insert($tableName, ['bool_col' => true])->execute();
        $boolValue = $db->createCommand("SELECT bool_col FROM $tableName WHERE id = 4")->queryScalar();
        $this->assertEquals(1, $boolValue);

        // test php typecast
        $phpTypeCast = $column->phpTypecast($boolValue);
        $this->assertSame(true, $phpTypeCast);

        // test value `null`
        $db->createCommand()->insert($tableName, ['bool_col' => null])->execute();
        $boolValue = $db->createCommand("SELECT bool_col FROM $tableName WHERE id = 5")->queryScalar();

        // test php typecast
        $phpTypeCast = $column->phpTypecast($boolValue);
        $this->assertNull($phpTypeCast);
    }
}
