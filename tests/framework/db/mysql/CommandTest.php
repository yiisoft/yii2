<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

/**
 * @group db
 * @group mysql
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{
    public $driverName = 'mysql';

    protected $upsertTestCharCast = 'CONVERT([[address]], CHAR)';

    public function testAddDropCheck()
    {
        $this->markTestSkipped('MySQL does not support adding/dropping check constraints.');
    }
}
