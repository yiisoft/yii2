<?php
namespace yiiunit\framework\db\sqlite;

use yiiunit\framework\db\ActiveRecordTest;

/**
 * @group db
 * @group sqlite
 */
class SqliteActiveRecordTest extends ActiveRecordTest
{
    protected $driverName = 'sqlite';
}
