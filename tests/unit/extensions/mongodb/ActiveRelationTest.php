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
        $customerCollection = $this->getConnection()->getCollection('customer');

        $customers = [];
        for ($i = 1; $i <= 5; $i++) {
            $customers[] = [
                'name' => 'name' . $i,
                'email' => 'email' . $i,
                'address' => 'address' . $i,
                'status' => $i,
            ];
        }
        $customerCollection->batchInsert($customers);

        $customerOrderCollection = $this->getConnection()->getCollection('customer_order');
        $customerOrders = [];
        foreach ($customers as $customer) {
            $customerOrders[] = [
                'customer_id' => $customer['_id'],
                'number' => $customer['status'],
            ];
            $customerOrders[] = [
                'customer_id' => $customer['_id'],
                'number' => $customer['status'] + 100,
            ];
        }
        $customerOrderCollection->batchInsert($customerOrders);
    }

    // Tests :

    public function testFindLazy()
    {
        /** @var CustomerOrder $order */
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
}
