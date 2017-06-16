<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

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
