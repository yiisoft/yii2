<?php

namespace yiiunit\framework\db\mssql;

class MssqlActiveRecordTest extends \yiiunit\framework\db\ActiveRecordTest
{
    public function setUp()
    {
        $this->driverName = 'sqlsrv';
        parent::setUp();
    }
}
