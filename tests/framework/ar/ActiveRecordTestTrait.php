<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\ar;

use yii\base\Event;
use yii\db\ActiveRecordInterface;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\Order;
use yiiunit\TestCase;

/**
 * This trait provides unit tests shared by the different AR implementations.
 *
 * It is used directly in the unit tests for database active records in `tests/framework/db/ActiveRecordTest.php`
 * but also used in the test suites of `redis`, `mongodb`, `elasticsearch` and `sphinx` AR implementations
 * in the extensions.
 * @see https://github.com/yiisoft/yii2-redis/blob/a920547708c4a7091896923abc2499bc8c1c0a3b/tests/bootstrap.php#L17-L26
 */
trait ActiveRecordTestTrait
{
    /* @var $this TestCase */
    /**
     * This method should return the classname of Customer class.
     * @return string
     */
    abstract public function getCustomerClass();

    /**
     * This method should return the classname of Order class.
     * @return string
     */
    abstract public function getOrderClass();

    /**
     * This method should return the classname of OrderItem class.
     * @return string
     */
    abstract public function getOrderItemClass();

    /**
     * This method should return the classname of Item class.
     * @return string
     */
    abstract public function getItemClass();

    abstract public function getOrderWithNullFKClass();

    abstract public function getOrderItemWithNullFKmClass();

    /**
     * Can be overridden to do things after save().
     */
    public function afterSave()
    {
    }

    public function testFind()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        // find one
        $result = $customerClass::find();
        $this->assertInstanceOf('\\yii\\db\\ActiveQueryInterface', $result);
        $customer = $result->one();
        $this->assertInstanceOf($customerClass, $customer);

        // find all
        $customers = $customerClass::find()->all();
        $this->assertCount(3, $customers);
        $this->assertInstanceOf($customerClass, $customers[0]);
        $this->assertInstanceOf($customerClass, $customers[1]);
        $this->assertInstanceOf($customerClass, $customers[2]);

        // find by a single primary key
        $customer = $customerClass::findOne(2);
        $this->assertInstanceOf($customerClass, $customer);
        $this->assertEquals('user2', $customer->name);
        $customer = $customerClass::findOne(5);
        $this->assertNull($customer);
        $customer = $customerClass::findOne(['id' => [5, 6, 1]]);
        $this->assertInstanceOf($customerClass, $customer);
        $customer = $customerClass::find()->where(['id' => [5, 6, 1]])->one();
        $this->assertNotNull($customer);

        // find by column values
        $customer = $customerClass::findOne(['id' => 2, 'name' => 'user2']);
        $this->assertInstanceOf($customerClass, $customer);
        $this->assertEquals('user2', $customer->name);
        $customer = $customerClass::findOne(['id' => 2, 'name' => 'user1']);
        $this->assertNull($customer);
        $customer = $customerClass::findOne(['id' => 5]);
        $this->assertNull($customer);
        $customer = $customerClass::findOne(['name' => 'user5']);
        $this->assertNull($customer);

        // find by attributes
        $customer = $customerClass::find()->where(['name' => 'user2'])->one();
        $this->assertInstanceOf($customerClass, $customer);
        $this->assertEquals(2, $customer->id);

        // find by expression
        $customer = $customerClass::findOne(new Expression('[[id]] = :id', [':id' => 2]));
        $this->assertInstanceOf($customerClass, $customer);
        $this->assertEquals('user2', $customer->name);
        $customer = $customerClass::findOne(
            new Expression('[[id]] = :id AND [[name]] = :name', [':id' => 2, ':name' => 'user1'])
        );
        $this->assertNull($customer);
        $customer = $customerClass::findOne(new Expression('[[id]] = :id', [':id' => 5]));
        $this->assertNull($customer);
        $customer = $customerClass::findOne(new Expression('[[name]] = :name', [':name' => 'user5']));
        $this->assertNull($customer);

        // scope
        $this->assertCount(2, $customerClass::find()->active()->all());
        $this->assertEquals(2, $customerClass::find()->active()->count());
    }

    public function testFindAsArray()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        // asArray
        $customer = $customerClass::find()->where(['id' => 2])->asArray()->one();
        $this->assertEquals([
            'id' => 2,
            'email' => 'user2@example.com',
            'name' => 'user2',
            'address' => 'address2',
            'status' => 1,
            'profile_id' => null,
        ], $customer);

        // find all asArray
        $customers = $customerClass::find()->asArray()->all();
        $this->assertCount(3, $customers);
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

    public function testHasAttribute()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        $customer = new $customerClass();
        $this->assertTrue($customer->hasAttribute('id'));
        $this->assertTrue($customer->hasAttribute('email'));
        $this->assertFalse($customer->hasAttribute(0));
        $this->assertFalse($customer->hasAttribute(null));
        $this->assertFalse($customer->hasAttribute(42));

        $customer = $customerClass::findOne(1);
        $this->assertTrue($customer->hasAttribute('id'));
        $this->assertTrue($customer->hasAttribute('email'));
        $this->assertFalse($customer->hasAttribute(0));
        $this->assertFalse($customer->hasAttribute(null));
        $this->assertFalse($customer->hasAttribute(42));
    }

    public function testFindScalar()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        // query scalar
        $customerName = $customerClass::find()->where(['id' => 2])->scalar('name');
        $this->assertEquals('user2', $customerName);
        $customerName = $customerClass::find()->where(['status' => 2])->scalar('name');
        $this->assertEquals('user3', $customerName);
        $customerName = $customerClass::find()->where(['status' => 2])->scalar('noname');
        $this->assertNull($customerName);
        $customerId = $customerClass::find()->where(['status' => 2])->scalar('id');
        $this->assertEquals(3, $customerId);
    }

    public function testFindColumn()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $this->assertEquals(['user1', 'user2', 'user3'], $customerClass::find()->orderBy(['name' => SORT_ASC])->column('name'));
        $this->assertEquals(['user3', 'user2', 'user1'], $customerClass::find()->orderBy(['name' => SORT_DESC])->column('name'));
    }

    public function testFindIndexBy()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        // indexBy
        $customers = $customerClass::find()->indexBy('name')->orderBy('id')->all();
        $this->assertCount(3, $customers);
        $this->assertInstanceOf($customerClass, $customers['user1']);
        $this->assertInstanceOf($customerClass, $customers['user2']);
        $this->assertInstanceOf($customerClass, $customers['user3']);

        // indexBy callable
        $customers = $customerClass::find()->indexBy(function ($customer) {
            return $customer->id . '-' . $customer->name;
        })->orderBy('id')->all();
        $this->assertCount(3, $customers);
        $this->assertInstanceOf($customerClass, $customers['1-user1']);
        $this->assertInstanceOf($customerClass, $customers['2-user2']);
        $this->assertInstanceOf($customerClass, $customers['3-user3']);
    }

    public function testFindIndexByAsArray()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        // indexBy + asArray
        $customers = $customerClass::find()->asArray()->indexBy('name')->all();
        $this->assertCount(3, $customers);
        $this->assertArrayHasKey('id', $customers['user1']);
        $this->assertArrayHasKey('name', $customers['user1']);
        $this->assertArrayHasKey('email', $customers['user1']);
        $this->assertArrayHasKey('address', $customers['user1']);
        $this->assertArrayHasKey('status', $customers['user1']);
        $this->assertArrayHasKey('id', $customers['user2']);
        $this->assertArrayHasKey('name', $customers['user2']);
        $this->assertArrayHasKey('email', $customers['user2']);
        $this->assertArrayHasKey('address', $customers['user2']);
        $this->assertArrayHasKey('status', $customers['user2']);
        $this->assertArrayHasKey('id', $customers['user3']);
        $this->assertArrayHasKey('name', $customers['user3']);
        $this->assertArrayHasKey('email', $customers['user3']);
        $this->assertArrayHasKey('address', $customers['user3']);
        $this->assertArrayHasKey('status', $customers['user3']);

        // indexBy callable + asArray
        $customers = $customerClass::find()->indexBy(function ($customer) {
            return $customer['id'] . '-' . $customer['name'];
        })->asArray()->all();
        $this->assertCount(3, $customers);
        $this->assertArrayHasKey('id', $customers['1-user1']);
        $this->assertArrayHasKey('name', $customers['1-user1']);
        $this->assertArrayHasKey('email', $customers['1-user1']);
        $this->assertArrayHasKey('address', $customers['1-user1']);
        $this->assertArrayHasKey('status', $customers['1-user1']);
        $this->assertArrayHasKey('id', $customers['2-user2']);
        $this->assertArrayHasKey('name', $customers['2-user2']);
        $this->assertArrayHasKey('email', $customers['2-user2']);
        $this->assertArrayHasKey('address', $customers['2-user2']);
        $this->assertArrayHasKey('status', $customers['2-user2']);
        $this->assertArrayHasKey('id', $customers['3-user3']);
        $this->assertArrayHasKey('name', $customers['3-user3']);
        $this->assertArrayHasKey('email', $customers['3-user3']);
        $this->assertArrayHasKey('address', $customers['3-user3']);
        $this->assertArrayHasKey('status', $customers['3-user3']);
    }

    public function testRefresh()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        $customer = new $customerClass();
        $this->assertFalse($customer->refresh());

        $customer = $customerClass::findOne(1);
        $customer->name = 'to be refreshed';
        $this->assertTrue($customer->refresh());
        $this->assertEquals('user1', $customer->name);
    }

    public function testEquals()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $itemClass ActiveRecordInterface */
        $itemClass = $this->getItemClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $customerA = new $customerClass();
        $customerB = new $customerClass();
        $this->assertFalse($customerA->equals($customerB));

        $customerA = new $customerClass();
        $customerB = new $itemClass();
        $this->assertFalse($customerA->equals($customerB));

        $customerA = $customerClass::findOne(1);
        $customerB = $customerClass::findOne(2);
        $this->assertFalse($customerA->equals($customerB));

        $customerB = $customerClass::findOne(1);
        $this->assertTrue($customerA->equals($customerB));

        $customerA = $customerClass::findOne(1);
        $customerB = $itemClass::findOne(1);
        $this->assertFalse($customerA->equals($customerB));
    }

    public function testFindCount()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $this->assertEquals(3, $customerClass::find()->count());

        $this->assertEquals(1, $customerClass::find()->where(['id' => 1])->count());
        $this->assertEquals(2, $customerClass::find()->where(['id' => [1, 2]])->count());
        $this->assertEquals(2, $customerClass::find()->where(['id' => [1, 2]])->offset(1)->count());
        $this->assertEquals(2, $customerClass::find()->where(['id' => [1, 2]])->offset(2)->count());

        // limit should have no effect on count()
        $this->assertEquals(3, $customerClass::find()->limit(1)->count());
        $this->assertEquals(3, $customerClass::find()->limit(2)->count());
        $this->assertEquals(3, $customerClass::find()->limit(10)->count());
        $this->assertEquals(3, $customerClass::find()->offset(2)->limit(2)->count());
    }

    public function testFindLimit()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        // all()
        $customers = $customerClass::find()->all();
        $this->assertCount(3, $customers);

        $customers = $customerClass::find()->orderBy('id')->limit(1)->all();
        $this->assertCount(1, $customers);
        $this->assertEquals('user1', $customers[0]->name);

        $customers = $customerClass::find()->orderBy('id')->limit(1)->offset(1)->all();
        $this->assertCount(1, $customers);
        $this->assertEquals('user2', $customers[0]->name);

        $customers = $customerClass::find()->orderBy('id')->limit(1)->offset(2)->all();
        $this->assertCount(1, $customers);
        $this->assertEquals('user3', $customers[0]->name);

        $customers = $customerClass::find()->orderBy('id')->limit(2)->offset(1)->all();
        $this->assertCount(2, $customers);
        $this->assertEquals('user2', $customers[0]->name);
        $this->assertEquals('user3', $customers[1]->name);

        $customers = $customerClass::find()->limit(2)->offset(3)->all();
        $this->assertCount(0, $customers);

        // one()
        $customer = $customerClass::find()->orderBy('id')->one();
        $this->assertEquals('user1', $customer->name);

        $customer = $customerClass::find()->orderBy('id')->offset(0)->one();
        $this->assertEquals('user1', $customer->name);

        $customer = $customerClass::find()->orderBy('id')->offset(1)->one();
        $this->assertEquals('user2', $customer->name);

        $customer = $customerClass::find()->orderBy('id')->offset(2)->one();
        $this->assertEquals('user3', $customer->name);

        $customer = $customerClass::find()->offset(3)->one();
        $this->assertNull($customer);
    }

    public function testFindComplexCondition()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $this->assertEquals(2, $customerClass::find()->where(['OR', ['name' => 'user1'], ['name' => 'user2']])->count());
        $this->assertCount(2, $customerClass::find()->where(['OR', ['name' => 'user1'], ['name' => 'user2']])->all());

        $this->assertEquals(2, $customerClass::find()->where(['name' => ['user1', 'user2']])->count());
        $this->assertCount(2, $customerClass::find()->where(['name' => ['user1', 'user2']])->all());

        $this->assertEquals(1, $customerClass::find()->where(['AND', ['name' => ['user2', 'user3']], ['BETWEEN', 'status', 2, 4]])->count());
        $this->assertCount(1, $customerClass::find()->where(['AND', ['name' => ['user2', 'user3']], ['BETWEEN', 'status', 2, 4]])->all());
    }

    public function testFindNullValues()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $customer = $customerClass::findOne(2);
        $customer->name = null;
        $customer->save(false);
        $this->afterSave();

        $result = $customerClass::find()->where(['name' => null])->all();
        $this->assertCount(1, $result);
        $this->assertEquals(2, reset($result)->primaryKey);
    }

    public function testExists()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $this->assertTrue($customerClass::find()->where(['id' => 2])->exists());
        $this->assertFalse($customerClass::find()->where(['id' => 5])->exists());
        $this->assertTrue($customerClass::find()->where(['name' => 'user1'])->exists());
        $this->assertFalse($customerClass::find()->where(['name' => 'user5'])->exists());

        $this->assertTrue($customerClass::find()->where(['id' => [2, 3]])->exists());
        $this->assertTrue($customerClass::find()->where(['id' => [2, 3]])->offset(1)->exists());
        $this->assertFalse($customerClass::find()->where(['id' => [2, 3]])->offset(2)->exists());
    }

    public function testFindLazy()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $customer = $customerClass::findOne(2);
        $this->assertFalse($customer->isRelationPopulated('orders'));
        $orders = $customer->orders;
        $this->assertTrue($customer->isRelationPopulated('orders'));
        $this->assertCount(2, $orders);
        $this->assertCount(1, $customer->relatedRecords);

        // unset
        unset($customer['orders']);
        $this->assertFalse($customer->isRelationPopulated('orders'));

        /* @var $customer Customer */
        $customer = $customerClass::findOne(2);
        $this->assertFalse($customer->isRelationPopulated('orders'));
        $orders = $customer->getOrders()->where(['id' => 3])->all();
        $this->assertFalse($customer->isRelationPopulated('orders'));
        $this->assertCount(0, $customer->relatedRecords);

        $this->assertCount(1, $orders);
        $this->assertEquals(3, $orders[0]->id);
    }

    public function testFindEager()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $orderClass ActiveRecordInterface */
        $orderClass = $this->getOrderClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $customers = $customerClass::find()->with('orders')->indexBy('id')->all();
        ksort($customers);
        $this->assertCount(3, $customers);
        $this->assertTrue($customers[1]->isRelationPopulated('orders'));
        $this->assertTrue($customers[2]->isRelationPopulated('orders'));
        $this->assertTrue($customers[3]->isRelationPopulated('orders'));
        $this->assertCount(1, $customers[1]->orders);
        $this->assertCount(2, $customers[2]->orders);
        $this->assertCount(0, $customers[3]->orders);
        // unset
        unset($customers[1]->orders);
        $this->assertFalse($customers[1]->isRelationPopulated('orders'));

        $customer = $customerClass::find()->where(['id' => 1])->with('orders')->one();
        $this->assertTrue($customer->isRelationPopulated('orders'));
        $this->assertCount(1, $customer->orders);
        $this->assertCount(1, $customer->relatedRecords);

        // multiple with() calls
        $orders = $orderClass::find()->with('customer', 'items')->all();
        $this->assertCount(3, $orders);
        $this->assertTrue($orders[0]->isRelationPopulated('customer'));
        $this->assertTrue($orders[0]->isRelationPopulated('items'));
        $orders = $orderClass::find()->with('customer')->with('items')->all();
        $this->assertCount(3, $orders);
        $this->assertTrue($orders[0]->isRelationPopulated('customer'));
        $this->assertTrue($orders[0]->isRelationPopulated('items'));
    }

    public function testFindLazyVia()
    {
        /* @var $orderClass ActiveRecordInterface */
        $orderClass = $this->getOrderClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        /* @var $order Order */
        $order = $orderClass::findOne(1);
        $this->assertEquals(1, $order->id);
        $this->assertCount(2, $order->items);
        $this->assertEquals(1, $order->items[0]->id);
        $this->assertEquals(2, $order->items[1]->id);
    }

    public function testFindLazyVia2()
    {
        /* @var $orderClass ActiveRecordInterface */
        $orderClass = $this->getOrderClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        /* @var $order Order */
        $order = $orderClass::findOne(1);
        $order->id = 100;
        $this->assertEquals([], $order->items);
    }

    public function testFindEagerViaRelation()
    {
        /* @var $orderClass ActiveRecordInterface */
        $orderClass = $this->getOrderClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $orders = $orderClass::find()->with('items')->orderBy('id')->all();
        $this->assertCount(3, $orders);
        $order = $orders[0];
        $this->assertEquals(1, $order->id);
        $this->assertTrue($order->isRelationPopulated('items'));
        $this->assertCount(2, $order->items);
        $this->assertEquals(1, $order->items[0]->id);
        $this->assertEquals(2, $order->items[1]->id);
    }

    public function testFindNestedRelation()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $customers = $customerClass::find()->with('orders', 'orders.items')->indexBy('id')->all();
        ksort($customers);
        $this->assertCount(3, $customers);
        $this->assertTrue($customers[1]->isRelationPopulated('orders'));
        $this->assertTrue($customers[2]->isRelationPopulated('orders'));
        $this->assertTrue($customers[3]->isRelationPopulated('orders'));
        $this->assertCount(1, $customers[1]->orders);
        $this->assertCount(2, $customers[2]->orders);
        $this->assertCount(0, $customers[3]->orders);
        $this->assertTrue($customers[1]->orders[0]->isRelationPopulated('items'));
        $this->assertTrue($customers[2]->orders[0]->isRelationPopulated('items'));
        $this->assertTrue($customers[2]->orders[1]->isRelationPopulated('items'));
        $this->assertCount(2, $customers[1]->orders[0]->items);
        $this->assertCount(3, $customers[2]->orders[0]->items);
        $this->assertCount(1, $customers[2]->orders[1]->items);

        $customers = $customerClass::find()->where(['id' => 1])->with('ordersWithItems')->one();
        $this->assertTrue($customers->isRelationPopulated('ordersWithItems'));
        $this->assertCount(1, $customers->ordersWithItems);

        /** @var Order $order */
        $order = $customers->ordersWithItems[0];
        $this->assertTrue($order->isRelationPopulated('orderItems'));
        $this->assertCount(2, $order->orderItems);
    }

    /**
     * Ensure ActiveRelationTrait does preserve order of items on find via().
     *
     * @see https://github.com/yiisoft/yii2/issues/1310.
     */
    public function testFindEagerViaRelationPreserveOrder()
    {
        /* @var $orderClass ActiveRecordInterface */
        $orderClass = $this->getOrderClass();

        /* @var $this TestCase|ActiveRecordTestTrait */

        /*
        Item (name, category_id)
        Order (customer_id, created_at, total)
        OrderItem (order_id, item_id, quantity, subtotal)

        Result should be the following:

        Order 1: 1, 1325282384, 110.0
        - orderItems:
            OrderItem: 1, 1, 1, 30.0
            OrderItem: 1, 2, 2, 40.0
        - itemsInOrder:
            Item 1: 'Agile Web Application Development with Yii1.1 and PHP5', 1
            Item 2: 'Yii 1.1 Application Development Cookbook', 1

        Order 2: 2, 1325334482, 33.0
        - orderItems:
            OrderItem: 2, 3, 1, 8.0
            OrderItem: 2, 4, 1, 10.0
            OrderItem: 2, 5, 1, 15.0
        - itemsInOrder:
            Item 5: 'Cars', 2
            Item 3: 'Ice Age', 2
            Item 4: 'Toy Story', 2
        Order 3: 2, 1325502201, 40.0
        - orderItems:
            OrderItem: 3, 2, 1, 40.0
        - itemsInOrder:
            Item 3: 'Ice Age', 2
         */
        $orders = $orderClass::find()->with('itemsInOrder1')->orderBy('created_at')->all();
        $this->assertCount(3, $orders);

        $order = $orders[0];
        $this->assertEquals(1, $order->id);
        $this->assertTrue($order->isRelationPopulated('itemsInOrder1'));
        $this->assertCount(2, $order->itemsInOrder1);
        $this->assertEquals(1, $order->itemsInOrder1[0]->id);
        $this->assertEquals(2, $order->itemsInOrder1[1]->id);

        $order = $orders[1];
        $this->assertEquals(2, $order->id);
        $this->assertTrue($order->isRelationPopulated('itemsInOrder1'));
        $this->assertCount(3, $order->itemsInOrder1);
        $this->assertEquals(5, $order->itemsInOrder1[0]->id);
        $this->assertEquals(3, $order->itemsInOrder1[1]->id);
        $this->assertEquals(4, $order->itemsInOrder1[2]->id);

        $order = $orders[2];
        $this->assertEquals(3, $order->id);
        $this->assertTrue($order->isRelationPopulated('itemsInOrder1'));
        $this->assertCount(1, $order->itemsInOrder1);
        $this->assertEquals(2, $order->itemsInOrder1[0]->id);
    }

    // different order in via table
    public function testFindEagerViaRelationPreserveOrderB()
    {
        /* @var $orderClass ActiveRecordInterface */
        $orderClass = $this->getOrderClass();

        $orders = $orderClass::find()->with('itemsInOrder2')->orderBy('created_at')->all();
        $this->assertCount(3, $orders);

        $order = $orders[0];
        $this->assertEquals(1, $order->id);
        $this->assertTrue($order->isRelationPopulated('itemsInOrder2'));
        $this->assertCount(2, $order->itemsInOrder2);
        $this->assertEquals(1, $order->itemsInOrder2[0]->id);
        $this->assertEquals(2, $order->itemsInOrder2[1]->id);

        $order = $orders[1];
        $this->assertEquals(2, $order->id);
        $this->assertTrue($order->isRelationPopulated('itemsInOrder2'));
        $this->assertCount(3, $order->itemsInOrder2);
        $this->assertEquals(5, $order->itemsInOrder2[0]->id);
        $this->assertEquals(3, $order->itemsInOrder2[1]->id);
        $this->assertEquals(4, $order->itemsInOrder2[2]->id);

        $order = $orders[2];
        $this->assertEquals(3, $order->id);
        $this->assertTrue($order->isRelationPopulated('itemsInOrder2'));
        $this->assertCount(1, $order->itemsInOrder2);
        $this->assertEquals(2, $order->itemsInOrder2[0]->id);
    }

    public function testLink()
    {
        /* @var $orderClass ActiveRecordInterface */
        /* @var $itemClass ActiveRecordInterface */
        /* @var $orderItemClass ActiveRecordInterface */
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        $orderClass = $this->getOrderClass();
        $orderItemClass = $this->getOrderItemClass();
        $itemClass = $this->getItemClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        $customer = $customerClass::findOne(2);
        $this->assertCount(2, $customer->orders);

        // has many
        $order = new $orderClass();
        $order->total = 100;
        $this->assertTrue($order->isNewRecord);
        $customer->link('orders', $order);
        $this->afterSave();
        $this->assertCount(3, $customer->orders);
        $this->assertFalse($order->isNewRecord);
        $this->assertCount(3, $customer->getOrders()->all());
        $this->assertEquals(2, $order->customer_id);

        // belongs to
        $order = new $orderClass();
        $order->total = 100;
        $this->assertTrue($order->isNewRecord);
        $customer = $customerClass::findOne(1);
        $this->assertNull($order->customer);
        $order->link('customer', $customer);
        $this->assertFalse($order->isNewRecord);
        $this->assertEquals(1, $order->customer_id);
        $this->assertEquals(1, $order->customer->primaryKey);

        // via model
        $order = $orderClass::findOne(1);
        $this->assertCount(2, $order->items);
        $this->assertCount(2, $order->orderItems);
        $orderItem = $orderItemClass::findOne(['order_id' => 1, 'item_id' => 3]);
        $this->assertNull($orderItem);
        $item = $itemClass::findOne(3);
        $order->link('items', $item, ['quantity' => 10, 'subtotal' => 100]);
        $this->afterSave();
        $this->assertCount(3, $order->items);
        $this->assertCount(3, $order->orderItems);
        $orderItem = $orderItemClass::findOne(['order_id' => 1, 'item_id' => 3]);
        $this->assertInstanceOf($orderItemClass, $orderItem);
        $this->assertEquals(10, $orderItem->quantity);
        $this->assertEquals(100, $orderItem->subtotal);
    }

    public function testUnlink()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $orderClass ActiveRecordInterface */
        $orderClass = $this->getOrderClass();
        /* @var $orderWithNullFKClass ActiveRecordInterface */
        $orderWithNullFKClass = $this->getOrderWithNullFKClass();
        /* @var $orderItemsWithNullFKClass ActiveRecordInterface */
        $orderItemsWithNullFKClass = $this->getOrderItemWithNullFKmClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        // has many without delete
        $customer = $customerClass::findOne(2);
        $this->assertCount(2, $customer->ordersWithNullFK);
        $customer->unlink('ordersWithNullFK', $customer->ordersWithNullFK[1], false);

        $this->assertCount(1, $customer->ordersWithNullFK);
        $orderWithNullFK = $orderWithNullFKClass::findOne(3);

        $this->assertEquals(3, $orderWithNullFK->id);
        $this->assertNull($orderWithNullFK->customer_id);

        // has many with delete
        $customer = $customerClass::findOne(2);
        $this->assertCount(2, $customer->orders);
        $customer->unlink('orders', $customer->orders[1], true);
        $this->afterSave();

        $this->assertCount(1, $customer->orders);
        $this->assertNull($orderClass::findOne(3));

        // via model with delete
        $order = $orderClass::findOne(2);
        $this->assertCount(3, $order->items);
        $this->assertCount(3, $order->orderItems);
        $order->unlink('items', $order->items[2], true);
        $this->afterSave();

        $this->assertCount(2, $order->items);
        $this->assertCount(2, $order->orderItems);

        // via model without delete
        $this->assertCount(2, $order->itemsWithNullFK);
        $order->unlink('itemsWithNullFK', $order->itemsWithNullFK[1], false);
        $this->afterSave();

        $this->assertCount(1, $order->itemsWithNullFK);
        $this->assertCount(2, $order->orderItems);
    }

    public function testUnlinkAll()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $orderClass ActiveRecordInterface */
        $orderClass = $this->getOrderClass();
        /* @var $orderItemClass ActiveRecordInterface */
        $orderItemClass = $this->getOrderItemClass();
        /* @var $itemClass ActiveRecordInterface */
        $itemClass = $this->getItemClass();
        /* @var $orderWithNullFKClass ActiveRecordInterface */
        $orderWithNullFKClass = $this->getOrderWithNullFKClass();
        /* @var $orderItemsWithNullFKClass ActiveRecordInterface */
        $orderItemsWithNullFKClass = $this->getOrderItemWithNullFKmClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        // has many with delete
        $customer = $customerClass::findOne(2);
        $this->assertCount(2, $customer->orders);
        $this->assertEquals(3, $orderClass::find()->count());
        $customer->unlinkAll('orders', true);
        $this->afterSave();
        $this->assertEquals(1, $orderClass::find()->count());
        $this->assertCount(0, $customer->orders);

        $this->assertNull($orderClass::findOne(2));
        $this->assertNull($orderClass::findOne(3));


        // has many without delete
        $customer = $customerClass::findOne(2);
        $this->assertCount(2, $customer->ordersWithNullFK);
        $this->assertEquals(3, $orderWithNullFKClass::find()->count());
        $customer->unlinkAll('ordersWithNullFK', false);
        $this->afterSave();
        $this->assertCount(0, $customer->ordersWithNullFK);
        $this->assertEquals(3, $orderWithNullFKClass::find()->count());
        $this->assertEquals(2, $orderWithNullFKClass::find()->where(['AND', ['id' => [2, 3]], ['customer_id' => null]])->count());


        // via model with delete
        /* @var $order Order */
        $order = $orderClass::findOne(1);
        $this->assertCount(2, $order->books);
        $orderItemCount = $orderItemClass::find()->count();
        $this->assertEquals(5, $itemClass::find()->count());
        $order->unlinkAll('books', true);
        $this->afterSave();
        $this->assertEquals(5, $itemClass::find()->count());
        $this->assertEquals($orderItemCount - 2, $orderItemClass::find()->count());
        $this->assertCount(0, $order->books);

        // via model without delete
        $this->assertCount(2, $order->booksWithNullFK);
        $orderItemCount = $orderItemsWithNullFKClass::find()->count();
        $this->assertEquals(5, $itemClass::find()->count());
        $order->unlinkAll('booksWithNullFK', false);
        $this->afterSave();
        $this->assertCount(0, $order->booksWithNullFK);
        $this->assertEquals(2, $orderItemsWithNullFKClass::find()->where(['AND', ['item_id' => [1, 2]], ['order_id' => null]])->count());
        $this->assertEquals($orderItemCount, $orderItemsWithNullFKClass::find()->count());
        $this->assertEquals(5, $itemClass::find()->count());

        // via table is covered in \yiiunit\framework\db\ActiveRecordTest::testUnlinkAllViaTable()
    }

    public function testUnlinkAllAndConditionSetNull()
    {
        /* @var $this TestCase|ActiveRecordTestTrait */

        /* @var $customerClass \yii\db\BaseActiveRecord */
        $customerClass = $this->getCustomerClass();
        /* @var $orderClass \yii\db\BaseActiveRecord */
        $orderClass = $this->getOrderWithNullFKClass();

        // in this test all orders are owned by customer 1
        $orderClass::updateAll(['customer_id' => 1]);
        $this->afterSave();

        $customer = $customerClass::findOne(1);
        $this->assertCount(3, $customer->ordersWithNullFK);
        $this->assertCount(1, $customer->expensiveOrdersWithNullFK);
        $this->assertEquals(3, $orderClass::find()->count());
        $customer->unlinkAll('expensiveOrdersWithNullFK');
        $this->assertCount(3, $customer->ordersWithNullFK);
        $this->assertCount(0, $customer->expensiveOrdersWithNullFK);
        $this->assertEquals(3, $orderClass::find()->count());
        $customer = $customerClass::findOne(1);
        $this->assertCount(2, $customer->ordersWithNullFK);
        $this->assertCount(0, $customer->expensiveOrdersWithNullFK);
    }

    public function testUnlinkAllAndConditionDelete()
    {
        /* @var $this TestCase|ActiveRecordTestTrait */

        /* @var $customerClass \yii\db\BaseActiveRecord */
        $customerClass = $this->getCustomerClass();
        /* @var $orderClass \yii\db\BaseActiveRecord */
        $orderClass = $this->getOrderClass();

        // in this test all orders are owned by customer 1
        $orderClass::updateAll(['customer_id' => 1]);
        $this->afterSave();

        $customer = $customerClass::findOne(1);
        $this->assertCount(3, $customer->orders);
        $this->assertCount(1, $customer->expensiveOrders);
        $this->assertEquals(3, $orderClass::find()->count());
        $customer->unlinkAll('expensiveOrders', true);
        $this->assertCount(3, $customer->orders);
        $this->assertCount(0, $customer->expensiveOrders);
        $this->assertEquals(2, $orderClass::find()->count());
        $customer = $customerClass::findOne(1);
        $this->assertCount(2, $customer->orders);
        $this->assertCount(0, $customer->expensiveOrders);
    }

    public static $afterSaveNewRecord;
    public static $afterSaveInsert;

    public function testInsert()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        $customer = new $customerClass();
        $customer->email = 'user4@example.com';
        $customer->name = 'user4';
        $customer->address = 'address4';

        $this->assertNull($customer->id);
        $this->assertTrue($customer->isNewRecord);
        static::$afterSaveNewRecord = null;
        static::$afterSaveInsert = null;

        $customer->save();
        $this->afterSave();

        $this->assertNotNull($customer->id);
        $this->assertFalse(static::$afterSaveNewRecord);
        $this->assertTrue(static::$afterSaveInsert);
        $this->assertFalse($customer->isNewRecord);
    }

    public function testExplicitPkOnAutoIncrement()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        $customer = new $customerClass();
        $customer->id = 1337;
        $customer->email = 'user1337@example.com';
        $customer->name = 'user1337';
        $customer->address = 'address1337';

        $this->assertTrue($customer->isNewRecord);
        $customer->save();
        $this->afterSave();

        $this->assertEquals(1337, $customer->id);
        $this->assertFalse($customer->isNewRecord);
    }

    public function testUpdate()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        // save
        /* @var $customer Customer */
        $customer = $customerClass::findOne(2);
        $this->assertInstanceOf($customerClass, $customer);
        $this->assertEquals('user2', $customer->name);
        $this->assertFalse($customer->isNewRecord);
        static::$afterSaveNewRecord = null;
        static::$afterSaveInsert = null;
        $this->assertEmpty($customer->dirtyAttributes);

        $customer->name = 'user2x';
        $customer->save();
        $this->afterSave();
        $this->assertEquals('user2x', $customer->name);
        $this->assertFalse($customer->isNewRecord);
        $this->assertFalse(static::$afterSaveNewRecord);
        $this->assertFalse(static::$afterSaveInsert);
        $customer2 = $customerClass::findOne(2);
        $this->assertEquals('user2x', $customer2->name);

        // updateAll
        $customer = $customerClass::findOne(3);
        $this->assertEquals('user3', $customer->name);
        $ret = $customerClass::updateAll(['name' => 'temp'], ['id' => 3]);
        $this->afterSave();
        $this->assertEquals(1, $ret);
        $customer = $customerClass::findOne(3);
        $this->assertEquals('temp', $customer->name);

        $ret = $customerClass::updateAll(['name' => 'tempX']);
        $this->afterSave();
        $this->assertEquals(3, $ret);

        $ret = $customerClass::updateAll(['name' => 'temp'], ['name' => 'user6']);
        $this->afterSave();
        $this->assertEquals(0, $ret);
    }

    public function testUpdateAttributes()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        /* @var $customer Customer */
        $customer = $customerClass::findOne(2);
        $this->assertInstanceOf($customerClass, $customer);
        $this->assertEquals('user2', $customer->name);
        $this->assertFalse($customer->isNewRecord);
        static::$afterSaveNewRecord = null;
        static::$afterSaveInsert = null;

        $customer->updateAttributes(['name' => 'user2x']);
        $this->afterSave();
        $this->assertEquals('user2x', $customer->name);
        $this->assertFalse($customer->isNewRecord);
        $this->assertNull(static::$afterSaveNewRecord);
        $this->assertNull(static::$afterSaveInsert);
        $customer2 = $customerClass::findOne(2);
        $this->assertEquals('user2x', $customer2->name);

        $customer = $customerClass::findOne(1);
        $this->assertEquals('user1', $customer->name);
        $this->assertEquals(1, $customer->status);
        $customer->name = 'user1x';
        $customer->status = 2;
        $customer->updateAttributes(['name']);
        $this->assertEquals('user1x', $customer->name);
        $this->assertEquals(2, $customer->status);
        $customer = $customerClass::findOne(1);
        $this->assertEquals('user1x', $customer->name);
        $this->assertEquals(1, $customer->status);
    }

    public function testUpdateCounters()
    {
        /* @var $orderItemClass ActiveRecordInterface */
        $orderItemClass = $this->getOrderItemClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        // updateCounters
        $pk = ['order_id' => 2, 'item_id' => 4];
        $orderItem = $orderItemClass::findOne($pk);
        $this->assertEquals(1, $orderItem->quantity);
        $ret = $orderItem->updateCounters(['quantity' => -1]);
        $this->afterSave();
        $this->assertEquals(1, $ret);
        $this->assertEquals(0, $orderItem->quantity);
        $orderItem = $orderItemClass::findOne($pk);
        $this->assertEquals(0, $orderItem->quantity);

        // updateAllCounters
        $pk = ['order_id' => 1, 'item_id' => 2];
        $orderItem = $orderItemClass::findOne($pk);
        $this->assertEquals(2, $orderItem->quantity);
        $ret = $orderItemClass::updateAllCounters([
            'quantity' => 3,
            'subtotal' => -10,
        ], $pk);
        $this->afterSave();
        $this->assertEquals(1, $ret);
        $orderItem = $orderItemClass::findOne($pk);
        $this->assertEquals(5, $orderItem->quantity);
        $this->assertEquals(30, $orderItem->subtotal);
    }

    public function testDelete()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        // delete
        $customer = $customerClass::findOne(2);
        $this->assertInstanceOf($customerClass, $customer);
        $this->assertEquals('user2', $customer->name);
        $customer->delete();
        $this->afterSave();
        $customer = $customerClass::findOne(2);
        $this->assertNull($customer);

        // deleteAll
        $customers = $customerClass::find()->all();
        $this->assertCount(2, $customers);
        $ret = $customerClass::deleteAll();
        $this->afterSave();
        $this->assertEquals(2, $ret);
        $customers = $customerClass::find()->all();
        $this->assertCount(0, $customers);

        $ret = $customerClass::deleteAll();
        $this->afterSave();
        $this->assertEquals(0, $ret);
    }

    /**
     * Some PDO implementations(e.g. cubrid) do not support boolean values.
     * Make sure this does not affect AR layer.
     */
    public function testBooleanAttribute()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        $customer = new $customerClass();
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

        $customers = $customerClass::find()->where(['status' => true])->all();
        $this->assertCount(2, $customers);

        $customers = $customerClass::find()->where(['status' => false])->all();
        $this->assertCount(1, $customers);
    }

    public function testAfterFind()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $orderClass BaseActiveRecord */
        $orderClass = $this->getOrderClass();
        /* @var $this TestCase|ActiveRecordTestTrait */

        $afterFindCalls = [];
        Event::on(BaseActiveRecord::className(), BaseActiveRecord::EVENT_AFTER_FIND, function ($event) use (&$afterFindCalls) {
            /* @var $ar BaseActiveRecord */
            $ar = $event->sender;
            $afterFindCalls[] = [\get_class($ar), $ar->getIsNewRecord(), $ar->getPrimaryKey(), $ar->isRelationPopulated('orders')];
        });

        $customer = $customerClass::findOne(1);
        $this->assertNotNull($customer);
        $this->assertEquals([[$customerClass, false, 1, false]], $afterFindCalls);
        $afterFindCalls = [];

        $customer = $customerClass::find()->where(['id' => 1])->one();
        $this->assertNotNull($customer);
        $this->assertEquals([[$customerClass, false, 1, false]], $afterFindCalls);
        $afterFindCalls = [];

        $customer = $customerClass::find()->where(['id' => 1])->all();
        $this->assertNotNull($customer);
        $this->assertEquals([[$customerClass, false, 1, false]], $afterFindCalls);
        $afterFindCalls = [];

        $customer = $customerClass::find()->where(['id' => 1])->with('orders')->all();
        $this->assertNotNull($customer);
        $this->assertEquals([
            [$this->getOrderClass(), false, 1, false],
            [$customerClass, false, 1, true],
        ], $afterFindCalls);
        $afterFindCalls = [];

        if ($this instanceof \yiiunit\extensions\redis\ActiveRecordTest) { // TODO redis does not support orderBy() yet
            $customer = $customerClass::find()->where(['id' => [1, 2]])->with('orders')->all();
        } else {
            // orderBy is needed to avoid random test failure
            $customer = $customerClass::find()->where(['id' => [1, 2]])->with('orders')->orderBy('name')->all();
        }
        $this->assertNotNull($customer);
        $this->assertEquals([
            [$orderClass, false, 1, false],
            [$orderClass, false, 2, false],
            [$orderClass, false, 3, false],
            [$customerClass, false, 1, true],
            [$customerClass, false, 2, true],
        ], $afterFindCalls);
        $afterFindCalls = [];

        Event::off(BaseActiveRecord::className(), BaseActiveRecord::EVENT_AFTER_FIND);
    }

    public function testAfterRefresh()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */

        $afterRefreshCalls = [];
        Event::on(BaseActiveRecord::className(), BaseActiveRecord::EVENT_AFTER_REFRESH, function ($event) use (&$afterRefreshCalls) {
            /* @var $ar BaseActiveRecord */
            $ar = $event->sender;
            $afterRefreshCalls[] = [\get_class($ar), $ar->getIsNewRecord(), $ar->getPrimaryKey(), $ar->isRelationPopulated('orders')];
        });

        $customer = $customerClass::findOne(1);
        $this->assertNotNull($customer);
        $customer->refresh();
        $this->assertEquals([[$customerClass, false, 1, false]], $afterRefreshCalls);
        $afterRefreshCalls = [];
        Event::off(BaseActiveRecord::className(), BaseActiveRecord::EVENT_AFTER_REFRESH);
    }

    public function testFindEmptyInCondition()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */

        $customers = $customerClass::find()->where(['id' => [1]])->all();
        $this->assertCount(1, $customers);

        $customers = $customerClass::find()->where(['id' => []])->all();
        $this->assertCount(0, $customers);

        $customers = $customerClass::find()->where(['IN', 'id', [1]])->all();
        $this->assertCount(1, $customers);

        $customers = $customerClass::find()->where(['IN', 'id', []])->all();
        $this->assertCount(0, $customers);
    }

    public function testFindEagerIndexBy()
    {
        /* @var $this TestCase|ActiveRecordTestTrait */

        /* @var $orderClass ActiveRecordInterface */
        $orderClass = $this->getOrderClass();

        /* @var $order Order */
        $order = $orderClass::find()->with('itemsIndexed')->where(['id' => 1])->one();
        $this->assertTrue($order->isRelationPopulated('itemsIndexed'));
        $items = $order->itemsIndexed;
        $this->assertCount(2, $items);
        $this->assertTrue(isset($items[1]));
        $this->assertTrue(isset($items[2]));

        /* @var $order Order */
        $order = $orderClass::find()->with('itemsIndexed')->where(['id' => 2])->one();
        $this->assertTrue($order->isRelationPopulated('itemsIndexed'));
        $items = $order->itemsIndexed;
        $this->assertCount(3, $items);
        $this->assertTrue(isset($items[3]));
        $this->assertTrue(isset($items[4]));
        $this->assertTrue(isset($items[5]));
    }

    public function testAttributeAccess()
    {
        /* @var $customerClass ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        $model = new $customerClass();

        $this->assertTrue($model->canSetProperty('name'));
        $this->assertTrue($model->canGetProperty('name'));
        $this->assertFalse($model->canSetProperty('unExistingColumn'));
        $this->assertFalse(isset($model->name));

        $model->name = 'foo';
        $this->assertTrue(isset($model->name));
        unset($model->name);
        $this->assertNull($model->name);

        // @see https://github.com/yiisoft/yii2-gii/issues/190
        $baseModel = new $customerClass();
        $this->assertFalse($baseModel->hasProperty('unExistingColumn'));


        /* @var $customer ActiveRecord */
        $customer = new $customerClass();
        $this->assertInstanceOf($customerClass, $customer);

        $this->assertTrue($customer->canGetProperty('id'));
        $this->assertTrue($customer->canSetProperty('id'));

        // tests that we really can get and set this property
        $this->assertNull($customer->id);
        $customer->id = 10;
        $this->assertNotNull($customer->id);

        // Let's test relations
        $this->assertTrue($customer->canGetProperty('orderItems'));
        $this->assertFalse($customer->canSetProperty('orderItems'));

        // Newly created model must have empty relation
        $this->assertSame([], $customer->orderItems);

        // does it still work after accessing the relation?
        $this->assertTrue($customer->canGetProperty('orderItems'));
        $this->assertFalse($customer->canSetProperty('orderItems'));

        try {
            /* @var $itemClass ActiveRecordInterface */
            $itemClass = $this->getItemClass();
            $customer->orderItems = [new $itemClass()];
            $this->fail('setter call above MUST throw Exception');
        } catch (\Exception $e) {
            // catch exception "Setting read-only property"
            $this->assertInstanceOf('yii\base\InvalidCallException', $e);
        }

        // related attribute $customer->orderItems didn't change cause it's read-only
        $this->assertSame([], $customer->orderItems);

        $this->assertFalse($customer->canGetProperty('non_existing_property'));
        $this->assertFalse($customer->canSetProperty('non_existing_property'));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/17089
     */
    public function testViaWithCallable()
    {
        /* @var $orderClass ActiveRecordInterface */
        $orderClass = $this->getOrderClass();

        /* @var Order $order */
        $order = $orderClass::findOne(2);

        $expensiveItems = $order->expensiveItemsUsingViaWithCallable;
        $cheapItems = $order->cheapItemsUsingViaWithCallable;

        $this->assertCount(2, $expensiveItems);
        $this->assertEquals(4, $expensiveItems[0]->id);
        $this->assertEquals(5, $expensiveItems[1]->id);

        $this->assertCount(1, $cheapItems);
        $this->assertEquals(3, $cheapItems[0]->id);
    }
}
