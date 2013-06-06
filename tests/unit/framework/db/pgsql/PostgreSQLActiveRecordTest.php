<?php

namespace yiiunit\framework\db\pgsql;

use yiiunit\framework\db\ActiveRecordTest;

class PostgreSQLActiveRecordTest extends ActiveRecordTest
{
    protected function setUp()
    {
        $this->driverName = 'pgsql';
        parent::setUp();
    }
}
