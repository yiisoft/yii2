<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\grid;

use yiiunit\TestCase;
use yiiunit\data\base\RulesModel;
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
class DataColumnTest extends TestCase
{
    /**
     * @see DataColumn::getHeaderCellLabel()
     */
    public function testColumnLabelsOnEmptyArrayProvider(): void
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [],
                'totalCount' => 0,
                'modelClass' => Order::class,
            ]),
            'columns' => ['customer_id', 'total'],
        ]);
        $labels = [];
        foreach ($grid->columns as $column) {
            $labels[] = $this->invokeMethod($column, 'getHeaderCellLabel');
        }
        $this->assertEquals(['Customer', 'Invoice Total'], $labels);
    }

    /**
     * @see DataColumn::getHeaderCellLabel()
     */
    public function testColumnLabelsOnEmptyArrayProviderWithFilterModel(): void
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
            $labels[] = $this->invokeMethod($column, 'getHeaderCellLabel');
        }
        $this->assertEquals(['Customer', 'Invoice Total'], $labels);
    }

    /**
     * @see DataColumn::$filter
     * @see DataColumn::renderFilterCellContent()
     */
    public function testFilterInputString(): void
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

        $this->assertEquals($this->invokeMethod($dataColumn, 'renderFilterCellContent'), $filterInput);
    }

    /**
     * @see DataColumn::$filter
     * @see DataColumn::renderFilterCellContent()
     */
    public function testFilterHasMaxLengthWhenIsAnActiveTextInput(): void
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

        $this->assertEquals($this->invokeMethod($dataColumn, 'renderFilterCellContent'), $filterInput);
    }

    /**
     * @see DataColumn::$filter
     * @see DataColumn::renderFilterCellContent()
     */
    public function testFilterInputArray(): void
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

        $this->assertEqualsWithoutLE(
            <<<'HTML'
<select class="form-control" name="Order[customer_id]">
<option value=""></option>
<option value="0">1</option>
<option value="1">2</option>
</select>
HTML
            ,
            $this->invokeMethod($dataColumn, 'renderFilterCellContent'),
        );
    }

    /**
     * @see DataColumn::$filter
     * @see DataColumn::renderFilterCellContent()
     */
    public function testFilterInputFormatBoolean(): void
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

        $this->assertEqualsWithoutLE(
            <<<'HTML'
<select class="form-control" name="Order[customer_id]">
<option value=""></option>
<option value="1">Yes</option>
<option value="0">No</option>
</select>
HTML
            ,
            $this->invokeMethod($dataColumn, 'renderFilterCellContent'),
        );
    }

    public function testRenderHeaderCellContentWithHeaderSet(): void
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['id' => 1]]]),
            'columns' => [
                ['attribute' => 'id', 'header' => 'Custom Header'],
            ],
        ]);

        $header = $grid->columns[0]->renderHeaderCell();
        $this->assertSame('<th>Custom Header</th>', $header);
    }

    public function testRenderHeaderCellContentWithSortLink(): void
    {
        $this->mockWebApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [['id' => 1, 'name' => 'test']],
                'sort' => [
                    'attributes' => ['id'],
                    'route' => '/',
                ],
            ]),
            'columns' => ['id'],
        ]);

        $header = $grid->columns[0]->renderHeaderCell();
        $this->assertStringContainsString('<a', $header);
        $this->assertStringContainsString('Id', $header);
    }

    public function testRenderHeaderCellContentEncodedLabel(): void
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['id' => 1]]]),
            'columns' => [
                ['attribute' => 'id', 'label' => '<b>Bold</b>'],
            ],
        ]);

        $header = $grid->columns[0]->renderHeaderCell();
        $this->assertStringContainsString('&lt;b&gt;Bold&lt;/b&gt;', $header);
    }

    public function testRenderHeaderCellContentNotEncodedLabel(): void
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['id' => 1]]]),
            'columns' => [
                ['attribute' => 'id', 'label' => '<b>Bold</b>', 'encodeLabel' => false],
            ],
        ]);

        $header = $grid->columns[0]->renderHeaderCell();
        $this->assertStringContainsString('<b>Bold</b>', $header);
    }

    public function testGetHeaderCellLabelWithNullAttribute(): void
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['id' => 1]]]),
            'columns' => [
                ['class' => DataColumn::class, 'label' => null, 'attribute' => null],
            ],
        ]);

        $label = $this->invokeMethod($grid->columns[0], 'getHeaderCellLabel');
        $this->assertSame('', $label);
    }

    public function testGetHeaderCellLabelFromArrayModels(): void
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [['id' => 1, 'some_attribute' => 'val']],
            ]),
            'columns' => ['some_attribute'],
        ]);

        $label = $this->invokeMethod($grid->columns[0], 'getHeaderCellLabel');
        $this->assertSame('Some Attribute', $label);
    }

    public function testGetHeaderCellLabelFromModelInstances(): void
    {
        $this->mockApplication();
        $model = new Singer();
        $model->firstName = 'John';
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [$model],
            ]),
            'columns' => ['firstName'],
        ]);

        $label = $this->invokeMethod($grid->columns[0], 'getHeaderCellLabel');
        $this->assertSame('First Name', $label);
    }

    public function testGetHeaderCellLabelWithExplicitLabel(): void
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'columns' => [
                ['attribute' => 'id', 'label' => 'My Label'],
            ],
        ]);

        $label = $this->invokeMethod($grid->columns[0], 'getHeaderCellLabel');
        $this->assertSame('My Label', $label);
    }

    public function testGetDataCellValueWithStringValue(): void
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'columns' => [
                ['attribute' => 'id', 'value' => 'name'],
            ],
        ]);

        $column = $grid->columns[0];
        $result = $column->getDataCellValue(['id' => 1, 'name' => 'test'], 1, 0);
        $this->assertSame('test', $result);
    }

    public function testGetDataCellValueWithClosureValue(): void
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'columns' => [
                [
                    'attribute' => 'id',
                    'value' => function ($model, $key, $index, $column) {
                        return $model['id'] * 10;
                    },
                ],
            ],
        ]);

        $column = $grid->columns[0];
        $result = $column->getDataCellValue(['id' => 5], 1, 0);
        $this->assertSame(50, $result);
    }

    public function testGetDataCellValueReturnsNull(): void
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'columns' => [
                ['class' => DataColumn::class],
            ],
        ]);

        $column = $grid->columns[0];
        $result = $column->getDataCellValue(['id' => 1], 1, 0);
        $this->assertNull($result);
    }

    public function testRenderDataCellContentWithFormat(): void
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'columns' => [
                ['attribute' => 'name', 'format' => 'text'],
            ],
        ]);

        $column = $grid->columns[0];
        $result = $column->renderDataCell(['name' => '<b>html</b>'], 1, 0);
        $this->assertSame('<td>&lt;b&gt;html&lt;/b&gt;</td>', $result);
    }

    public function testRenderDataCellContentWithContentCallback(): void
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'columns' => [
                [
                    'attribute' => 'id',
                    'content' => function ($model, $key, $index, $column) {
                        return 'custom-' . $model['id'];
                    },
                ],
            ],
        ]);

        $column = $grid->columns[0];
        $result = $column->renderDataCell(['id' => 7], 1, 0);
        $this->assertSame('<td>custom-7</td>', $result);
    }

    public function testRenderFilterCellContentFallbackToParent(): void
    {
        $this->mockApplication();
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'columns' => [
                ['attribute' => 'id', 'filter' => false],
            ],
            'filterModel' => new RulesModel(['rules' => [['user_id', 'safe']]]),
        ]);

        $column = $grid->columns[0];
        $result = $this->invokeMethod($column, 'renderFilterCellContent');
        $this->assertSame('&nbsp;', $result);
    }

    public function testRenderFilterCellContentWithErrors(): void
    {
        $this->mockApplication();
        $model = new RulesModel(['rules' => [['user_id', 'integer']]]);
        $model->user_id = 'not-a-number';
        $model->validate();

        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'columns' => [
                ['attribute' => 'user_id', 'filterAttribute' => 'user_id'],
            ],
            'filterModel' => $model,
        ]);

        $column = $grid->columns[0];
        $result = $this->invokeMethod($column, 'renderFilterCellContent');
        $this->assertStringContainsString('help-block', $result);
        $this->assertStringContainsString(' <div', $result);

        $filterCell = $column->renderFilterCell();
        $this->assertStringContainsString('has-error', $filterCell);
    }

    /**
     * @see DataColumn::$filterAttribute
     * @see DataColumn::renderFilterCellContent()
     */
    public function testFilterInputArrayWithErrors(): void
    {
        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => '\yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);
        $columns = ['id' => 'pk', 'customer_id' => 'integer'];
        ActiveRecord::$db = Yii::$app->getDb();
        Yii::$app->getDb()->createCommand()->createTable(Order::tableName(), $columns)->execute();

        $model = new Order();
        $model->addError('customer_id', 'Test error');

        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [], 'totalCount' => 0]),
            'columns' => [
                ['attribute' => 'customer_id', 'filter' => [1, 2]],
            ],
            'filterModel' => $model,
        ]);

        $dataColumn = $grid->columns[0];
        $result = $this->invokeMethod($dataColumn, 'renderFilterCellContent');
        $this->assertStringContainsString('</select> <div', $result);
    }

    public function testFilterInputFormatBooleanWithErrors(): void
    {
        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => '\yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);
        $columns = ['id' => 'pk', 'customer_id' => 'integer'];
        ActiveRecord::$db = Yii::$app->getDb();
        Yii::$app->getDb()->createCommand()->createTable(Order::tableName(), $columns)->execute();

        $model = new Order();
        $model->addError('customer_id', 'Test error');

        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [], 'totalCount' => 0]),
            'columns' => [
                ['attribute' => 'customer_id', 'format' => 'boolean'],
            ],
            'filterModel' => $model,
        ]);

        $dataColumn = $grid->columns[0];
        $result = $this->invokeMethod($dataColumn, 'renderFilterCellContent');
        $this->assertStringContainsString('</select> <div', $result);
    }

    public function testFilterInputWithFilterAttribute(): void
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
            'filterModel' => new RulesModel(['rules' => [['user_id', 'safe']]]),
        ]);

        $dataColumn = $grid->columns[0];

        $this->assertEquals(
            '<input type="text" class="form-control" name="RulesModel[user_id]">',
            $this->invokeMethod($dataColumn, 'renderFilterCellContent'),
        );
    }
}
