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

        $_GET['fields'] = '';
        $this->assertEquals([
            'id' => 1, 'customer_id' => 1,
            ], $seralizer->serialize($model));

        $_GET['fields'] = '*, customer, items';
        $this->assertEquals([
            'id' => 1, 'customer_id' => 1,
            'customer' => ['id' => 1],
            'items' => [
                ['product_id' => 1, 'qty' => 10,],
                ['product_id' => 2, 'qty' => 3426,],
            ]
            ], $seralizer->serialize($model));


        $_GET['fields'] = '*, customer.name, items.product.name';
        $this->assertEquals([
            'id' => 1,
            'customer_id' => 1,
            'customer' => [
                'name' => 'Munir'
            ],
            'items' => [
                [
                    'product' => [
                        'name' => 'Product satu'
                    ]
                ],
                [
                    'product' => [
                        'name' => 'Product dua'
                    ]
                ],
            ]
            ], $seralizer->serialize($model));
    }
}