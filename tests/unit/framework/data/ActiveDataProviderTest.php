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
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\data\ar\Order;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
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
		$provider = new ActiveDataProvider(array(
			'query' => Order::find()->orderBy('id'),
		));
		$orders = $provider->getItems();
		$this->assertEquals(3, count($orders));
		$this->assertTrue($orders[0] instanceof Order);
		$this->assertEquals(array(1, 2, 3), $provider->getKeys());

		$provider = new ActiveDataProvider(array(
			'query' => Order::find(),
			'pagination' => array(
				'pageSize' => 2,
			)
		));
		$orders = $provider->getItems();
		$this->assertEquals(2, count($orders));
	}

	public function testQuery()
	{
		$query = new Query;
		$provider = new ActiveDataProvider(array(
			'query' => $query->from('tbl_order')->orderBy('id'),
		));
		$orders = $provider->getItems();
		$this->assertEquals(3, count($orders));
		$this->assertTrue(is_array($orders[0]));
		$this->assertEquals(array(0, 1, 2), $provider->getKeys());

		$query = new Query;
		$provider = new ActiveDataProvider(array(
			'query' => $query->from('tbl_order'),
			'pagination' => array(
				'pageSize' => 2,
			)
		));
		$orders = $provider->getItems();
		$this->assertEquals(2, count($orders));
	}
}
