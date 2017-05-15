<?php

namespace yiiunit\framework\db\mysql;

/**
 * @group db
 * @group mysql
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{
    public $driverName = 'mysql';

    public function testAddDropCheck()
    {
        $this->markTestSkipped('MySQL does not support adding/dropping check constraints.');
    }

}
