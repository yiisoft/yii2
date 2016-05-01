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

        $this->assertEquals([
            'id' => 1,
            'customer_id' => 1
        ], $order->toArray());

        // expand customer and items
        $this->assertEquals([
            'id' => 1,
            'customer_id' => 1,
            'customer' => [
                'id' => 1,
            ],
            'items' => [
                ['product_id' => 1, 'qty' => 10],
                ['product_id' => 2, 'qty' => 3426]
            ]
        ], $order->toArray([], ['customer', 'items']));

        // expand sub items and exclude customer.id
        $this->assertEquals([
            'id' => 1,
            'customer_id' => 1,
            'customer' => [
                'name' => 'Munir'
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
                ]
            ]
        ], $order->toArray([], ['customer.name', 'items.product.name'], ['customer.id']));
        
        // expand sub items and exclude all id field
        $this->assertEquals([
            'customer_id' => 1,
            'customer' => [
                'name' => 'Munir'
            ],
            'items' => [
                ['product_id' => 1, 'qty' => 10,
                    'product' => [
                        'name' => 'Product satu'
                    ]
                ],
                ['product_id' => 2, 'qty' => 3426,
                    'product' => [
                        'name' => 'Product dua'
                    ]
                ]
            ]
        ], $order->toArray([], ['customer.name', 'items.product.name'], ['*.id']));
    }
}
