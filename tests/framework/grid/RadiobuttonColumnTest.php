<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\grid;

use Yii;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\grid\RadioButtonColumn;
use yii\helpers\Html;
use yii\web\Request;
use yiiunit\TestCase;

/**
 * Class RadiobuttonColumnTest.
 * @group grid
 * @since 2.0.11
 */
class RadiobuttonColumnTest extends TestCase
{
    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage The "name" property must be set.
     */
    public function testException()
    {
        new RadioButtonColumn([
            'name' => null,
        ]);
    }

    public function testOptionsByArray()
    {
        $column = new RadioButtonColumn([
            'radioOptions' => [
                'value' => 42,
            ],
        ]);
        $this->assertEquals('<td><input type="radio" name="radioButtonSelection" value="42"></td>', $column->renderDataCell([], 1, 0));
    }

    public function testOptionsByCallback()
    {
        $model = [
            'label' => 'label',
            'value' => 123,
        ];
        $column = new RadioButtonColumn([
            'radioOptions' => function ($model) {
                return [
                    'value' => $model['value'],
                ];
            },
        ]);
        $actual = $column->renderDataCell($model, 1, 0);
        $this->assertEquals('<td><input type="radio" name="radioButtonSelection" value="' . $model['value'] . '"></td>', $actual);
    }

    public function testContent()
    {
        $column = new RadioButtonColumn([
            'content' => function ($model, $key, $index, $column) {
                return null;
            }
        ]);
        $this->assertContains('<td></td>', $column->renderDataCell([], 1, 0));

        $column = new RadioButtonColumn([
            'content' => function ($model, $key, $index, $column) {
                return Html::radio('radioButtonInput', false);
            }
        ]);
        $this->assertContains(Html::radio('radioButtonInput', false), $column->renderDataCell([], 1, 0));
    }

    public function testMultipleInGrid()
    {
        $this->mockApplication();
        Yii::setAlias('@webroot', '@yiiunit/runtime');
        Yii::setAlias('@web', 'http://localhost/');
        Yii::$app->assetManager->bundles['yii\web\JqueryAsset'] = false;
        Yii::$app->set('request', new Request(['url' => '/abc']));

        $models = [
            ['label' => 'label1', 'value' => 1],
            ['label' => 'label2', 'value' => 2, 'checked' => true],
        ];
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => $models]),
            'options' => ['id' => 'radio-gridview'],
            'columns' => [
                [
                    'class' => RadioButtonColumn::className(),
                    'radioOptions' => function ($model) {
                        return [
                            'value' => $model['value'],
                            'checked' => $model['value'] == 2,
                        ];
                    },
                ],
            ],
        ]);
        ob_start();
        $grid->run();
        $actual = ob_get_clean();
        $this->assertEqualsWithoutLE(<<<'HTML'
<div id="radio-gridview"><div class="summary">Showing <b>1-2</b> of <b>2</b> items.</div>
<table class="table table-striped table-bordered"><thead>
<tr><th>&nbsp;</th></tr>
</thead>
<tbody>
<tr data-key="0"><td><input type="radio" name="radioButtonSelection" value="1"></td></tr>
<tr data-key="1"><td><input type="radio" name="radioButtonSelection" value="2" checked></td></tr>
</tbody></table>
</div>
HTML
            , $actual);
    }
}
