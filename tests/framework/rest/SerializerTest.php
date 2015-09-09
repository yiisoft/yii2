<?php

namespace yiiunit\framework\rest;

use yiiunit\TestCase;
use yiiunit\framework\base\stubs\Order;
use yii\rest\Serializer;

/**
 * @group rest
 */
class SerializerTest extends TestCase
{

    public function testExpandField()
    {
        $this->mockWebApplication();

        $model = new Order(['id' => 1, 'customer_id' => 1]);
        $model->addItem(['product_id' => 1, 'qty' => 10]);
        $model->addItem(['product_id' => 2, 'qty' => 3426]);

        $seralizer = new Serializer();
        $_GET['expand'] = 'customer, items';
        $data = $seralizer->serialize($model);
        $this->assertEquals([
            'id' => 1, 'customer_id' => 1,
            'customer' => ['id' => 1,],
            'items' => [
                ['product_id' => 1, 'qty' => 10,],
                ['product_id' => 2, 'qty' => 3426,],
            ]
            ], $data);


        $_GET['expand'] = 'customer.name, items.product.name';
        $data = $seralizer->serialize($model);
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
