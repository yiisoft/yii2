<?php
namespace yiiunit\framework\db\pgsql;

use yiiunit\framework\data\ActiveDataProviderTest;

/**
 * @group db
 * @group pgsql
 * @group data
 */
class PostgreSQLActiveDataProviderTest extends ActiveDataProviderTest
{
    public $driverName = 'pgsql';
}
