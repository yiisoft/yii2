<?php


namespace yiiunit\framework\grid;

use yii\data\ArrayDataProvider;
use yii\grid\DataColumn;
use yii\grid\GridView;

/**
 * @author Evgeniy Tkachenko <et.coder@gmail.com>
 * @group grid
 */
class GridViewTest extends \yiiunit\TestCase
{
    public function testGuessColumns()
    {
        $this->mockApplication();
        $row = ['id' => 1, 'name' => 'Name1', 'value' => 'Value1', 'description' => 'Description1',];

        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(
                [
                    'allModels' => [
                        $row,
                    ],
                ]
            ),
        ]);

        $columns = $grid->columns;
        $this->assertCount(count($row), $columns);

        foreach ($columns as $index => $column) {
            $this->assertInstanceOf(DataColumn::className(), $column);
            $this->assertArrayHasKey($column->attribute, $row);
        }

        $row = array_merge($row, ['relation' => ['id' => 1, 'name' => 'RelationName',],]);
        $row = array_merge($row, ['otherRelation' => (object)$row['relation']]);

        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(
                [
                    'allModels' => [
                        $row,
                    ],
                ]
            ),
        ]);

        $columns = $grid->columns;
        $this->assertCount(count($row) - 2, $columns);

        foreach ($columns as $index => $column) {
            $this->assertInstanceOf(DataColumn::className(), $column);
            $this->assertArrayHasKey($column->attribute, $row);
            $this->assertNotEquals('relation', $column->attribute);
            $this->assertNotEquals('otherRelation', $column->attribute);
        }
    }
}
