<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use Yii;
use yii\data\ArrayDataProvider;
use yii\widgets\ListView;
use yiiunit\TestCase;

/**
 * @group widgets
 */
class ListViewTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testEmptyListShown()
    {
        $this->getListView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'emptyText' => 'Nothing at all',
        ])->run();

        $this->expectOutputString('<div id="w0" class="list-view"><div class="empty">Nothing at all</div></div>');
    }

    public function testEmpty()
    {
        $this->getListView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'emptyText' => false,
        ])->run();

        $this->expectOutputString('<div id="w0" class="list-view"></div>');
    }

    public function testEmptyListNotShown()
    {
        $this->getListView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'showOnEmpty' => true,
        ])->run();

        $this->expectOutputString(<<<'HTML'
<div id="w0" class="list-view">

</div>
HTML
        );
    }

    private function getListView($options = [])
    {
        return new ListView(array_merge([
            'id' => 'w0',
            'dataProvider' => $this->getDataProvider(),
        ], $options));
    }

    private function getDataProvider()
    {
        return new ArrayDataProvider([
            'allModels' => [
                ['id' => 1, 'login' => 'silverfire'],
                ['id' => 2, 'login' => 'samdark'],
                ['id' => 3, 'login' => 'cebe'],
            ],
        ]);
    }

    public function testSimplyListView()
    {
        $this->getListView()->run();

        $this->expectOutputString(<<<'HTML'
<div id="w0" class="list-view"><div class="summary">Showing <b>1-3</b> of <b>3</b> items.</div>
<div data-key="0">0</div>
<div data-key="1">1</div>
<div data-key="2">2</div>
</div>
HTML
        );
    }

    public function testWidgetOptions()
    {
        $this->getListView(['options' => ['class' => 'test-passed'], 'separator' => ''])->run();

        $this->expectOutputString(<<<'HTML'
<div id="w0" class="test-passed"><div class="summary">Showing <b>1-3</b> of <b>3</b> items.</div>
<div data-key="0">0</div><div data-key="1">1</div><div data-key="2">2</div>
</div>
HTML
        );
    }

    public function itemViewOptions()
    {
        return [
            [
                null,
                '<div id="w0" class="list-view"><div class="summary">Showing <b>1-3</b> of <b>3</b> items.</div>
<div data-key="0">0</div>
<div data-key="1">1</div>
<div data-key="2">2</div>
</div>',
            ],
            [
                function ($model, $key, $index, $widget) {
                    return "Item #{$index}: {$model['login']} - Widget: " . $widget->className();
                },
                '<div id="w0" class="list-view"><div class="summary">Showing <b>1-3</b> of <b>3</b> items.</div>
<div data-key="0">Item #0: silverfire - Widget: yii\widgets\ListView</div>
<div data-key="1">Item #1: samdark - Widget: yii\widgets\ListView</div>
<div data-key="2">Item #2: cebe - Widget: yii\widgets\ListView</div>
</div>',
            ],
            [
                '@yiiunit/data/views/widgets/ListView/item',
                '<div id="w0" class="list-view"><div class="summary">Showing <b>1-3</b> of <b>3</b> items.</div>
<div data-key="0">Item #0: silverfire - Widget: yii\widgets\ListView</div>
<div data-key="1">Item #1: samdark - Widget: yii\widgets\ListView</div>
<div data-key="2">Item #2: cebe - Widget: yii\widgets\ListView</div>
</div>',
            ],
        ];
    }

    /**
     * @dataProvider itemViewOptions
     */
    public function testItemViewOptions($itemView, $expected)
    {
        $this->getListView(['itemView' => $itemView])->run();
        $this->expectOutputString($expected);
    }

    public function itemOptions()
    {
        return [
            [
                null,
                '<div id="w0" class="list-view"><div class="summary">Showing <b>1-3</b> of <b>3</b> items.</div>
<div data-key="0">0</div>
<div data-key="1">1</div>
<div data-key="2">2</div>
</div>',
            ],
            [
                function ($model, $key, $index, $widget) {
                    return [
                        'tag' => 'span',
                        'data' => [
                            'test' => 'passed',
                            'key' => $key,
                            'index' => $index,
                            'id' => $model['id'],
                        ],
                    ];
                },
                '<div id="w0" class="list-view"><div class="summary">Showing <b>1-3</b> of <b>3</b> items.</div>
<span data-test="passed" data-key="0" data-index="0" data-id="1" data-key="0">0</span>
<span data-test="passed" data-key="1" data-index="1" data-id="2" data-key="1">1</span>
<span data-test="passed" data-key="2" data-index="2" data-id="3" data-key="2">2</span>
</div>',
            ],
        ];
    }

    /**
     * @dataProvider itemOptions
     */
    public function testItemOptions($itemOptions, $expected)
    {
        $this->getListView(['itemOptions' => $itemOptions])->run();
        $this->expectOutputString($expected);
    }

    public function testBeforeAndAfterItem()
    {
        $before = function ($model, $key, $index, $widget) {
            $widget = get_class($widget);
            return "<!-- before: {$model['id']}, key: $key, index: $index, widget: $widget -->";
        };
        $after = function ($model, $key, $index, $widget) {
            if ($model['id'] === 1) {
                return null;
            }
            $widget = get_class($widget);
            return "<!-- after: {$model['id']}, key: $key, index: $index, widget: $widget -->";
        };
        $this->getListView([
            'beforeItem' => $before,
            'afterItem' => $after,
        ])->run();

        $this->expectOutputString(<<<HTML
<div id="w0" class="list-view"><div class="summary">Showing <b>1-3</b> of <b>3</b> items.</div>
<!-- before: 1, key: 0, index: 0, widget: yii\widgets\ListView -->
<div data-key="0">0</div>
<!-- before: 2, key: 1, index: 1, widget: yii\widgets\ListView -->
<div data-key="1">1</div>
<!-- after: 2, key: 1, index: 1, widget: yii\widgets\ListView -->
<!-- before: 3, key: 2, index: 2, widget: yii\widgets\ListView -->
<div data-key="2">2</div>
<!-- after: 3, key: 2, index: 2, widget: yii\widgets\ListView -->
</div>
HTML
);
    }
}
