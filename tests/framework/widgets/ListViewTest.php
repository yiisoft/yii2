<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use yii\web\Request;
use yii\data\ArrayDataProvider;
use yii\data\DataProviderInterface;
use yii\widgets\ListView;
use yiiunit\TestCase;

/**
 * @group widgets
 */
class ListViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testEmptyListShown()
    {
        ob_start();
        $this->getListView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'emptyText' => 'Nothing at all',
        ])->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE('<div id="w0" class="list-view"><div class="empty">Nothing at all</div></div>', $out);
    }

    public function testEmpty()
    {
        ob_start();
        $this->getListView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'emptyText' => false,
        ])->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE('<div id="w0" class="list-view"></div>', $out);
    }

    public function testEmptyListNotShown()
    {
        ob_start();
        $this->getListView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'showOnEmpty' => true,
        ])->run();
        $out = ob_get_clean();

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
    private function getDataProvider($additionalConfig = [])
    {
        return new ArrayDataProvider(array_merge([
            'allModels' => [
                ['id' => 1, 'login' => 'silverfire'],
                ['id' => 2, 'login' => 'samdark'],
                ['id' => 3, 'login' => 'cebe'],
            ],
        ], $additionalConfig));
    }

    public function testSimplyListView()
    {
        ob_start();
        $this->getListView()->run();
        $out = ob_get_clean();

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
        ob_start();
        $this->getListView(['options' => ['class' => 'test-passed'], 'separator' => ''])->run();
        $out = ob_get_clean();

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
     * @param mixed $itemView
     * @param string $expected
     */
    public function testItemViewOptions($itemView, $expected)
    {
        ob_start();
        $this->getListView(['itemView' => $itemView])->run();
        $out = ob_get_clean();

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
        ob_start();
        $this->getListView(['itemOptions' => $itemOptions])->run();
        $out = ob_get_clean();

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

        ob_start();
        $this->getListView([
            'beforeItem' => $before,
            'afterItem' => $after,
        ])->run();
        $out = ob_get_clean();

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

    public function testNoDataProvider()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('The "dataProvider" property must be set.');
        (new ListView())->run();
    }

    public function providerForNoSorter()
    {
        return [
            'no sort attributes' => [[]],
            'sorter false' => [['dataProvider' => $this->getDataProvider(['sort' => false])]],
        ];
    }

    /**
     * @dataProvider providerForNoSorter
     */
    public function testRenderNoSorter($additionalConfig)
    {
        $config = array_merge(['layout' => '{sorter}'], $additionalConfig);

        ob_start();
        $this->getListView($config)->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE('<div id="w0" class="list-view"></div>', $out);
    }

    public function testRenderSorterOnlyWithNoItems()
    {
        // by default sorter is skipped when there are no items during run()
        $out = (new ListView([
            'id' => 'w0',
            'dataProvider' => $this->getDataProvider(['allModels' => [], 'sort' => ['attributes' => ['id']]]),
        ]))->renderSorter();

        $this->assertEquals('', $out);
    }

    public function testRenderSorter()
    {
        \Yii::$app->set('request', new Request(['scriptUrl' => '/']));

        ob_start();
        $this->getListView([
            'layout' => '{sorter}',
            'dataProvider' => $this->getDataProvider([
                'sort' => [
                    'attributes' => ['id'],
                    'route' => 'list/view',
                ]
            ])
        ])->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE('<div id="w0" class="list-view"><ul class="sorter">
<li><a href="/?r=list%2Fview&amp;sort=id" data-sort="id">Id</a></li>
</ul></div>', $out);
    }

    public function testRenderSummaryWhenPaginationIsFalseAndSummaryIsNull()
    {
        ob_start();
        $this->getListView(['dataProvider' => $this->getDataProvider(['pagination' => false])])->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE('<div id="w0" class="list-view"><div class="summary">Total <b>3</b> items.</div>
<div data-key="0">0</div>
<div data-key="1">1</div>
<div data-key="2">2</div>
</div>', $out);
    }

    public function providerForSummary()
    {
        return [
            'empty' => ['', '<div id="w0" class="list-view">
<div data-key="0">0</div>
<div data-key="1">1</div>
<div data-key="2">2</div>
</div>'],
            'all tokens' => ['{begin}-{end}-{count}-{totalCount}-{page}-{pageCount}', '<div id="w0" class="list-view"><div class="summary">1-3-3-3-1-1</div>
<div data-key="0">0</div>
<div data-key="1">1</div>
<div data-key="2">2</div>
</div>'],
        ];
    }

    /**
     * @dataProvider providerForSummary
     */
    public function testRenderSummaryWhenSummaryIsCustom($summary, $result)
    {
        ob_start();
        $this->getListView(['summary' => $summary])->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE($result, $out);
    }
}
