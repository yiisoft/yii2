<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\grid;

use yii\data\ArrayDataProvider;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/**
 * @author Evgeniy Tkachenko <et.coder@gmail.com>
 * @group grid
 */
class GridViewTest extends \yiiunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication([
            'components' => [
                'assetManager' => [
                    'bundles' => [
                        'yii\grid\GridViewAsset' => false,
                        'yii\web\JqueryAsset' => false,
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return array
     */
    public function emptyDataProvider()
    {
        return [
            [null, 'No results found.', [], []],
            ['Empty', 'Empty', [], []],
            // https://github.com/yiisoft/yii2/issues/13352
            [false, '', [], []],
            [null, 'No results found.', ['class' => 'test-class'], ['class' => 'test-class']],
            [null, 'No results found.', function ($model, $key, $index, $grid){
                return ['class' => 'test-class'];
            }, ['class' => 'test-class']],
        ];
    }

    /**
     * @dataProvider emptyDataProvider
     * @param mixed $emptyText
     * @param string $expectedText
     * @param null|array|\Closure $rowOptions
     * @param null|array|\Closure $expectedRowOptions
     * @throws \Exception
     */
    public function testEmpty($emptyText, $expectedText, $rowOptions, $expectedRowOptions)
    {
        $html = GridView::widget([
            'id' => 'grid',
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'showHeader' => false,
            'emptyText' => $emptyText,
            'options' => [],
            'tableOptions' => [],
            'rowOptions' => $rowOptions,
            'view' => new View(),
            'filterUrl' => '/',
        ]);
        $html = preg_replace("/\r|\n/", '', $html);

        if ($expectedText) {
            $emptyRowHtml = Html::tag('tr',
                "<td colspan=\"0\"><div class=\"empty\">{$expectedText}</div></td>",
                $expectedRowOptions);
        } else {
            $emptyRowHtml = '';
        }
        $expectedHtml = "<div id=\"grid\"><table><tbody>{$emptyRowHtml}</tbody></table></div>";

        $this->assertEquals($expectedHtml, $html);
    }

    public function testGuessColumns()
    {
        $row = ['id' => 1, 'name' => 'Name1', 'value' => 'Value1', 'description' => 'Description1'];

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

        $row = array_merge($row, ['relation' => ['id' => 1, 'name' => 'RelationName']]);
        $row = array_merge($row, ['otherRelation' => (object) $row['relation']]);

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
