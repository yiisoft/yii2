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
    }

    public function testTemplate()
    {
        $column = new ActionColumn();
        $column->urlCreator = function ($action, $model, $key, $index) {
            return "/$action/";
        };
        $content = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertContains('/view/', $content);
        $this->assertContains('/update/', $content);
        $this->assertContains('/delete/', $content);

        $column->template = '{show} {edit} {delete}';
        $content = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertNotContains('/view/', $content);
        $this->assertNotContains('/update/', $content);
        $this->assertContains('/delete/', $content);

        $column->template = '{show} {edit} {remove}';
        $content = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertSame('<td>  </td>', $content);
    }

    public function testRenderDataCell()
    {
        $column = new ActionColumn();
        $column->urlCreator = function ($model, $key, $index) {
            return 'http://test.com';
        };
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $viewButton = '<a href="http://test.com" title="View" aria-label="View"><span class="icon icon-eye-open"></span></a>';
        $updateButton = '<a href="http://test.com" title="Update" aria-label="Update"><span class="icon icon-pencil"></span></a>';
        $deleteButton = '<a href="http://test.com" title="Delete" aria-label="Delete" data-confirm="Are you sure you want to delete this item?" data-method="post"><span class="icon icon-trash"></span></a>';
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

    /**
     * @depends testRenderDataCell
     */
    public function testOverrideDefaultButtons()
    {
        $column = new ActionColumn([
            'buttons' => [
                'view' => [
                    'url' => '/view-override/'
                ],
                'update' => [
                    'icon' => 'override'
                ],
                'delete' => [
                    'visible' => false
                ],
            ],
        ]);
        $column->urlCreator = function ($action, $model, $key, $index) {
            return "/$action/";
        };
        $content = $column->renderDataCell(['id' => 1], 1, 0);

        $viewButton = '<a href="/view-override/" title="View" aria-label="View"><span class="icon icon-eye-open"></span></a>';
        $updateButton = '<a href="/update/" title="Update" aria-label="Update"><span class="icon icon-override"></span></a>';
        $deleteButton = '';
        $expectedHtml = "<td>$viewButton $updateButton $deleteButton</td>";
        $this->assertSame($expectedHtml, $content);
    }

    /**
     * @depends testRenderDataCell
     */
    public function testButtonOptions()
    {
        $column = new ActionColumn();
        $column->buttonOptions = [
            'title' => false,
            'aria-label' => false,
        ];
        $column->urlCreator = function ($action, $model, $key, $index) {
            return "/$action/";
        };
        $content = $column->renderDataCell(['id' => 1], 1, 0);

        $this->assertNotContains('title="', $content);
        $this->assertNotContains('aria-label="', $content);
    }
}
