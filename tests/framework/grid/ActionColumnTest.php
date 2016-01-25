<?php


namespace yiiunit\framework\grid;


use yii\base\Model;
use yii\grid\ActionColumn;

/**
 * @author Vitaly S. <fornit1917@gmail.com>
 *
 * @group grid
 */
class ActionColumnTest extends \yiiunit\TestCase
{
    public function testRenderDataCell()
    {
        $column = new ActionColumn();
        $column->urlCreator = function($model, $key, $index) {
            return 'http://test.com';
        };
        $column->template = '{update}';
        $column->buttons = [
            'update' => function($url, $model, $key) {
                return 'update_button';
            }
        ];

        //test default visible button
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertContains('update_button', $columnContents);

        //test visible button
        $column->visibleButtons = [
            'update' => true
        ];
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertContains('update_button', $columnContents);

        //test visible button (condition is callback)
        $column->visibleButtons = [
            'update' => function($model, $key, $index){return $model['id'] == 1;}
        ];
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertContains('update_button', $columnContents);

        //test invisible button
        $column->visibleButtons = [
            'update' => false
        ];
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertNotContains('update_button', $columnContents);

        //test invisible button (condition is callback)
        $column->visibleButtons = [
            'update' => function($model, $key, $index){return $model['id'] != 1;}
        ];
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertNotContains('update_button', $columnContents);

    }
} 