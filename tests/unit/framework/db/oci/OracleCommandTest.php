<?php
namespace yiiunit\framework\db\oci;

use yiiunit\framework\db\CommandTest;

/**
 * @group db
 * @group oci
 */
class OracleCommandTest extends CommandTest
{
    protected $driverName = 'oci';

    public function testAutoQuoting()
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT "id", "t"."name" FROM "customer" t', $command->sql);
    }
}
