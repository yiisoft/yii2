<?php
namespace yii\tests\unit\framework\db\pgsql;

use yiiunit\framework\db\CommandTest;

/**
 * @group db
 * @group pgsql
 */
class PostgreSQLCommandTest extends CommandTest
{
    public $driverName = 'pgsql';

    public function testAutoQuoting()
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT "id", "t"."name" FROM "customer" t', $command->sql);
    }

    public function testBooleanValuesInsert()
    {
        $db = $this->getConnection();
        $command = $db->createCommand();
        $command->insert('bool_values', ['bool_col' => true]);
        $this->assertEquals(1, $command->execute());

        $command = $db->createCommand();
        $command->insert('bool_values', ['bool_col' => false]);
        $this->assertEquals(1, $command->execute());

        $command = $db->createCommand('SELECT COUNT(*) FROM "bool_values" WHERE bool_col = TRUE;');
        $this->assertEquals(1, $command->queryScalar());
        $command = $db->createCommand('SELECT COUNT(*) FROM "bool_values" WHERE bool_col = FALSE;');
        $this->assertEquals(1, $command->queryScalar());
    }

    public function testBooleanValuesBatchInsert()
    {
        $db = $this->getConnection();
        $command = $db->createCommand();
        $command->batchInsert('bool_values',
            ['bool_col'], [
                [true],
                [false],
            ]
        );
        $this->assertEquals(2, $command->execute());

        $command = $db->createCommand('SELECT COUNT(*) FROM "bool_values" WHERE bool_col = TRUE;');
        $this->assertEquals(1, $command->queryScalar());
        $command = $db->createCommand('SELECT COUNT(*) FROM "bool_values" WHERE bool_col = FALSE;');
        $this->assertEquals(1, $command->queryScalar());
    }
}