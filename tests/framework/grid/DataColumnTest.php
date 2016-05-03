<?php


namespace yiiunit\framework\grid;

use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yiiunit\data\ar\Order;

/**
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 *
 * @group grid
 */
class DataColumnTest extends \yiiunit\TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testColumnLabelsOnEmptyArrayProvider()
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [],
                'totalCount' => 0,
                'modelClass' => Order::className()
            ]),
            'columns' => ['customer_id', 'total']
        ]);

        $labels = [];
        foreach ($grid->columns as $column) {
            $method = new \ReflectionMethod($column, 'getHeaderCellLabel');
            $method->setAccessible(true);
            $labels[] = $method->invoke($column);
            $method->setAccessible(false);
        }

        $this->assertEquals(['Customer', 'Invoice Total'], $labels);
    }

    public function testColumnLabelsOnEmptyArrayProviderWithFilterModel()
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [],
                'totalCount' => 0,
            ]),
            'columns' => ['customer_id', 'total'],
            'filterModel' => new Order
        ]);

        $labels = [];
        foreach ($grid->columns as $column) {
            $method = new \ReflectionMethod($column, 'getHeaderCellLabel');
            $method->setAccessible(true);
            $labels[] = $method->invoke($column);
            $method->setAccessible(false);
        }

        $this->assertEquals(['Customer', 'Invoice Total'], $labels);
    }
}
