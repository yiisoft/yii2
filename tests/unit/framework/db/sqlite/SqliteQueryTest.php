<?php
namespace yiiunit\framework\db\sqlite;

use yiiunit\framework\db\QueryTest;

class SqliteQueryTest extends QueryTest
{
    protected function setUp()
    {
        $this->driverName = 'sqlite';
        parent::setUp();
    }
}
