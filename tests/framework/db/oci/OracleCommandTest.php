<?php
namespace yiiunit\framework\db\oci;

<<<<<<< HEAD
=======
use yii\db\Schema;
>>>>>>> master
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
<<<<<<< HEAD
=======

    public function testLastInsertId()
    {
        $db = $this->getConnection();

        $sql = 'INSERT INTO {{profile}}([[description]]) VALUES (\'non duplicate\')';
        $command = $db->createCommand($sql);
        $command->execute();
        $this->assertEquals(3, $db->getSchema()->getLastInsertID('profile_SEQ'));
    }
>>>>>>> master
}
