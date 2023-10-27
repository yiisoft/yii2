<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite\type;

use yii\db\sqlite\Schema;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * @group db
 * @group sqlite
 */
class BooleanTest extends DatabaseTestCase
{
    protected $driverName = 'sqlite';

    public function testBoolean()
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
                'bool_col_tinyint' => $schema->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN),
                'bool_col_bit' => $schema->createColumnSchemaBuilder('bit', 1),
            ]
        )->execute();

        // test type `boolean`
        $columnBoolColTinyint = $db->getTableSchema($tableName)->getColumn('bool_col_tinyint');
        $this->assertSame('boolean', $columnBoolColTinyint->phpType);

        $columnBoolColBit = $db->getTableSchema($tableName)->getColumn('bool_col_bit');
        $this->assertSame('boolean', $columnBoolColBit->phpType);

        // test value `false`
        $db->createCommand()->insert($tableName, ['bool_col_tinyint' => false, 'bool_col_bit' => false])->execute();
        $boolValues = $db->createCommand("SELECT * FROM $tableName WHERE id = 1")->queryOne();
        $this->assertEquals(0, $boolValues['bool_col_tinyint']);
        $this->assertEquals(0, $boolValues['bool_col_bit']);

        // test php typecast
        $phpTypeCastBoolColTinyint = $columnBoolColTinyint->phpTypecast($boolValues['bool_col_tinyint']);
        $this->assertFalse($phpTypeCastBoolColTinyint);

        $phpTypeCastBoolColBit = $columnBoolColBit->phpTypecast($boolValues['bool_col_bit']);
        $this->assertFalse($phpTypeCastBoolColBit);

        // test value `true`
        $db->createCommand()->insert($tableName, ['bool_col_tinyint' => true, 'bool_col_bit' => true])->execute();
        $boolValues = $db->createCommand("SELECT * FROM $tableName WHERE id = 2")->queryOne();
        $this->assertEquals(1, $boolValues['bool_col_tinyint']);
        $this->assertEquals(1, $boolValues['bool_col_bit']);

        // test php typecast
        $phpTypeCastBoolColTinyint = $columnBoolColTinyint->phpTypecast($boolValues['bool_col_tinyint']);
        $this->assertTrue($phpTypeCastBoolColTinyint);

        $phpTypeCastBoolColBit = $columnBoolColBit->phpTypecast($boolValues['bool_col_bit']);
        $this->assertTrue($phpTypeCastBoolColBit);
    }

    public function testBooleanWithValueInteger()
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
                'bool_col_tinyint' => $schema->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN),
                'bool_col_bit' => $schema->createColumnSchemaBuilder('bit', 1),
            ]
        )->execute();

        // test type `boolean`
        $columnBoolColTinyint = $db->getTableSchema($tableName)->getColumn('bool_col_tinyint');
        $this->assertSame('boolean', $columnBoolColTinyint->phpType);

        $columnBoolColBit = $db->getTableSchema($tableName)->getColumn('bool_col_bit');
        $this->assertSame('boolean', $columnBoolColBit->phpType);

        // test value `0`
        $db->createCommand()->insert($tableName, ['bool_col_tinyint' => 0, 'bool_col_bit' => 0])->execute();
        $boolValues = $db->createCommand("SELECT * FROM $tableName WHERE id = 1")->queryOne();
        $this->assertEquals(0, $boolValues['bool_col_tinyint']);
        $this->assertEquals(0, $boolValues['bool_col_bit']);

        // test php typecast
        $phpTypeCastBoolColTinyint = $columnBoolColTinyint->phpTypecast($boolValues['bool_col_tinyint']);
        $this->assertFalse($phpTypeCastBoolColTinyint);

        $phpTypeCastBoolColBit = $columnBoolColBit->phpTypecast($boolValues['bool_col_bit']);
        $this->assertFalse($phpTypeCastBoolColBit);

        // test value `1`
        $db->createCommand()->insert($tableName, ['bool_col_tinyint' => 1, 'bool_col_bit' => 1])->execute();
        $boolValues = $db->createCommand("SELECT * FROM $tableName WHERE id = 2")->queryOne();
        $this->assertEquals(1, $boolValues['bool_col_tinyint']);
        $this->assertEquals(1, $boolValues['bool_col_bit']);

        // test php typecast
        $phpTypeCastBoolColTinyint = $columnBoolColTinyint->phpTypecast($boolValues['bool_col_tinyint']);
        $this->assertTrue($phpTypeCastBoolColTinyint);

        $phpTypeCastBoolColBit = $columnBoolColBit->phpTypecast($boolValues['bool_col_bit']);
        $this->assertTrue($phpTypeCastBoolColBit);
    }

    public function testBooleanWithValueNegative()
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
                'bool_col_tinyint' => $schema->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN),
                'bool_col_bit' => $schema->createColumnSchemaBuilder('bit', 1),
            ]
        )->execute();

        // test type `boolean`
        $columnBoolColTinyint = $db->getTableSchema($tableName)->getColumn('bool_col_tinyint');
        $this->assertSame('boolean', $columnBoolColTinyint->phpType);

        $columnBoolColBit = $db->getTableSchema($tableName)->getColumn('bool_col_bit');
        $this->assertSame('boolean', $columnBoolColBit->phpType);

        // test value `-1`
        $db->createCommand()->insert($tableName, ['bool_col_tinyint' => -1, 'bool_col_bit' => -1])->execute();
        $boolValues = $db->createCommand("SELECT * FROM $tableName WHERE id = 1")->queryOne();

        $this->assertEquals(1, $boolValues['bool_col_tinyint']);
        $this->assertEquals(1, $boolValues['bool_col_bit']);

        // test php typecast
        $phpTypeCastBoolColTinyint = $columnBoolColTinyint->phpTypecast($boolValues['bool_col_tinyint']);
        $this->assertTrue($phpTypeCastBoolColTinyint);

        $phpTypeCastBoolColBit = $columnBoolColBit->phpTypecast($boolValues['bool_col_bit']);
        $this->assertTrue($phpTypeCastBoolColBit);
    }

    public function testBooleanWithValueNull()
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
                'bool_col_tinyint' => $schema->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN),
                'bool_col_bit' => $schema->createColumnSchemaBuilder('bit', 1),
            ]
        )->execute();

        // test type `boolean`
        $columnBoolColTinyint = $db->getTableSchema($tableName)->getColumn('bool_col_tinyint');
        $this->assertSame('boolean', $columnBoolColTinyint->phpType);

        $columnBoolColBit = $db->getTableSchema($tableName)->getColumn('bool_col_bit');
        $this->assertSame('boolean', $columnBoolColBit->phpType);

        // test value `null`
        $db->createCommand()->insert($tableName, ['bool_col_tinyint' => null, 'bool_col_bit' => null])->execute();
        $boolValues = $db->createCommand("SELECT * FROM $tableName WHERE id = 1")->queryOne();

        $this->assertNull($boolValues['bool_col_tinyint']);
        $this->assertNull($boolValues['bool_col_bit']);

        // test php typecast
        $phpTypeCastBoolColTinyint = $columnBoolColTinyint->phpTypecast($boolValues['bool_col_tinyint']);
        $this->assertNull($phpTypeCastBoolColTinyint);

        $phpTypeCastBoolColBit = $columnBoolColBit->phpTypecast($boolValues['bool_col_bit']);
        $this->assertNull($phpTypeCastBoolColBit);
    }

    public function testBooleanWithValueOverflow()
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
                'bool_col_tinyint' => $schema->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN),
                'bool_col_bit' => $schema->createColumnSchemaBuilder('bit', 1),
            ]
        )->execute();

        // test type `boolean`
        $columnBoolColTinyint = $db->getTableSchema($tableName)->getColumn('bool_col_tinyint');
        $this->assertSame('boolean', $columnBoolColTinyint->phpType);

        $columnBoolColBit = $db->getTableSchema($tableName)->getColumn('bool_col_bit');
        $this->assertSame('boolean', $columnBoolColBit->phpType);

        // test value `2`
        $db->createCommand()->insert($tableName, ['bool_col_tinyint' => 2, 'bool_col_bit' => 2])->execute();
        $boolValues = $db->createCommand("SELECT * FROM $tableName WHERE id = 1")->queryOne();

        $this->assertEquals(1, $boolValues['bool_col_tinyint']);
        $this->assertEquals(1, $boolValues['bool_col_bit']);

        // test php typecast
        $phpTypeCastBoolColTinyint = $columnBoolColTinyint->phpTypecast($boolValues['bool_col_tinyint']);
        $this->assertTrue($phpTypeCastBoolColTinyint);

        $phpTypeCastBoolColBit = $columnBoolColBit->phpTypecast($boolValues['bool_col_bit']);
        $this->assertTrue($phpTypeCastBoolColBit);
    }
}
