<?php
namespace yiiunit\framework\db\sqlite;

use yiiunit\framework\db\ActiveRecordTest;

class SqliteActiveRecordTest extends ActiveRecordTest
{
    protected function setUp()
    {
        $this->driverName = 'sqlite';
        parent::setUp();
    }
}
