<?php
namespace yiiunit\framework\db\sqlite;

use yiiunit\framework\data\ActiveDataProviderTest;

/**
 * @group db
 * @group sqlite
 * @group data
 */
class SqliteActiveDataProviderTest extends ActiveDataProviderTest
{
    public $driverName = 'sqlite';
}
