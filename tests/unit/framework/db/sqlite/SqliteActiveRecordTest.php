<?php

namespace yiiunit\framework\db\sqlite;

class SqliteActiveRecordTest extends \yiiunit\framework\db\ActiveRecordTest
{
    protected function setUp()
    {
        $this->driverName = 'sqlite';
        parent::setUp();
    }
}
