<?php

namespace yiiunit\framework\db\mssql;

class MssqlActiveRecordTest extends \yiiunit\framework\db\ActiveRecordTest
{
    protected function setUp()
    {
        $this->driverName = 'sqlsrv';
        parent::setUp();
    }
}
