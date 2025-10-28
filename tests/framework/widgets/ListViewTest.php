<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use Yii;
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

    public function testEmptyListShown(): void
    {
        ob_start();
        $this->getListView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'emptyText' => 'Nothing at all',
        ])->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE('<div id="w0" class="list-view"><div class="empty">Nothing at all</div></div>', $out);
    }

    public function testEmpty(): void
    {
        ob_start();
        $this->getListView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'emptyText' => false,
        ])->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE('<div id="w0" class="list-view"></div>', $out);
    }

    public function testEmptyListNotShown(): void
    {
        ob_start();
        $this->getListView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'showOnEmpty' => true,
        ])->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE(
            <<<'HTML'
<div id="w0" class="list-view">

</div>
HTML,
            $out
        );
    }

    /**
     * @return ListView
     */
    private function getListView(array $options = [])
    {
        return new ListView(array_merge([
            'id' => 'w0',
            'dataProvider' => $this->getDataProvider(),
        ], $options));
    }

    /**
     * @return DataProviderInterface
     */
    private static function getDataProvider(array $additionalConfig = [])
    {
        return new ArrayDataProvider(array_merge([
            'allModels' => [
                ['id' => 1, 'login' => 'silverfire'],
                ['id' => 2, 'login' => 'samdark'],
                ['id' => 3, 'login' => 'cebe'],
            ],
        ], $additionalConfig));
    }

    public function testSimplyListView(): void
    {
        ob_start();
        $this->getListView()->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE(
            <<<'HTML'
<div id="w0" class="list-view"><div class="summary">Showing <b>1-3</b> of <b>3</b> items.</div>
<div data-key="0">0</div>
<div data-key="1">1</div>
<div data-key="2">2</div>
</div>
HTML,
            $out
        );
    }

    public function testWidgetOptions(): void
    {
        ob_start();
        $this->getListView(['options' => ['class' => 'test-passed'], 'separator' => ''])->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE(
            <<<'HTML'
<div id="w0" class="test-passed"><div class="summary">Showing <b>1-3</b> of <b>3</b> items.</div>
<div data-key="0">0</div><div data-key="1">1</div><div data-key="2">2</div>
</div>
HTML,
            $out
        );
    }

    public static function itemViewOptions(): array
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
     *
     * @param mixed $itemView The item view to be used.
     * @param string $expected The expected result.
     */
    public function testItemViewOptions(mixed $itemView, string $expected): void
    {
        ob_start();
        $this->getListView(['itemView' => $itemView])->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE($expected, $out);
    }

    public static function itemOptions(): array
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
                fn($model, $key, $index, $widget) => [
                    'tag' => 'span',
                    'data' => [
                        'test' => 'passed',
                        'key' => $key,
                        'index' => $index,
                        'id' => $model['id'],
                    ],
                ],
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
     *
     * @param mixed $itemOptions The item options.
     * @param string $expected The expected result.
     */
    public function testItemOptions(mixed $itemOptions, string $expected): void
    {
        ob_start();
        $this->getListView(['itemOptions' => $itemOptions])->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE($expected, $out);
    }

    public function testBeforeAndAfterItem(): void
    {
        $before = function ($model, $key, $index, $widget) {
            $widget = $widget::class;
            return "<!-- before: {$model['id']}, key: $key, index: $index, widget: $widget -->";
        };
        $after = function ($model, $key, $index, $widget) {
            if ($model['id'] === 1) {
                return null;
            }
            $widget = $widget::class;
            return "<!-- after: {$model['id']}, key: $key, index: $index, widget: $widget -->";
        };

        ob_start();
        $this->getListView([
            'beforeItem' => $before,
            'afterItem' => $after,
        ])->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE(
            <<<HTML
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
HTML,
            $out
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/pull/14596
     */
    public function testShouldTriggerInitEvent(): void
    {
        $initTriggered = false;
        $this->getListView([
            'on init' => function () use (&$initTriggered): void {
                $initTriggered = true;
            },
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
        ]);
        $this->assertTrue($initTriggered);
    }

    public function testNoDataProvider(): void
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('The "dataProvider" property must be set.');
        (new ListView())->run();
    }

    public static function providerForNoSorter(): array
    {
        return [
            'no sort attributes' => [[]],
            'sorter false' => [['dataProvider' => self::getDataProvider(['sort' => false])]],
        ];
    }

    /**
     * @dataProvider providerForNoSorter
     *
     * @param array $additionalConfig Additional configuration for the list view.
     */
    public function testRenderNoSorter(array $additionalConfig): void
    {
        $config = array_merge(['layout' => '{sorter}'], $additionalConfig);

        ob_start();
        $this->getListView($config)->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE('<div id="w0" class="list-view"></div>', $out);
    }

    public function testRenderSorterOnlyWithNoItems(): void
    {
        // by default sorter is skipped when there are no items during run()
        $out = (new ListView([
            'id' => 'w0',
            'dataProvider' => $this->getDataProvider(['allModels' => [], 'sort' => ['attributes' => ['id']]]),
        ]))->renderSorter();

        $this->assertEquals('', $out);
    }

    public function testRenderSorter(): void
    {
        Yii::$app->set('request', new Request(['scriptUrl' => '/']));

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

    public function testRenderSummaryWhenPaginationIsFalseAndSummaryIsNull(): void
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

    public static function providerForSummary(): array
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
     *
     * @param string $summary Summary template.
     * @param string $result Expected result.
     */
    public function testRenderSummaryWhenSummaryIsCustom(string $summary, string $result): void
    {
        ob_start();
        $this->getListView(['summary' => $summary])->run();
        $out = ob_get_clean();

        $this->assertEqualsWithoutLE($result, $out);
    }
}
