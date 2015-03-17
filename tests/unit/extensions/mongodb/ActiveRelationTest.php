<?php

namespace yiiunit\extensions\mongodb;

use yiiunit\data\ar\mongodb\ActiveRecord;
use yiiunit\data\ar\mongodb\Customer;
use yiiunit\data\ar\mongodb\CustomerOrder;

/**
 * @group mongodb
 */
class ActiveRelationTest extends MongoDbTestCase
{
    protected function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
        $this->setUpTestRows();
    }

    protected function tearDown()
    {
        $this->dropCollection(Customer::collectionName());
        $this->dropCollection(CustomerOrder::collectionName());
        parent::tearDown();
    }

    /**
     * Sets up test rows.
     */
    protected function setUpTestRows()
    {
        $customers = [];
        for ($i = 1; $i <= 5; $i++) {
            $customers[] = [
                'name' => 'name' . $i,
                'email' => 'email' . $i,
                'address' => 'address' . $i,
                'status' => $i,
            ];
        }
        $customerCollection = $this->getConnection()->getCollection('customer');
        $customers = $customerCollection->batchInsert($customers);

        $items = [];
        for ($i = 1; $i <= 10; $i++) {
            $items[] = [
                'name' => 'name' . $i,
                'price' => $i,
            ];
        }
        $itemCollection = $this->getConnection()->getCollection('item');
        $items = $itemCollection->batchInsert($items);

        $customerOrders = [];
        foreach ($customers as $i => $customer) {
            $customerOrders[] = [
                'customer_id' => $customer['_id'],
                'number' => $customer['status'],
                'item_ids' => [
                    $items[$i]['_id'],
                    $items[$i+5]['_id'],
                ],
            ];
            $customerOrders[] = [
                'customer_id' => $customer['_id'],
                'number' => $customer['status'] + 100,
                'item_ids' => [
                    $items[$i]['_id'],
                    $items[$i+5]['_id'],
                ],
            ];
        }
        $customerOrderCollection = $this->getConnection()->getCollection('customer_order');
        $customerOrderCollection->batchInsert($customerOrders);
    }

    // Tests :

    public function testFindLazy()
    {
        /* @var $order CustomerOrder */
        $order = CustomerOrder::findOne(['number' => 2]);
        $this->assertFalse($order->isRelationPopulated('customer'));
        $customer = $order->customer;
        $this->assertTrue($order->isRelationPopulated('customer'));
        $this->assertTrue($customer instanceof Customer);
        $this->assertEquals((string) $customer->_id, (string) $order->customer_id);
        $this->assertEquals(1, count($order->relatedRecords));
    }

    public function testFindEager()
    {
        $orders = CustomerOrder::find()->with('customer')->all();
        $this->assertEquals(10, count($orders));
        $this->assertTrue($orders[0]->isRelationPopulated('customer'));
        $this->assertTrue($orders[1]->isRelationPopulated('customer'));
        $this->assertTrue($orders[0]->customer instanceof Customer);
        $this->assertEquals((string) $orders[0]->customer->_id, (string) $orders[0]->customer_id);
        $this->assertTrue($orders[1]->customer instanceof Customer);
        $this->assertEquals((string) $orders[1]->customer->_id, (string) $orders[1]->customer_id);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/5411
     *
     * @depends testFindEager
     */
    public function testFindEagerHasManyByArrayKey()
    {
        $order = CustomerOrder::find()->where(['number' => 1])->with('items')->one();
        $this->assertNotEmpty($order->items);
    }
}
