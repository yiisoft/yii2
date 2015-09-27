<?php

namespace yiiunit\framework\db\mssql;

use yiiunit\framework\db\ActiveRecordTest;

/**
 * @group db
 * @group mssql
 */
class MssqlActiveRecordTest extends ActiveRecordTest
{
    protected $driverName = 'sqlsrv';

    public function testExplicitPkOnAutoIncrement()
    {
        $this->markTestSkipped('MSSQL does not support explicit value for an IDENTITY column.');
    }
}
