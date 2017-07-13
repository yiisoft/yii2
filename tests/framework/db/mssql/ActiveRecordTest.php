<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

/**
 * @group db
 * @group mssql
 */
class ActiveRecordTest extends \yiiunit\framework\db\ActiveRecordTest
{
    public $driverName = 'sqlsrv';

    public function testExplicitPkOnAutoIncrement()
    {
        $this->markTestSkipped('MSSQL does not support explicit value for an IDENTITY column.');
    }
}
