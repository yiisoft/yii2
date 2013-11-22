<?php
namespace yiiunit\framework\db\cubrid;

use yiiunit\data\ar\Customer;
use yiiunit\framework\db\ActiveRecordTest;

/**
 * @group db
 * @group cubrid
 */
class CubridActiveRecordTest extends ActiveRecordTest
{
	public $driverName = 'cubrid';
}
