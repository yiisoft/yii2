<?php

namespace yiiunit\extensions\redis;

use yii\redis\ActiveQuery;
use yiiunit\data\ar\redis\ActiveRecord;
use yiiunit\data\ar\redis\Customer;
use yiiunit\data\ar\redis\OrderItem;
use yiiunit\data\ar\redis\Order;
use yiiunit\data\ar\redis\Item;
use yiiunit\data\ar\redis\OrderItemWithNullFK;
use yiiunit\data\ar\redis\OrderWithNullFK;
use yiiunit\framework\ar\ActiveRecordTestTrait;

/**
 * @group redis
 */
class ActiveRecordTest extends RedisTestCase
{
    use ActiveRecordTestTrait;

    public function getCustomerClass()
    {
        return Customer::className();
    }

    public function getItemClass()
    {
        return Item::className();
    }

    public function getOrderClass()
    {
        return Order::className();
    }

    public function getOrderItemClass()
    {
        return OrderItem::className();
    }

    public function getOrderWithNullFKClass()
    {
        return OrderWithNullFK::className();
    }

    public function getOrderItemWithNullFKmClass()
    {
        return OrderItemWithNullFK::className();
    }

    public function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();

        $customer = new Customer();
        $customer->setAttributes(['email' => 'user1@example.com', 'name' => 'user1', 'address' => 'address1', 'status' => 1, 'profile_id' => 1], false);
        $customer->save(false);
        $customer = new Customer();
        $customer->setAttributes(['email' => 'user2@example.com', 'name' => 'user2', 'address' => 'address2', 'status' => 1, 'profile_id' => null], false);
        $customer->save(false);
        $customer = new Customer();
        $customer->setAttributes(['email' => 'user3@example.com', 'name' => 'user3', 'address' => 'address3', 'status' => 2, 'profile_id' => 2], false);
        $customer->save(false);

//		INSERT INTO category (name) VALUES ('Books');
//		INSERT INTO category (name) VALUES ('Movies');

        $item = new Item();
        $item->setAttributes(['name' => 'Agile Web Application Development with Yii1.1 and PHP5', 'category_id' => 1], false);
        $item->save(false);
        $item = new Item();
        $item->setAttributes(['name' => 'Yii 1.1 Application Development Cookbook', 'category_id' => 1], false);
        $item->save(false);
        $item = new Item();
        $item->setAttributes(['name' => 'Ice Age', 'category_id' => 2], false);
        $item->save(false);
        $item = new Item();
        $item->setAttributes(['name' => 'Toy Story', 'category_id' => 2], false);
        $item->save(false);
        $item = new Item();
        $item->setAttributes(['name' => 'Cars', 'category_id' => 2], false);
        $item->save(false);

        $order = new Order();
        $order->setAttributes(['customer_id' => 1, 'created_at' => 1325282384, 'total' => 110.0], false);
        $order->save(false);
        $order = new Order();
        $order->setAttributes(['customer_id' => 2, 'created_at' => 1325334482, 'total' => 33.0], false);
        $order->save(false);
        $order = new Order();
        $order->setAttributes(['customer_id' => 2, 'created_at' => 1325502201, 'total' => 40.0], false);
        $order->save(false);

        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 1, 'item_id' => 1, 'quantity' => 1, 'subtotal' => 30.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 1, 'item_id' => 2, 'quantity' => 2, 'subtotal' => 40.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 4, 'quantity' => 1, 'subtotal' => 10.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 5, 'quantity' => 1, 'subtotal' => 15.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 3, 'quantity' => 1, 'subtotal' => 8.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 3, 'item_id' => 2, 'quantity' => 1, 'subtotal' => 40.0], false);
        $orderItem->save(false);

        $order = new OrderWithNullFK();
        $order->setAttributes(['customer_id' => 1, 'created_at' => 1325282384, 'total' => 110.0], false);
        $order->save(false);
        $order = new OrderWithNullFK();
        $order->setAttributes(['customer_id' => 2, 'created_at' => 1325334482, 'total' => 33.0], false);
        $order->save(false);
        $order = new OrderWithNullFK();
        $order->setAttributes(['customer_id' => 2, 'created_at' => 1325502201, 'total' => 40.0], false);
        $order->save(false);

        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 1, 'item_id' => 1, 'quantity' => 1, 'subtotal' => 30.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 1, 'item_id' => 2, 'quantity' => 2, 'subtotal' => 40.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 4, 'quantity' => 1, 'subtotal' => 10.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 5, 'quantity' => 1, 'subtotal' => 15.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 3, 'quantity' => 1, 'subtotal' => 8.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 3, 'item_id' => 2, 'quantity' => 1, 'subtotal' => 40.0], false);
        $orderItem->save(false);

    }

    public function testFindEagerViaRelationPreserveOrder()
    {
        $this->markTestSkipped('Redis does not support orderBy.');
    }

    public function testFindEagerViaRelationPreserveOrderB()
    {
        $this->markTestSkipped('Redis does not support orderBy.');
    }

    /**
     * overridden because null values are not part of the asArray result in redis
     */
    public function testFindAsArray()
    {
        /* @var $customerClass \yii\db\ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        // asArray
        $customer = $customerClass::find()->where(['id' => 2])->asArray()->one();
        $this->assertEquals([
            'id' => 2,
            'email' => 'user2@example.com',
            'name' => 'user2',
            'address' => 'address2',
            'status' => 1,
        ], $customer);

        // find all asArray
        $customers = $customerClass::find()->asArray()->all();
        $this->assertEquals(3, count($customers));
        $this->assertArrayHasKey('id', $customers[0]);
        $this->assertArrayHasKey('name', $customers[0]);
        $this->assertArrayHasKey('email', $customers[0]);
        $this->assertArrayHasKey('address', $customers[0]);
        $this->assertArrayHasKey('status', $customers[0]);
        $this->assertArrayHasKey('id', $customers[1]);
        $this->assertArrayHasKey('name', $customers[1]);
        $this->assertArrayHasKey('email', $customers[1]);
        $this->assertArrayHasKey('address', $customers[1]);
        $this->assertArrayHasKey('status', $customers[1]);
        $this->assertArrayHasKey('id', $customers[2]);
        $this->assertArrayHasKey('name', $customers[2]);
        $this->assertArrayHasKey('email', $customers[2]);
        $this->assertArrayHasKey('address', $customers[2]);
        $this->assertArrayHasKey('status', $customers[2]);
    }

    public function testStatisticalFind()
    {
        // find count, sum, average, min, max, scalar
        $this->assertEquals(3, Customer::find()->count());
        $this->assertEquals(6, Customer::find()->sum('id'));
        $this->assertEquals(2, Customer::find()->average('id'));
        $this->assertEquals(1, Customer::find()->min('id'));
        $this->assertEquals(3, Customer::find()->max('id'));

        $this->assertEquals(6, OrderItem::find()->count());
        $this->assertEquals(7, OrderItem::find()->sum('quantity'));
    }

    public function testFindIndexBy()
    {
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        // indexBy
        $customers = Customer::find()->indexBy('name')/*->orderBy('id')*/->all();
        $this->assertEquals(3, count($customers));
        $this->assertTrue($customers['user1'] instanceof $customerClass);
        $this->assertTrue($customers['user2'] instanceof $customerClass);
        $this->assertTrue($customers['user3'] instanceof $customerClass);

        // indexBy callable
        $customers = Customer::find()->indexBy(function ($customer) {
            return $customer->id . '-' . $customer->name;
        })/*->orderBy('id')*/->all(); // TODO this test is duplicated because of missing orderBy support in redis
        $this->assertEquals(3, count($customers));
        $this->assertTrue($customers['1-user1'] instanceof $customerClass);
        $this->assertTrue($customers['2-user2'] instanceof $customerClass);
        $this->assertTrue($customers['3-user3'] instanceof $customerClass);
    }

    public function testFindLimit()
    {
        // TODO this test is duplicated because of missing orderBy support in redis
        /* @var $this TestCase|ActiveRecordTestTrait */
        // all()
        $customers = Customer::find()->all();
        $this->assertEquals(3, count($customers));

        $customers = Customer::find()/*->orderBy('id')*/->limit(1)->all();
        $this->assertEquals(1, count($customers));
        $this->assertEquals('user1', $customers[0]->name);

        $customers = Customer::find()/*->orderBy('id')*/->limit(1)->offset(1)->all();
        $this->assertEquals(1, count($customers));
        $this->assertEquals('user2', $customers[0]->name);

        $customers = Customer::find()/*->orderBy('id')*/->limit(1)->offset(2)->all();
        $this->assertEquals(1, count($customers));
        $this->assertEquals('user3', $customers[0]->name);

        $customers = Customer::find()/*->orderBy('id')*/->limit(2)->offset(1)->all();
        $this->assertEquals(2, count($customers));
        $this->assertEquals('user2', $customers[0]->name);
        $this->assertEquals('user3', $customers[1]->name);

        $customers = Customer::find()->limit(2)->offset(3)->all();
        $this->assertEquals(0, count($customers));

        // one()
        $customer = Customer::find()/*->orderBy('id')*/->one();
        $this->assertEquals('user1', $customer->name);

        $customer = Customer::find()/*->orderBy('id')*/->offset(0)->one();
        $this->assertEquals('user1', $customer->name);

        $customer = Customer::find()/*->orderBy('id')*/->offset(1)->one();
        $this->assertEquals('user2', $customer->name);

        $customer = Customer::find()/*->orderBy('id')*/->offset(2)->one();
        $this->assertEquals('user3', $customer->name);

        $customer = Customer::find()->offset(3)->one();
        $this->assertNull($customer);
    }

    public function testFindEagerViaRelation()
    {
        /* @var $orderClass \yii\db\ActiveRecordInterface */
        $orderClass = $this->getOrderClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $orders = $orderClass::find()->with('items')/*->orderBy('id')*/->all(); // TODO this test is duplicated because of missing orderBy support in redis
        $this->assertEquals(3, count($orders));
        $order = $orders[0];
        $this->assertEquals(1, $order->id);
        $this->assertEquals(2, count($order->items));
        $this->assertEquals(1, $order->items[0]->id);
        $this->assertEquals(2, $order->items[1]->id);
    }

    public function testFindColumn()
    {
        // TODO this test is duplicated because of missing orderBy support in redis
        $this->assertEquals(['user1', 'user2', 'user3'], Customer::find()->column('name'));
        // TODO $this->assertEquals(['user3', 'user2', 'user1'], Customer::find()->orderBy(['name' => SORT_DESC])->column('name'));
    }

    // TODO test serial column incr

    public function testUpdatePk()
    {
        // updateCounters
        $pk = ['order_id' => 2, 'item_id' => 4];
        $orderItem = OrderItem::findOne($pk);
        $this->assertEquals(2, $orderItem->order_id);
        $this->assertEquals(4, $orderItem->item_id);

        $orderItem->order_id = 2;
        $orderItem->item_id = 10;
        $orderItem->save();

        $this->assertNull(OrderItem::findOne($pk));
        $this->assertNotNull(OrderItem::findOne(['order_id' => 2, 'item_id' => 10]));
    }

    public function testFilterWhere()
    {
        // should work with hash format
        $query = new ActiveQuery('dummy');
        $query->filterWhere([
            'id' => 0,
            'title' => '   ',
            'author_ids' => [],
        ]);
        $this->assertEquals(['id' => 0], $query->where);

        $query->andFilterWhere(['status' => null]);
        $this->assertEquals(['id' => 0], $query->where);

        $query->orFilterWhere(['name' => '']);
        $this->assertEquals(['id' => 0], $query->where);

        // should work with operator format
        $query = new ActiveQuery('dummy');
        $condition = ['like', 'name', 'Alex'];
        $query->filterWhere($condition);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['between', 'id', null, null]);
        $this->assertEquals($condition, $query->where);

        $query->orFilterWhere(['not between', 'id', null, null]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['in', 'id', []]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['not in', 'id', []]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['not in', 'id', []]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['like', 'id', '']);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['or like', 'id', '']);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['not like', 'id', '   ']);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['or not like', 'id', null]);
        $this->assertEquals($condition, $query->where);
    }

    public function testFilterWhereRecursively()
    {
        $query = new ActiveQuery('dummy');
        $query->filterWhere(['and', ['like', 'name', ''], ['like', 'title', ''], ['id' => 1], ['not', ['like', 'name', '']]]);
        $this->assertEquals(['and', ['id' => 1]], $query->where);
    }
}
