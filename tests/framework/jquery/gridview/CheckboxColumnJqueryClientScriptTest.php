<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\jquery\gridview;

use Yii;
use yii\data\ArrayDataProvider;
use yii\grid\CheckboxColumn;
use yii\grid\GridView;
use yii\jquery\gridview\CheckboxColumnJqueryClientScript;

/**
 * @group jquery
 */
final class CheckboxColumnJqueryClientScriptTest extends \yiiunit\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['REQUEST_URI'] = 'https://example.com/';

        $this->mockWebApplication();

        Yii::$app->assetManager->hashCallback = static fn ($path): string => '5a1b552';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    public function testRegister(): void
    {
        $config = [
            'id' => 'test-grid',
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'options' => ['id' => 'test-grid'],
            'columns' => [
                [
                    'class' => CheckboxColumn::class,
                    'name' => 'selection',
                    'multiple' => true,
                    'cssClass' => 'checkbox-class',
                ],
            ],
        ];

        $view = Yii::$app->getView();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <title>Test</title>
                </head>
            <body>

            <div id="test-grid">
            <table class="table table-striped table-bordered"><thead>
            <tr><th><input type="checkbox" class="select-on-check-all" name="selection_all" value="1"></th></tr>
            </thead>
            <tbody>
            <tr><td colspan="1"><div class="empty">No results found.</div></td></tr>
            </tbody></table>
            </div>
            <script src="/assets/5a1b552/jquery.js"></script>
            <script src="/assets/5a1b552/yii.js"></script>
            <script src="/assets/5a1b552/yii.gridView.js"></script>
            <script>jQuery(function ($) {
            jQuery('#test-grid').yiiGridView('setSelectionColumn', {"name":"selection[]","class":"checkbox-class","multiple":true,"checkAll":"selection_all"});
            jQuery('#test-grid').yiiGridView({"filterUrl":"\/","filterSelector":"#test-grid-filters input, #test-grid-filters select","filterOnFocusOut":true});
            });</script></body>
            </html>

            HTML,
            $view->render('@yiiunit/data/views/layout.php', ['content' => GridView::widget($config)]),
            'Failed asserting that the generated form matches the expected view.',
        );
    }

    public function testRegisterWithClientScriptOptions(): void
    {
        $gridView = new GridView(
            [
                'dataProvider' => new ArrayDataProvider(['allModels' => []]),
                'options' => ['id' => 'test-grid'],
            ],
        );

        $checkboxColumn = new CheckboxColumn(
            [
                'cssClass' => 'custom-class',
                'grid' => $gridView,
                'multiple' => false,
                'name' => 'customSelection',
            ],
        );

        $view = Yii::$app->getView();

        $checkboxColumn->clientScript->register($checkboxColumn, $view);

        $this->assertInstanceOf(
            CheckboxColumnJqueryClientScript::class,
            $checkboxColumn->clientScript,
            "CheckboxColumn should have 'CheckboxColumnJqueryClientScript' instance.",
        );
        $this->assertEmpty(
            $checkboxColumn->clientScript->getClientOptions($checkboxColumn),
            "'getClientOptions()' method should always return empty array.",
        );
    }

}
