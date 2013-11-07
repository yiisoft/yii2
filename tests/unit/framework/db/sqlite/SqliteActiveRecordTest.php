<?php
namespace yiiunit\framework\db\sqlite;

use yiiunit\data\ar\Customer;
use yiiunit\framework\db\ActiveRecordTest;

/**
 * @group db
 * @group sqlite
 */
class SqliteActiveRecordTest extends ActiveRecordTest
{
	protected $driverName = 'sqlite';
}
