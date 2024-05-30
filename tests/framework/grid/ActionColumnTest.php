<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\grid;

use yii\grid\ActionColumn;
use yii\helpers\Html;

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
        $viewButton = '<a href="http://test.com" title="View" aria-label="View" data-pjax="0"><svg aria-hidden="true" style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:1.125em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M573 241C518 136 411 64 288 64S58 136 3 241a32 32 0 000 30c55 105 162 177 285 177s230-72 285-177a32 32 0 000-30zM288 400a144 144 0 11144-144 144 144 0 01-144 144zm0-240a95 95 0 00-25 4 48 48 0 01-67 67 96 96 0 1092-71z"/></svg></a>';
        $updateButton = '<a href="http://test.com" title="Update" aria-label="Update" data-pjax="0"><svg aria-hidden="true" style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:1em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M498 142l-46 46c-5 5-13 5-17 0L324 77c-5-5-5-12 0-17l46-46c19-19 49-19 68 0l60 60c19 19 19 49 0 68zm-214-42L22 362 0 484c-3 16 12 30 28 28l122-22 262-262c5-5 5-13 0-17L301 100c-4-5-12-5-17 0zM124 340c-5-6-5-14 0-20l154-154c6-5 14-5 20 0s5 14 0 20L144 340c-6 5-14 5-20 0zm-36 84h48v36l-64 12-32-31 12-65h36v48z"/></svg></a>';
        $deleteButton = '<a href="http://test.com" title="Delete" aria-label="Delete" data-pjax="0" data-confirm="Are you sure you want to delete this item?" data-method="post"><svg aria-hidden="true" style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:.875em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M32 464a48 48 0 0048 48h288a48 48 0 0048-48V128H32zm272-256a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zM432 32H312l-9-19a24 24 0 00-22-13H167a24 24 0 00-22 13l-9 19H16A16 16 0 000 48v32a16 16 0 0016 16h416a16 16 0 0016-16V48a16 16 0 00-16-16z"/></svg></a>';
        $expectedHtml = "<td>$viewButton $updateButton $deleteButton</td>";
        $this->assertEquals($expectedHtml, $columnContents);

        $column = new ActionColumn();
        $column->urlCreator = function ($model, $key, $index) {
            return 'http://test.com';
        };
        $column->template = '{update}';

        //test custom icon
        $column->icons = [
            'pencil' => Html::tag('span', '', ['class' => ['glyphicon', 'glyphicon-pencil']])
        ];
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $expectedHtml = '<td><a href="http://test.com" title="Update" aria-label="Update" data-pjax="0"><span class="glyphicon glyphicon-pencil"></span></a></td>';
        $this->assertEquals($expectedHtml, $columnContents);

        $column->buttons = [
            'update' => function ($url, $model, $key) {
                return 'update_button';
            },
        ];

        //test default visible button
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertStringContainsString('update_button', $columnContents);

        //test visible button
        $column->visibleButtons = [
            'update' => true,
        ];
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertStringContainsString('update_button', $columnContents);

        //test visible button (condition is callback)
        $column->visibleButtons = [
            'update' => function ($model, $key, $index) {return $model['id'] == 1;},
        ];
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertStringContainsString('update_button', $columnContents);

        //test invisible button
        $column->visibleButtons = [
            'update' => false,
        ];
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertStringNotContainsString('update_button', $columnContents);

        //test invisible button (condition is callback)
        $column->visibleButtons = [
            'update' => function ($model, $key, $index) {return $model['id'] != 1;},
        ];
        $columnContents = $column->renderDataCell(['id' => 1], 1, 0);
        $this->assertStringNotContainsString('update_button', $columnContents);
    }
}
