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

    public function testBatchInsert()
    {
        parent::testBatchInsert();

        $command = $this->getConnection()->createCommand();
        $command->batchInsert('bool_values',
            ['bool_col'], [
                [true],
                [false],
            ]
        );
        $this->assertEquals(2, $command->execute());
    }
}