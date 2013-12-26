<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\data;

use yii\data\ActiveDataProvider;
use yii\db\Query;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\Item;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\data\ar\Order;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @group data
 * @group db
 */
class ActiveDataProviderTest extends DatabaseTestCase
{
	protected function setUp()
	{
		parent::setUp();
		ActiveRecord::$db = $this->getConnection();
	}

	public function testActiveQuery()
	{
		$provider = new ActiveDataProvider([
			'query' => Order::find()->orderBy('id'),
		]);
		$orders = $provider->getModels();
		$this->assertEquals(3, count($orders));
		$this->assertTrue($orders[0] instanceof Order);
		$this->assertTrue($orders[1] instanceof Order);
		$this->assertTrue($orders[2] instanceof Order);
		$this->assertEquals([1, 2, 3], $provider->getKeys());

		$provider = new ActiveDataProvider([
			'query' => Order::find(),
			'pagination' => [
				'pageSize' => 2,
			]
		]);
		$orders = $provider->getModels();
		$this->assertEquals(2, count($orders));
	}

	public function testActiveRelation()
	{
		/** @var Customer $customer */
		$customer = Customer::find(2);
		$provider = new ActiveDataProvider([
			'query' => $customer->getOrders(),
		]);
		$orders = $provider->getModels();
		$this->assertEquals(2, count($orders));
		$this->assertTrue($orders[0] instanceof Order);
		$this->assertTrue($orders[1] instanceof Order);
		$this->assertEquals([2, 3], $provider->getKeys());

		$provider = new ActiveDataProvider([
			'query' => $customer->getOrders(),
			'pagination' => [
				'pageSize' => 1,
			]
		]);
		$orders = $provider->getModels();
		$this->assertEquals(1, count($orders));
	}

	public function testActiveRelationVia()
	{
		/** @var Order $order */
		$order = Order::find(2);
		$provider = new ActiveDataProvider([
			'query' => $order->getItems(),
		]);
		$items = $provider->getModels();
		$this->assertEquals(3, count($items));
		$this->assertTrue($items[0] instanceof Item);
		$this->assertTrue($items[1] instanceof Item);
		$this->assertTrue($items[2] instanceof Item);
		$this->assertEquals([3, 4, 5], $provider->getKeys());

		$provider = new ActiveDataProvider([
			'query' => $order->getItems(),
			'pagination' => [
				'pageSize' => 2,
			]
		]);
		$items = $provider->getModels();
		$this->assertEquals(2, count($items));
	}

	public function testActiveRelationViaTable()
	{
		/** @var Order $order */
		$order = Order::find(1);
		$provider = new ActiveDataProvider([
			'query' => $order->getBooks(),
		]);
		$items = $provider->getModels();
		$this->assertEquals(2, count($items));
		$this->assertTrue($items[0] instanceof Item);
		$this->assertTrue($items[1] instanceof Item);

		$provider = new ActiveDataProvider([
			'query' => $order->getBooks(),
			'pagination' => [
				'pageSize' => 1,
			]
		]);
		$items = $provider->getModels();
		$this->assertEquals(1, count($items));
	}

	public function testQuery()
	{
		$query = new Query;
		$provider = new ActiveDataProvider([
			'db' => $this->getConnection(),
			'query' => $query->from('tbl_order')->orderBy('id'),
		]);
		$orders = $provider->getModels();
		$this->assertEquals(3, count($orders));
		$this->assertTrue(is_array($orders[0]));
		$this->assertEquals([0, 1, 2], $provider->getKeys());

		$query = new Query;
		$provider = new ActiveDataProvider([
			'db' => $this->getConnection(),
			'query' => $query->from('tbl_order'),
			'pagination' => [
				'pageSize' => 2,
			]
		]);
		$orders = $provider->getModels();
		$this->assertEquals(2, count($orders));
	}

	public function testRefresh()
	{
		$query = new Query;
		$provider = new ActiveDataProvider([
			'db' => $this->getConnection(),
			'query' => $query->from('tbl_order')->orderBy('id'),
		]);
		$this->assertEquals(3, count($provider->getModels()));

		$provider->getPagination()->pageSize = 2;
		$this->assertEquals(3, count($provider->getModels()));
		$provider->refresh();
		$this->assertEquals(2, count($provider->getModels()));
	}
	
	public function testPaginationBeforeModels()
	{
		$query = new Query;
		$provider = new ActiveDataProvider([
			'db' => $this->getConnection(),
			'query' => $query->from('tbl_order')->orderBy('id'),
		]);
		$pagination = $provider->getPagination();
		$this->assertEquals(0, $pagination->getPageCount());
		$this->assertCount(3, $provider->getModels());
		$this->assertEquals(1, $pagination->getPageCount());

		$provider->getPagination()->pageSize = 2;
		$this->assertEquals(3, count($provider->getModels()));
		$provider->refresh();
		$this->assertEquals(2, count($provider->getModels()));
	}
}
