<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\data;

use yii\data\ActiveDataProvider;
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
			'query' => Order::find(),
		));
		$orders = $provider->getItems();
		$this->assertEquals(3, count($orders));

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


	}
}
