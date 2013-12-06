<?php
namespace yiiunit\framework\db;

use yii\db\ActiveQuery;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\NullValues;
use yiiunit\data\ar\OrderItem;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\Item;
use yiiunit\framework\ar\ActiveRecordTestTrait;

/**
 * @group db
 * @group mysql
 */
class ActiveRecordTest extends DatabaseTestCase
{
	use ActiveRecordTestTrait;

	protected function setUp()
	{
		parent::setUp();
		ActiveRecord::$db = $this->getConnection();
	}

	public function callCustomerFind($q = null)	 { return Customer::find($q); }
	public function callOrderFind($q = null)     { return Order::find($q); }
	public function callOrderItemFind($q = null) { return OrderItem::find($q); }
	public function callItemFind($q = null)      { return Item::find($q); }

	public function getCustomerClass() { return Customer::className(); }
	public function getItemClass() { return Item::className(); }
	public function getOrderClass() { return Order::className(); }
	public function getOrderItemClass() { return OrderItem::className(); }

	public function testCustomColumns()
	{
		// find custom column
		$customer = $this->callCustomerFind()->select(['*', '(status*2) AS status2'])
			->where(['name' => 'user3'])->one();
		$this->assertEquals(3, $customer->id);
		$this->assertEquals(4, $customer->status2);
	}

	public function testStatisticalFind()
	{
		// find count, sum, average, min, max, scalar
		$this->assertEquals(3, $this->callCustomerFind()->count());
		$this->assertEquals(2, $this->callCustomerFind()->where('id=1 OR id=2')->count());
		$this->assertEquals(6, $this->callCustomerFind()->sum('id'));
		$this->assertEquals(2, $this->callCustomerFind()->average('id'));
		$this->assertEquals(1, $this->callCustomerFind()->min('id'));
		$this->assertEquals(3, $this->callCustomerFind()->max('id'));
		$this->assertEquals(3, $this->callCustomerFind()->select('COUNT(*)')->scalar());
	}

	public function testFindScalar()
	{
		// query scalar
		$customerName = $this->callCustomerFind()->where(array('id' => 2))->select('name')->scalar();
		$this->assertEquals('user2', $customerName);
	}

	public function testFindColumn()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		$this->assertEquals(['user1', 'user2', 'user3'], Customer::find()->select('name')->column());
		$this->assertEquals(['user3', 'user2', 'user1'], Customer::find()->orderBy(['name' => SORT_DESC])->select('name')->column());
	}

	public function testFindBySql()
	{
		// find one
		$customer = Customer::findBySql('SELECT * FROM tbl_customer ORDER BY id DESC')->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user3', $customer->name);

		// find all
		$customers = Customer::findBySql('SELECT * FROM tbl_customer')->all();
		$this->assertEquals(3, count($customers));

		// find with parameter binding
		$customer = Customer::findBySql('SELECT * FROM tbl_customer WHERE id=:id', [':id' => 2])->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);
	}

	public function testFindLazyViaTable()
	{
		/** @var Order $order */
		$order = Order::find(1);
		$this->assertEquals(1, $order->id);
		$this->assertEquals(2, count($order->books));
		$this->assertEquals(1, $order->items[0]->id);
		$this->assertEquals(2, $order->items[1]->id);

		$order = Order::find(2);
		$this->assertEquals(2, $order->id);
		$this->assertEquals(0, count($order->books));
	}

	public function testFindEagerViaTable()
	{
		$orders = Order::find()->with('books')->orderBy('id')->all();
		$this->assertEquals(3, count($orders));

		$order = $orders[0];
		$this->assertEquals(1, $order->id);
		$this->assertEquals(2, count($order->books));
		$this->assertEquals(1, $order->books[0]->id);
		$this->assertEquals(2, $order->books[1]->id);

		$order = $orders[1];
		$this->assertEquals(2, $order->id);
		$this->assertEquals(0, count($order->books));

		$order = $orders[2];
		$this->assertEquals(3, $order->id);
		$this->assertEquals(1, count($order->books));
		$this->assertEquals(2, $order->books[0]->id);

		// https://github.com/yiisoft/yii2/issues/1402
		$orders = Order::find()->with('books')->orderBy('id')->asArray()->all();
		$this->assertEquals(3, count($orders));

		$order = $orders[0];
		$this->assertTrue(is_array($order));
		$this->assertEquals(1, $order['id']);
		$this->assertEquals(2, count($order['books']));
		$this->assertEquals(1, $order['books'][0]['id']);
		$this->assertEquals(2, $order['books'][1]['id']);
	}

	public function testStoreNull()
	{
		$record = new NullValues();
		$this->assertNull($record->var1);
		$this->assertNull($record->var2);
		$this->assertNull($record->var3);
		$this->assertNull($record->stringcol);

		$record->id = 1;

		$record->var1 = 123;
		$record->var2 = 456;
		$record->var3 = 789;
		$record->stringcol = 'hello!';

		$record->save(false);
		$this->assertTrue($record->refresh());

		$this->assertEquals(123, $record->var1);
		$this->assertEquals(456, $record->var2);
		$this->assertEquals(789, $record->var3);
		$this->assertEquals('hello!', $record->stringcol);

		$record->var1 = null;
		$record->var2 = null;
		$record->var3 = null;
		$record->stringcol = null;

		$record->save(false);
		$this->assertTrue($record->refresh());

		$this->assertNull($record->var1);
		$this->assertNull($record->var2);
		$this->assertNull($record->var3);
		$this->assertNull($record->stringcol);

		$record->var1 = 0;
		$record->var2 = 0;
		$record->var3 = 0;
		$record->stringcol = '';

		$record->save(false);
		$this->assertTrue($record->refresh());

		$this->assertEquals(0, $record->var1);
		$this->assertEquals(0, $record->var2);
		$this->assertEquals(0, $record->var3);
		$this->assertEquals('', $record->stringcol);
	}

	public function testStoreEmpty()
	{
		$record = new NullValues();
		$record->id = 1;

		// this is to simulate empty html form submission
		$record->var1 = '';
		$record->var2 = '';
		$record->var3 = '';
		$record->stringcol = '';

		$record->save(false);
		$this->assertTrue($record->refresh());

		// https://github.com/yiisoft/yii2/commit/34945b0b69011bc7cab684c7f7095d837892a0d4#commitcomment-4458225
		$this->assertTrue($record->var1 === $record->var2);
		$this->assertTrue($record->var2 === $record->var3);
	}

	public function testIsPrimaryKey()
	{
		$this->assertFalse(Customer::isPrimaryKey([]));
		$this->assertTrue(Customer::isPrimaryKey(['id']));
		$this->assertFalse(Customer::isPrimaryKey(['id', 'name']));
		$this->assertFalse(Customer::isPrimaryKey(['name']));
		$this->assertFalse(Customer::isPrimaryKey(['name', 'email']));

		$this->assertFalse(OrderItem::isPrimaryKey([]));
		$this->assertFalse(OrderItem::isPrimaryKey(['order_id']));
		$this->assertFalse(OrderItem::isPrimaryKey(['item_id']));
		$this->assertFalse(OrderItem::isPrimaryKey(['quantity']));
		$this->assertFalse(OrderItem::isPrimaryKey(['quantity', 'subtotal']));
		$this->assertTrue(OrderItem::isPrimaryKey(['order_id', 'item_id']));
		$this->assertFalse(OrderItem::isPrimaryKey(['order_id', 'item_id', 'quantity']));
	}
}
