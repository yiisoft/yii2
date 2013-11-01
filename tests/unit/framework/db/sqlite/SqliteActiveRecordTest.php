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

	/**
	 * Some PDO implementations(e.g. cubrid) do not support boolean values.
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
		// sqlite will return empty string here but it would still
		// evaluate to false or null so we accept it
		$this->assertTrue(0 == $customer->status);

		$customers = Customer::find()->where(['status' => true])->all();
		$this->assertEquals(2, count($customers));

		$customers = Customer::find()->where(['status' => false])->all();
		$this->assertEquals(1, count($customers));
	}
}
