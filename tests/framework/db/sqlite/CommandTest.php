<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

/**
 * @group db
 * @group sqlite
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{
    protected $driverName = 'sqlite';

    public function testAutoQuoting()
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT `id`, `t`.`name` FROM `customer` t', $command->sql);
    }

    public function testAddDropPrimaryKey()
    {
        $this->markTestSkipped('SQLite does not support adding/dropping primary keys.');
    }

    public function testAddDropForeignKey()
    {
        $this->markTestSkipped('SQLite does not support adding/dropping foreign keys.');
    }

    public function testAddDropUnique()
    {
        $this->markTestSkipped('SQLite does not support adding/dropping unique constraints.');
    }

    public function testAddDropCheck()
    {
        $this->markTestSkipped('SQLite does not support adding/dropping check constraints.');
    }
}
