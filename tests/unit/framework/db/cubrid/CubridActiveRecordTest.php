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

	/**
	 * cubrid PDO does not support boolean values.
	 * Make sure this does not affect AR layer.
	 */
	public function testBooleanAttribute()
	{
		$customer = new Customer();
		$customer->name = 'boolean customer';
		$customer->email = 'mail@example.com';
		$customer->status = true;
		$customer->save(false);

		$customer->refresh();
		$this->assertEquals(1, $customer->status);

		$customer->status = false;
		$customer->save(false);

		$customer->refresh();
		$this->assertEquals(0, $customer->status);

		$customers = Customer::find()->where(array('status' => true))->all();
		$this->assertEquals(2, count($customers));

		$customers = Customer::find()->where(array('status' => false))->all();
		$this->assertEquals(1, count($customers));
	}
}
