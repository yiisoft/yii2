<?php
namespace yiiunit\framework\db\mssql;

use yiiunit\framework\data\ActiveDataProviderTest;

/**
 * @group db
 * @group mssql
 * @group data
 */
class MssqlActiveDataProviderTest extends ActiveDataProviderTest
{
	public $driverName = 'sqlsrv';
}
