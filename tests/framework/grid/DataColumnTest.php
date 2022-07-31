<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\grid;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Order;
use yiiunit\data\base\Singer;

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
                'modelClass' => Order::className(),
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
    public function testFilterHasMaxLengthWhenIsAnActiveTextInput()
    {
        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => '\yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);

        ActiveRecord::$db = Yii::$app->getDb();
        Yii::$app->getDb()->createCommand()->createTable(Singer::tableName(), [
            'firstName' => 'string',
            'lastName' => 'string'
        ])->execute();

        $filterInput = '<input type="text" class="form-control" name="Singer[lastName]" maxlength="25">';
        $grid = new GridView([
            'dataProvider' => new ActiveDataProvider(),
            'filterModel' => new Singer(),
            'columns' => [
                0 => 'lastName'
            ],
        ]);

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
                    'class' => '\yii\db\Connection',
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
                    'class' => '\yii\db\Connection',
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

    /**
     * @see DataColumn::$filterAttribute
     * @see DataColumn::renderFilterCellContent()
     */
    public function testFilterInputWithFilterAttribute()
    {
        $this->mockApplication();

        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [],
            ]),
            'columns' => [
                0 => [
                    'attribute' => 'username',
                    'filterAttribute' => 'user_id',
                ],
            ],
            'filterModel' => new \yiiunit\data\base\RulesModel(['rules' => [['user_id', 'safe']]]),
        ]);

        $dataColumn = $grid->columns[0];
        $method = new \ReflectionMethod($dataColumn, 'renderFilterCellContent');
        $method->setAccessible(true);
        $result = $method->invoke($dataColumn);

        $this->assertEquals('<input type="text" class="form-control" name="RulesModel[user_id]">', $result);
    }
}
