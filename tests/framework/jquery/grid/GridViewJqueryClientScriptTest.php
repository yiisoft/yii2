<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\jquery\grid;

use Yii;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;

/**
 * @group jquery
 */
final class GridViewJqueryClientScriptTest extends \yiiunit\TestCase
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
            'filterUrl' => '/test/filter',
            'filterSelector' => '#custom-filter input',
            'options' => ['id' => 'test-grid'],
            'filterRowOptions' => ['id' => 'test-grid-filters'],
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
            <tr></tr>
            </thead>
            <tbody>
            <tr><td colspan="0"><div class="empty">No results found.</div></td></tr>
            </tbody></table>
            </div>
            <script src="/assets/5a1b552/jquery.js"></script>
            <script src="/assets/5a1b552/yii.js"></script>
            <script src="/assets/5a1b552/yii.gridView.js"></script>
            <script>jQuery(function ($) {
            jQuery('#test-grid').yiiGridView({"filterUrl":"\/test\/filter","filterSelector":"#test-grid-filters input, #test-grid-filters select, #custom-filter input","filterOnFocusOut":true});
            });</script></body>
            </html>

            HTML,
            $view->render('@yiiunit/data/views/layout.php', ['content' => GridView::widget($config)]),
            'Rendered HTML does not match expected output',
        );

        $gridView = new GridView($config);

        $this->assertSame(
            [
                'filterUrl' => '/test/filter',
                'filterSelector' => '#test-grid-filters input, #test-grid-filters select, #custom-filter input',
            ],
            $gridView->clientScript->getClientOptions($gridView),
            "'getClientOptions()' method should return correct options array.",
        );
        $this->assertSame(
            [
                'filterUrl' => '/test/filter',
                'filterSelector' => '#test-grid-filters input, #test-grid-filters select, #custom-filter input',
            ],
            $this->invokeMethod($gridView, 'getClientOptions'),
            "'getClientOptions()' method should return correct options array.",
        );
    }

    public function testRegisterWithComplexFilterUrl(): void
    {
        $config = [
            'id' => 'test-grid',
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'filterUrl' => '/test/filter?param=value&other=123',
            'options' => ['id' => 'test-grid'],
            'filterRowOptions' => ['id' => 'test-grid-filters'],
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
            <tr></tr>
            </thead>
            <tbody>
            <tr><td colspan="0"><div class="empty">No results found.</div></td></tr>
            </tbody></table>
            </div>
            <script src="/assets/5a1b552/jquery.js"></script>
            <script src="/assets/5a1b552/yii.js"></script>
            <script src="/assets/5a1b552/yii.gridView.js"></script>
            <script>jQuery(function ($) {
            jQuery('#test-grid').yiiGridView({"filterUrl":"\/test\/filter?param=value\u0026other=123","filterSelector":"#test-grid-filters input, #test-grid-filters select","filterOnFocusOut":true});
            });</script></body>
            </html>

            HTML,
            $view->render('@yiiunit/data/views/layout.php', ['content' => GridView::widget($config)]),
            'Rendered HTML does not match expected output',
        );

        $gridView = new GridView($config);

        $this->assertSame(
            [
                'filterUrl' => '/test/filter?param=value&other=123',
                'filterSelector' => '#test-grid-filters input, #test-grid-filters select',
            ],
            $gridView->clientScript->getClientOptions($gridView),
            "'getClientOptions()' method should return correct options array.",
        );
    }

    public function testRegisterWithCustomFilterSelector(): void
    {
        $config = [
            'id' => 'test-grid',
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'filterUrl' => '/test/filter',
            'options' => ['id' => 'test-grid'],
            'filterRowOptions' => ['id' => 'test-grid-filters'],
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
            <tr></tr>
            </thead>
            <tbody>
            <tr><td colspan="0"><div class="empty">No results found.</div></td></tr>
            </tbody></table>
            </div>
            <script src="/assets/5a1b552/jquery.js"></script>
            <script src="/assets/5a1b552/yii.js"></script>
            <script src="/assets/5a1b552/yii.gridView.js"></script>
            <script>jQuery(function ($) {
            jQuery('#test-grid').yiiGridView({"filterUrl":"\/test\/filter","filterSelector":"#test-grid-filters input, #test-grid-filters select","filterOnFocusOut":true});
            });</script></body>
            </html>

            HTML,
            $view->render('@yiiunit/data/views/layout.php', ['content' => GridView::widget($config)]),
            'Rendered HTML does not match expected output',
        );

        $gridView = new GridView($config);

        $this->assertSame(
            [
                'filterUrl' => '/test/filter',
                'filterSelector' => '#test-grid-filters input, #test-grid-filters select',
            ],
            $gridView->clientScript->getClientOptions($gridView),
            "'getClientOptions()' method should return correct options array.",
        );
    }

    public function testRegisterWithDefaultFilterUrl(): void
    {
        Yii::$app->request->setUrl('/default/url');

        $config = [
            'id' => 'test-grid',
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'options' => ['id' => 'test-grid'],
            'filterRowOptions' => ['id' => 'test-grid-filters'],
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
            <tr></tr>
            </thead>
            <tbody>
            <tr><td colspan="0"><div class="empty">No results found.</div></td></tr>
            </tbody></table>
            </div>
            <script src="/assets/5a1b552/jquery.js"></script>
            <script src="/assets/5a1b552/yii.js"></script>
            <script src="/assets/5a1b552/yii.gridView.js"></script>
            <script>jQuery(function ($) {
            jQuery('#test-grid').yiiGridView({"filterUrl":"\/default\/url","filterSelector":"#test-grid-filters input, #test-grid-filters select","filterOnFocusOut":true});
            });</script></body>
            </html>

            HTML,
            $view->render('@yiiunit/data/views/layout.php', ['content' => GridView::widget($config)]),
            'Rendered HTML does not match expected output',
        );

        $gridView = new GridView($config);

        $this->assertSame(
            [
                'filterUrl' => '/default/url',
                'filterSelector' => '#test-grid-filters input, #test-grid-filters select',
            ],
            $gridView->clientScript->getClientOptions($gridView),
            "'getClientOptions()' method should return correct options array.",
        );
    }

    public function testRegisterWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $config = [
            'id' => 'test-grid',
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'filterUrl' => '/test/filter',
            'filterOnFocusOut' => true,
            'options' => ['id' => 'test-grid'],
            'filterRowOptions' => ['id' => 'test-grid-filters'],
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
            <tr></tr>
            </thead>
            <tbody>
            <tr><td colspan="0"><div class="empty">No results found.</div></td></tr>
            </tbody></table>
            </div>
            </body>
            </html>

            HTML,
            $view->render('@yiiunit/data/views/layout.php', ['content' => GridView::widget($config)]),
            'Rendered HTML does not match expected output',
        );

        $gridView = new GridView($config);

        $this->assertNull(
            $gridView->clientScript,
            "'ClientScript' property should be 'null' when 'useJquery' is 'false'.",
        );
        $this->assertSame(
            [],
            $this->invokeMethod($gridView, 'getClientOptions'),
            "'getClientOptions()' method should return an empty array.",
        );
    }
}
