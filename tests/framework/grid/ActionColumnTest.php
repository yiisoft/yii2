<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\grid;

use yii\grid\ActionColumn;

/**
 * @author Vitaly S. <fornit1917@gmail.com>
 *
 * @group grid
 */
class ActionColumnTest extends \yiiunit\TestCase
{
    public function testInit()
    {
        $column = new ActionColumn();
        $this->assertEquals(['view', 'update', 'delete'], array_keys($column->buttons));

        $column = new ActionColumn(['template' => '{show} {edit} {delete}']);
        $this->assertEquals(['delete'], array_keys($column->buttons));

        $column = new ActionColumn(['template' => '{show} {edit} {remove}']);
        $this->assertEmpty($column->buttons);

        $column = new ActionColumn(['template' => '{view-items} {update-items} {delete-items}']);
        $this->assertEmpty($column->buttons);

        $column = new ActionColumn(['template' => '{view} {view-items}']);
        $this->assertEquals(['view'], array_keys($column->buttons));
    }

    public function testRenderDataCell()
    {
        $column = new ActionColumn();
        $column->urlCreator = function ($model, $key, $index) {
            return 'http://test.com';
        };
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $viewButton = '<a href="http://test.com" title="View" aria-label="View" data-pjax="0"><span class="glyphicon glyphicon-eye-open"></span></a>';
        $updateButton = '<a href="http://test.com" title="Update" aria-label="Update" data-pjax="0"><span class="glyphicon glyphicon-pencil"></span></a>';
        $deleteButton = '<a href="http://test.com" title="Delete" aria-label="Delete" data-pjax="0" data-confirm="Are you sure you want to delete this item?" data-method="post"><span class="glyphicon glyphicon-trash"></span></a>';
        $expectedHtml = "<td>$viewButton $updateButton $deleteButton</td>";
        $this->assertEquals($expectedHtml, $columnContents);

        $column = new ActionColumn();
        $column->urlCreator = function ($model, $key, $index) {
            return 'http://test.com';
        };
        $column->template = '{update}';
        $column->buttons = [
            'update' => function ($url, $model, $key) {
                return 'update_button';
            },
        ];

        //test default visible button
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertContains('update_button', $columnContents);

        //test visible button
        $column->visibleButtons = [
            'update' => true,
        ];
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertContains('update_button', $columnContents);

        //test visible button (condition is callback)
        $column->visibleButtons = [
            'update' => function ($model, $key, $index) {return $model['id'] == 1;},
        ];
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertContains('update_button', $columnContents);

        //test invisible button
        $column->visibleButtons = [
            'update' => false,
        ];
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertNotContains('update_button', $columnContents);

        //test invisible button (condition is callback)
        $column->visibleButtons = [
            'update' => function ($model, $key, $index) {return $model['id'] != 1;},
        ];
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertNotContains('update_button', $columnContents);
    }
}
