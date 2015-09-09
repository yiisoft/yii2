<?php

namespace yiiunit\framework\base;

use yiiunit\TestCase;
use yiiunit\framework\base\stubs\Order;

/**
 * @group base
 */
class ArrayableTest extends TestCase
{

    public function testExpandField()
    {
        $order = new Order(['id' => 1, 'customer_id' => 1]);
        $order->addItem(['product_id' => 1, 'qty' => 10]);
        $order->addItem(['product_id' => 2, 'qty' => 3426]);

        $data = $order->toArray();
        $this->assertEquals(['id' => 1, 'customer_id' => 1], $data);
        $this->assertArrayNotHasKey('items', $data);
        $this->assertArrayNotHasKey('customer', $data);

        $data = $order->toArray([], ['customer', 'items']);
        $this->assertArrayHasKey('customer', $data);
        $this->assertArrayNotHasKey('name', $data['customer']);
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('qty', $data['items'][0]);
        $this->assertArrayNotHasKey('product', $data['items'][0]);


        $data = $order->toArray([], ['items.product']);
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('product', $data['items'][0]);
        $this->assertArrayHasKey('id', $data['items'][0]['product']);
        $this->assertArrayNotHasKey('name', $data['items'][0]['product']);

        $data = $order->toArray([], ['customer.name', 'items.product.name']);
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('name', $data['items'][0]['product']);
        $this->assertEquals([
            'id' => 1, 'customer_id' => 1,
            'customer' => [
                'id' => 1,
                'name' => 'Misbahul'
            ],
            'items' => [
                ['product_id' => 1, 'qty' => 10,
                    'product' => [
                        'id' => 1,
                        'name' => 'Product satu'
                    ]
                ],
                ['product_id' => 2, 'qty' => 3426,
                    'product' => [
                        'id' => 2,
                        'name' => 'Product dua'
                    ]
                ],
            ]
            ], $data);
    }
}
