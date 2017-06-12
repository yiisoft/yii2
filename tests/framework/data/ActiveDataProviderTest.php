<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\data;

use yii\base\InvalidCallException;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\Item;
use yiiunit\data\ar\Order;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\framework\db\UnqueryableQueryMock;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @group data
 * @group db
 */
abstract class ActiveDataProviderTest extends DatabaseTestCase
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
        $this->assertCount(3, $orders);
        $this->assertInstanceOf(Order::className(), $orders[0]);
        $this->assertInstanceOf(Order::className(), $orders[1]);
        $this->assertInstanceOf(Order::className(), $orders[2]);
        $this->assertEquals([1, 2, 3], $provider->getKeys());

        $provider = new ActiveDataProvider([
            'query' => Order::find(),
            'pagination' => [
                'pageSize' => 2,
            ],
        ]);
        $orders = $provider->getModels();
        $this->assertCount(2, $orders);
    }

    public function testActiveRelation()
    {
        /* @var $customer Customer */
        $customer = Customer::findOne(2);
        $provider = new ActiveDataProvider([
            'query' => $customer->getOrders(),
        ]);
        $orders = $provider->getModels();
        $this->assertCount(2, $orders);
        $this->assertInstanceOf(Order::className(), $orders[0]);
        $this->assertInstanceOf(Order::className(), $orders[1]);
        $this->assertEquals([2, 3], $provider->getKeys());

        $provider = new ActiveDataProvider([
            'query' => $customer->getOrders(),
            'pagination' => [
                'pageSize' => 1,
            ],
        ]);
        $orders = $provider->getModels();
        $this->assertCount(1, $orders);
    }

    public function testActiveRelationVia()
    {
        /* @var $order Order */
        $order = Order::findOne(2);
        $provider = new ActiveDataProvider([
            'query' => $order->getItems(),
        ]);
        $items = $provider->getModels();
        $this->assertCount(3, $items);
        $this->assertInstanceOf(Item::className(), $items[0]);
        $this->assertInstanceOf(item::className(), $items[1]);
        $this->assertInstanceOf(Item::className(), $items[2]);
        $this->assertEquals([3, 4, 5], $provider->getKeys());

        $provider = new ActiveDataProvider([
            'query' => $order->getItems(),
            'pagination' => [
                'pageSize' => 2,
            ],
        ]);
        $items = $provider->getModels();
        $this->assertCount(2, $items);
    }

    public function testActiveRelationViaTable()
    {
        /* @var $order Order */
        $order = Order::findOne(1);
        $provider = new ActiveDataProvider([
            'query' => $order->getBooks(),
        ]);
        $items = $provider->getModels();
        $this->assertCount(2, $items);
        $this->assertInstanceOf(Item::className(), $items[0]);
        $this->assertInstanceOf(Item::className(), $items[1]);

        $provider = new ActiveDataProvider([
            'query' => $order->getBooks(),
            'pagination' => [
                'pageSize' => 1,
            ],
        ]);
        $items = $provider->getModels();
        $this->assertCount(1, $items);
    }

    public function testQuery()
    {
        $query = new Query();
        $provider = new ActiveDataProvider([
            'db' => $this->getConnection(),
            'query' => $query->from('order')->orderBy('id'),
        ]);
        $orders = $provider->getModels();
        $this->assertCount(3, $orders);
        $this->assertInternalType('array', $orders[0]);
        $this->assertEquals([0, 1, 2], $provider->getKeys());

        $query = new Query();
        $provider = new ActiveDataProvider([
            'db' => $this->getConnection(),
            'query' => $query->from('order'),
            'pagination' => [
                'pageSize' => 2,
            ],
        ]);
        $orders = $provider->getModels();
        $this->assertCount(2, $orders);
    }

    public function testRefresh()
    {
        $query = new Query();
        $provider = new ActiveDataProvider([
            'db' => $this->getConnection(),
            'query' => $query->from('order')->orderBy('id'),
        ]);
        $this->assertCount(3, $provider->getModels());

        $provider->getPagination()->pageSize = 2;
        $this->assertCount(3, $provider->getModels());
        $provider->refresh();
        $this->assertCount(2, $provider->getModels());
    }

    public function testPaginationBeforeModels()
    {
        $query = new Query();
        $provider = new ActiveDataProvider([
            'db' => $this->getConnection(),
            'query' => $query->from('order')->orderBy('id'),
        ]);
        $pagination = $provider->getPagination();
        $this->assertEquals(0, $pagination->getPageCount());
        $this->assertCount(3, $provider->getModels());
        $this->assertEquals(1, $pagination->getPageCount());

        $provider->getPagination()->pageSize = 2;
        $this->assertCount(3, $provider->getModels());
        $provider->refresh();
        $this->assertCount(2, $provider->getModels());
    }

    public function testDoesNotPerformQueryWhenHasNoModels()
    {
        $query = new UnqueryableQueryMock();
        $provider = new ActiveDataProvider([
            'db' => $this->getConnection(),
            'query' => $query->from('order')->where('0=1'),
        ]);
        $pagination = $provider->getPagination();
        $this->assertEquals(0, $pagination->getPageCount());

        try {
            $this->assertCount(0, $provider->getModels());
        } catch (InvalidCallException $exception) {
            $this->fail('An excessive models query was executed.');
        }

        $this->assertEquals(0, $pagination->getPageCount());
    }
}
