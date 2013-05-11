<?php

namespace yiiunit\framework\db\mssql;

class MssqlQueryTest extends \yiiunit\framework\db\QueryTest
{
    public function setUp()
    {
        $this->driverName = 'sqlsrv';
        parent::setUp();
    }
}
