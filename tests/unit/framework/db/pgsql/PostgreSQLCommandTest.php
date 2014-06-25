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
}