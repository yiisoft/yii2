<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use yii\data\ArrayDataProvider;
use yii\data\DataProviderInterface;
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
        $out = $this->getListView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'emptyText' => 'Nothing at all',
        ])->run();

        $this->assertEqualsWithoutLE('<div id="w0" class="list-view"><div class="empty">Nothing at all</div></div>', $out);
    }

    public function testEmpty()
    {
        $out = $this->getListView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'emptyText' => false,
        ])->run();

        $this->assertEqualsWithoutLE('<div id="w0" class="list-view"></div>', $out);
    }

    public function testEmptyListNotShown()
    {
        $out = $this->getListView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'showOnEmpty' => true,
        ])->run();

        $this->assertEqualsWithoutLE(<<<'HTML'
<div id="w0" class="list-view">

</div>
HTML
        , $out);
    }

    /**
     * @param array $options
     * @return ListView
     */
    private function getListView($options = [])
    {
        return new ListView(array_merge([
            'id' => 'w0',
            'dataProvider' => $this->getDataProvider(),
        ], $options));
    }

    /**
     * @return DataProviderInterface
     */
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
        $out = $this->getListView()->run();

        $this->assertEqualsWithoutLE(<<<'HTML'
<div id="w0" class="list-view"><div class="summary">Showing <b>1-3</b> of <b>3</b> items.</div>
<div data-key="0">0</div>
<div data-key="1">1</div>
<div data-key="2">2</div>
</div>
HTML
        , $out);
    }

    public function testWidgetOptions()
    {
        $out = $this->getListView(['options' => ['class' => 'test-passed'], 'separator' => ''])->run();

        $this->assertEqualsWithoutLE(<<<'HTML'
<div id="w0" class="test-passed"><div class="summary">Showing <b>1-3</b> of <b>3</b> items.</div>
<div data-key="0">0</div><div data-key="1">1</div><div data-key="2">2</div>
</div>
HTML
        , $out);
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
                    return "Item #{$index}: {$model['login']} - Widget: " . get_class($widget);
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
     * @param mixed $itemView
     * @param string $expected
     */
    public function testItemViewOptions($itemView, $expected)
    {
        $out = $this->getListView(['itemView' => $itemView])->run();

        $this->assertEqualsWithoutLE($expected, $out);
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
     * @param mixed $itemOptions
     * @param string $expected
     */
    public function testItemOptions($itemOptions, $expected)
    {
        $out = $this->getListView(['itemOptions' => $itemOptions])->run();

        $this->assertEqualsWithoutLE($expected, $out);
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

        $out = $this->getListView([
            'beforeItem' => $before,
            'afterItem' => $after,
        ])->run();

        $this->assertEqualsWithoutLE(<<<HTML
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
    , $out
);
    }

    /**
     * @see https://github.com/yiisoft/yii2/pull/14596
     */
    public function testShouldTriggerInitEvent()
    {
        $initTriggered = false;
        $this->getListView([
            'on init' => function () use (&$initTriggered) {
                $initTriggered = true;
            },
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
        ]);
        $this->assertTrue($initTriggered);
    }
}
