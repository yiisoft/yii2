<?php

namespace yiiunit\framework\grid;

use yii\data\ArrayDataProvider;
use yii\grid\Column;
use PHPUnit\Framework\TestCase;
use yii\grid\GridView;
use yii\web\View;


class WrapperContentOption
{
    public function getContentOptionsCellRenderClosureArray($model, $key, $index, $column)
    {
        return [
            'data-test-content-option' => 'ClosureArray'
        ];
    }
}

class ColumnTest extends \yiiunit\TestCase
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

    public function testRenderDataCell()
    {
        $model = [
            'id' => 1,
            'reason' => 'test render closure options'
        ];

        $grid = new  GridView([
            'id' => 'grid',
            'dataProvider' => new ArrayDataProvider(['allModels' => [ $model ]]),
            'view' => new View(),
        ]);

        // test array contentOptions
        $columnArrayDD = new Column([
            'grid' => $grid,
            'contentOptions' => [
                'data-test-content-option' => 'Array'
            ],
        ]);
        $this->assertContains('data-test-content-option="Array"', $columnArrayDD->renderDataCell($model,'id',0));

        // test closure contentOptions
        $columnClosureDD = new Column([
            'grid' => $grid,
            'contentOptions' => function($data, $key, $index, $column) {
                return [
                    'data-test-content-option' => 'Closure'
                ];
            }
        ]);
        $this->assertContains('data-test-content-option="Closure"', $columnClosureDD->renderDataCell($model,'id',0));

        // test closure array
        $object = new WrapperContentOption();
        $columnClosureDD = new Column([
            'grid' => $grid,
            'contentOptions' => [$object, 'getContentOptionsCellRenderClosureArray'] /** @see WrapperContentOption::getContentOptionsCellRenderClosureArray() */
        ]);
        $this->assertContains('data-test-content-option="ClosureArray"', $columnClosureDD->renderDataCell($model,'id',0));


    }
}
