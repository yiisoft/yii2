<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\grid;

use Yii;
use yii\data\ArrayDataProvider;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\i18n\Formatter;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Order;

/**
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 *
 * @group grid
 */
class DataColumnTest extends \yiiunit\TestCase
{
    /**
     * @see DataColumn::getHeaderCellLabel()
     */
    public function testColumnLabels_OnEmpty_ArrayProvider()
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [],
                'totalCount' => 0,
                'modelClass' => Order::class
            ]),
            'columns' => ['customer_id', 'total'],
        ]);
        $labels = [];
        foreach ($grid->columns as $column) {
            $method = new \ReflectionMethod($column, 'getHeaderCellLabel');
            $method->setAccessible(true);
            $labels[] = $method->invoke($column);
        }
        $this->assertEquals(['Customer', 'Invoice Total'], $labels);
    }

    /**
     * @see DataColumn::getHeaderCellLabel()
     */
    public function testColumnLabels_OnEmpty_ArrayProvider_WithFilterModel()
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [],
                'totalCount' => 0,
            ]),
            'columns' => ['customer_id', 'total'],
            'filterModel' => new Order(),
        ]);
        $labels = [];
        foreach ($grid->columns as $column) {
            $method = new \ReflectionMethod($column, 'getHeaderCellLabel');
            $method->setAccessible(true);
            $labels[] = $method->invoke($column);
        }
        $this->assertEquals(['Customer', 'Invoice Total'], $labels);
    }

    /**
     * @see DataColumn::$filter
     * @see DataColumn::renderFilterCellContent()
     */
    public function testFilterInput_String()
    {
        $this->mockApplication();
        $filterInput = '<input type="text"/>';
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [],
                'totalCount' => 0,
            ]),
            'columns' => [
                0 => [
                    'attribute' => 'customer_id',
                    'filter' => $filterInput,
                ],
            ],
        ]);
        //print_r($grid->columns);exit();
        $dataColumn = $grid->columns[0];
        $method = new \ReflectionMethod($dataColumn, 'renderFilterCellContent');
        $method->setAccessible(true);
        $result = $method->invoke($dataColumn);
        $this->assertEquals($result, $filterInput);
    }


    /**
     * @see DataColumn::$filter
     * @see DataColumn::renderFilterCellContent()
     */
    public function testFilterInput_Array()
    {
        $this->mockApplication([
            'components' => [
                'db' => [
                    '__class' => \yii\db\Connection::class,
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);
        $columns = [
            'id' => 'pk',
            'customer_id' => 'integer',
        ];
        ActiveRecord::$db = Yii::$app->getDb();
        Yii::$app->getDb()->createCommand()->createTable(Order::tableName(), $columns)->execute();

        $filterInput = [1, 2];
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [],
                'totalCount' => 0,
            ]),
            'columns' => [
                0 => [
                    'attribute' => 'customer_id',
                    'filter' => $filterInput,
                ],
            ],
            'filterModel' => new Order(),
        ]);

        $dataColumn = $grid->columns[0];
        $method = new \ReflectionMethod($dataColumn, 'renderFilterCellContent');
        $method->setAccessible(true);
        $result = $method->invoke($dataColumn);

        $this->assertEqualsWithoutLE(<<<'HTML'
<select class="form-control" name="Order[customer_id]">
<option value=""></option>
<option value="0">1</option>
<option value="1">2</option>
</select>
HTML
            , $result);
    }

    /**
     * @see DataColumn::$filter
     * @see DataColumn::renderFilterCellContent()
     */
    public function testFilterInput_FormatBoolean()
    {
        $this->mockApplication([
            'components' => [
                'db' => [
                    '__class' => \yii\db\Connection::class,
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);
        $columns = [
            'id' => 'pk',
            'customer_id' => 'integer',
        ];
        ActiveRecord::$db = Yii::$app->getDb();
        Yii::$app->getDb()->createCommand()->createTable(Order::tableName(), $columns)->execute();

        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [],
                'totalCount' => 0,
            ]),
            'columns' => [
                0 => [
                    'attribute' => 'customer_id',
                    'format' => 'boolean', // does not make sense for this column but should still output proper dropdown list
                ],
            ],
            'filterModel' => new Order(),
        ]);

        $dataColumn = $grid->columns[0];
        $method = new \ReflectionMethod($dataColumn, 'renderFilterCellContent');
        $method->setAccessible(true);
        $result = $method->invoke($dataColumn);

        $this->assertEqualsWithoutLE(<<<'HTML'
<select class="form-control" name="Order[customer_id]">
<option value=""></option>
<option value="1">Yes</option>
<option value="0">No</option>
</select>
HTML
            , $result);
    }

    public function filterOptionsProvider()
    {
        return [
            [
                false,
                '<td><input type="text" class="form-control" name="Order[customer_id]"></td>',
            ],
            [
                ['class' => 'form-control'],
                '<td><input type="text" class="form-control" name="Order[customer_id]"></td>'
            ],
            [
                ['class' => 'form-control', 'data' => ['value' => "1"]],
                '<td><input type="text" class="form-control" name="Order[customer_id]" data-value="1"></td>'
            ],
            [
                ['class' => ''],
                '<td><input type="text" class="" name="Order[customer_id]"></td>'
            ],
            [
                ['class' => null],
                '<td><input type="text" name="Order[customer_id]"></td>'
            ],
            [
                ['class' => 'form-control', 'id' => 'customer_id'],
                '<td><input type="text" id="customer_id" class="form-control" name="Order[customer_id]"></td>'
            ],
            [
                ['class' => '', 'id' => 'customer_id'],
                '<td><input type="text" id="customer_id" class="" name="Order[customer_id]"></td>'
            ],
            [
                ['class' => null, 'id' => 'customer_id'],
                '<td><input type="text" id="customer_id" name="Order[customer_id]"></td>'
            ],
        ];
    }


    /**
     * @dataProvider filterOptionsProvider
     */
    public function testFilterInput_Options($options, $expectedHtml)
    {
        $this->mockApplication();
        $column = new DataColumn([
            'attribute' => 'customer_id',
            'grid' => new GridView([
                'filterModel' => new Order,
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => [],
                    'totalCount' => 0,
                ]),
            ]),
        ]);
        if ($options !== false) {
            $column->filterInputOptions = $options;
        }

        $filterCell = $column->renderFilterCell();
        $this->assertEqualsWithoutLE($expectedHtml, $filterCell);
    }
}
