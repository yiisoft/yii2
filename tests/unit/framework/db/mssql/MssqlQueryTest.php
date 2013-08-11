<?php

namespace yiiunit\framework\db\mssql;

use yiiunit\framework\db\QueryTest;

class MssqlQueryTest extends QueryTest
{
    public function setUp()
    {
        $this->driverName = 'sqlsrv';
        parent::setUp();
    }
}
