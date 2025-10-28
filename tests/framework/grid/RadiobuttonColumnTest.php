<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\grid;

use yii\base\InvalidConfigException;
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
    public function testException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('The "name" property must be set.');

        new RadioButtonColumn(['name' => null]);
    }

    public function testOptionsByArray(): void
    {
        $column = new RadioButtonColumn([
            'radioOptions' => [
                'value' => 42,
            ],
        ]);
        $this->assertEquals('<td><input type="radio" name="radioButtonSelection" value="42"></td>', $column->renderDataCell([], 1, 0));
    }

    public function testOptionsByCallback(): void
    {
        $model = [
            'label' => 'label',
            'value' => 123,
        ];
        $column = new RadioButtonColumn([
            'radioOptions' => fn($model) => [
                'value' => $model['value'],
            ],
        ]);
        $actual = $column->renderDataCell($model, 1, 0);
        $this->assertEquals('<td><input type="radio" name="radioButtonSelection" value="' . $model['value'] . '"></td>', $actual);
    }

    public function testContent(): void
    {
        $column = new RadioButtonColumn([
            'content' => fn($model, $key, $index, $column) => null
        ]);
        $this->assertStringContainsString('<td></td>', $column->renderDataCell([], 1, 0));

        $column = new RadioButtonColumn([
            'content' => fn($model, $key, $index, $column) => Html::radio('radioButtonInput', false)
        ]);
        $this->assertStringContainsString(Html::radio('radioButtonInput', false), $column->renderDataCell([], 1, 0));
    }

    public function testMultipleInGrid(): void
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
                    'class' => RadioButtonColumn::class,
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
