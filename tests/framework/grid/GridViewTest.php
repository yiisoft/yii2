<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\grid;

use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Connection;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\i18n\Formatter;
use yii\helpers\FileHelper;
use yii\web\Request;
use yii\web\UrlManager;
use yii\web\View;
use yiiunit\data\base\RulesModel;
use yiiunit\data\ar\NoAutoLabels;
use yiiunit\TestCase;

/**
 * @author Evgeniy Tkachenko <et.coder@gmail.com>
 * @group grid
 */
class GridViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication([
            'components' => [
                'assetManager' => [
                    'bundles' => [
                        'yii\grid\GridViewAsset' => false,
                        'yii\web\JqueryAsset' => false,
                    ],
                ],
            ],
        ]);
        Yii::setAlias('@webroot', '@yiiunit/runtime');
        Yii::setAlias('@web', 'http://localhost/');
        FileHelper::createDirectory(Yii::getAlias('@webroot/assets'));
    }

    /**
     * @return array
     */
    public static function emptyDataProvider(): array
    {
        return [
            [null, 'No results found.'],
            ['Empty', 'Empty'],
            // https://github.com/yiisoft/yii2/issues/13352
            [false, ''],
        ];
    }

    /**
     * @dataProvider emptyDataProvider
     * @param mixed $emptyText
     * @param string $expectedText
     * @throws Exception
     */
    public function testEmpty($emptyText, $expectedText): void
    {
        $html = GridView::widget([
            'id' => 'grid',
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'showHeader' => false,
            'emptyText' => $emptyText,
            'options' => [],
            'tableOptions' => [],
            'view' => new View(),
            'filterUrl' => '/',
        ]);
        $html = preg_replace("/\r|\n/", '', $html);

        if ($expectedText) {
            $emptyRowHtml = "<tr><td colspan=\"0\"><div class=\"empty\">{$expectedText}</div></td></tr>";
        } else {
            $emptyRowHtml = '';
        }
        $expectedHtml = "<div id=\"grid\"><table><tbody>{$emptyRowHtml}</tbody></table></div>";

        $this->assertEquals($expectedHtml, $html);
    }

    public function testGuessColumns(): void
    {
        $row = ['id' => 1, 'name' => 'Name1', 'value' => 'Value1', 'description' => 'Description1'];

        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(
                [
                    'allModels' => [
                        $row,
                    ],
                ]
            ),
        ]);

        $columns = $grid->columns;
        $this->assertCount(count($row), $columns);

        foreach ($columns as $index => $column) {
            $this->assertInstanceOf(DataColumn::class, $column);
            $this->assertArrayHasKey($column->attribute, $row);
        }

        $row = array_merge($row, ['relation' => ['id' => 1, 'name' => 'RelationName']]);
        $row = array_merge($row, ['otherRelation' => (object) $row['relation']]);

        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(
                [
                    'allModels' => [
                        $row,
                    ],
                ]
            ),
        ]);

        $columns = $grid->columns;
        $this->assertCount(count($row) - 2, $columns);

        foreach ($columns as $index => $column) {
            $this->assertInstanceOf(DataColumn::class, $column);
            $this->assertArrayHasKey($column->attribute, $row);
            $this->assertNotEquals('relation', $column->attribute);
            $this->assertNotEquals('otherRelation', $column->attribute);
        }
    }

    /**
     * @throws Exception
     */
    public function testFooter(): void
    {
        $config = [
            'id'           => 'grid',
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'showHeader'   => false,
            'showFooter'   => true,
            'options'      => [],
            'tableOptions' => [],
            'view'         => new View(),
            'filterUrl'    => '/',
        ];

        $html = GridView::widget($config);
        $html = preg_replace("/\r|\n/", '', $html);

        $this->assertTrue(preg_match('/<\/tfoot><tbody>/', $html) === 1);

        // Place footer after body
        $config['placeFooterAfterBody'] = true;

        $html = GridView::widget($config);
        $html = preg_replace("/\r|\n/", '', $html);

        $this->assertTrue(preg_match('/<\/tbody><tfoot>/', $html) === 1);
    }

    public function testHeaderLabels(): void
    {
        // Ensure GridView does not call Model::generateAttributeLabel() to generate labels unless the labels are explicitly used.
        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => Connection::class,
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);

        NoAutoLabels::$db = Yii::$app->getDb();
        Yii::$app->getDb()->createCommand()->createTable(NoAutoLabels::tableName(), ['attr1' => 'int', 'attr2' => 'int'])->execute();

        $urlManager = new UrlManager([
            'baseUrl' => '/',
            'scriptUrl' => '/index.php',
        ]);

        $grid = new GridView([
            'dataProvider' => new ActiveDataProvider([
                'query' => NoAutoLabels::find(),
            ]),
            'columns' => [
                'attr1',
                'attr2:text:Label for attr2',
            ],
        ]);

        $gridDataProvider = $grid->dataProvider;
        $this->assertInstanceOf(ActiveDataProvider::class, $gridDataProvider);

        // NoAutoLabels::generateAttributeLabel() should not be called.
        $gridDataProvider->setSort([
            'route' => '/',
            'urlManager' => $urlManager,
        ]);
        $grid->renderTableHeader();

        // NoAutoLabels::generateAttributeLabel() should not be called.
        $gridDataProvider->setSort([
            'route' => '/',
            'urlManager' => $urlManager,
            'attributes' => ['attr1', 'attr2'],
        ]);
        $grid->renderTableHeader();
        // If NoAutoLabels::generateAttributeLabel() has not been called no exception will be thrown meaning this test passed successfully.

        $this->assertTrue(true);
    }

    public function testFormatterAsArray(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => 'N/A'],
        ]);

        $this->assertInstanceOf(Formatter::class, $grid->formatter);
        $this->assertSame('N/A', $grid->formatter->nullDisplay);
    }

    public function testInvalidFormatterThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);

        new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'formatter' => 'invalid',
        ]);
    }

    public function testRenderErrorsWithErrors(): void
    {
        $model = new RulesModel(['rules' => [['name', 'required']]]);
        $model->validate();

        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'filterModel' => $model,
        ]);

        $result = $grid->renderErrors();
        $this->assertStringContainsString('error-summary', $result);
    }

    public function testRenderErrorsWithoutErrors(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'filterModel' => new RulesModel(),
        ]);

        $this->assertSame('', $grid->renderErrors());
    }

    public function testRenderErrorsWithoutFilterModel(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
        ]);

        $this->assertSame('', $grid->renderErrors());
    }

    public function testRenderSectionErrors(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
        ]);

        $this->assertSame('', $grid->renderSection('{errors}'));
    }

    public function testRenderCaption(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
            'caption' => 'Test Caption',
            'captionOptions' => ['class' => 'caption'],
        ]);

        $this->assertSame('<caption class="caption">Test Caption</caption>', $grid->renderCaption());
    }

    public function testRenderCaptionEmpty(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => []]),
        ]);

        $this->assertFalse($grid->renderCaption());
    }

    public function testRenderColumnGroup(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['id' => 1]]]),
            'columns' => [
                ['attribute' => 'id', 'options' => ['style' => 'width:100px']],
            ],
        ]);

        $result = $grid->renderColumnGroup();
        $this->assertStringContainsString('<colgroup>', $result);
        $this->assertStringContainsString('width:100px', $result);
    }

    public function testRenderColumnGroupReturnsFalse(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['id' => 1]]]),
            'columns' => ['id'],
        ]);

        $this->assertFalse($grid->renderColumnGroup());
    }

    public function testRunRegistersClientScript(): void
    {
        $this->destroyApplication();
        $this->mockApplication([
            'components' => [
                'assetManager' => [
                    'bundles' => [
                        'yii\web\YiiAsset' => false,
                        'yii\web\JqueryAsset' => false,
                    ],
                ],
            ],
        ]);
        Yii::setAlias('@webroot', '@yiiunit/runtime');
        Yii::setAlias('@web', 'http://localhost/');
        FileHelper::createDirectory(Yii::getAlias('@webroot/assets'));
        Yii::$app->set('request', new Request(['url' => '/abc']));

        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['id' => 1]]]),
            'id' => 'grid-run',
            'columns' => ['id'],
            'filterOnFocusOut' => false,
        ]);

        ob_start();
        $grid->run();
        ob_end_clean();

        $js = implode("\n", Yii::$app->getView()->js[View::POS_READY] ?? []);
        $this->assertArrayHasKey('yii\grid\GridViewAsset', Yii::$app->getView()->assetBundles);
        $this->assertStringContainsString(
            'jQuery(\'#grid-run\').yiiGridView({"filterUrl":"\/abc","filterSelector":"#grid-run-filters input, #grid-run-filters select","filterOnFocusOut":false});',
            $js
        );
    }

    public function testRenderTableHeaderWithFilterPosHeader(): void
    {
        $model = new RulesModel(['rules' => [['name', 'safe']]]);
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['name' => 'test']]]),
            'columns' => ['name'],
            'filterModel' => $model,
            'filterPosition' => GridView::FILTER_POS_HEADER,
            'filterRowOptions' => ['id' => 'test-filters', 'class' => 'filters'],
        ]);

        $result = $grid->renderTableHeader();
        $this->assertSame(<<<'HTML'
<thead>
<tr id="test-filters" class="filters"><td><input type="text" class="form-control" name="RulesModel[name]"></td></tr><tr><th>Name</th></tr>
</thead>
HTML, $result);
    }

    public function testRenderTableFooterWithFilterPosFooter(): void
    {
        $model = new RulesModel(['rules' => [['name', 'safe']]]);
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['name' => 'test']]]),
            'columns' => ['name'],
            'filterModel' => $model,
            'showFooter' => true,
            'filterPosition' => GridView::FILTER_POS_FOOTER,
            'filterRowOptions' => ['id' => 'test-filters', 'class' => 'filters'],
        ]);

        $result = $grid->renderTableFooter();
        $this->assertSame(<<<'HTML'
<tfoot>
<tr><td>&nbsp;</td></tr><tr id="test-filters" class="filters"><td><input type="text" class="form-control" name="RulesModel[name]"></td></tr>
</tfoot>
HTML, $result);
    }

    public function testRenderFilters(): void
    {
        $model = new RulesModel(['rules' => [['name', 'safe']]]);
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['name' => 'test']]]),
            'columns' => ['name'],
            'filterModel' => $model,
            'filterRowOptions' => ['id' => 'test-filters', 'class' => 'filters'],
        ]);

        $result = $grid->renderFilters();
        $this->assertSame(
            '<tr id="test-filters" class="filters"><td><input type="text" class="form-control" name="RulesModel[name]"></td></tr>',
            $result
        );
    }

    public function testRenderFiltersWithoutFilterModel(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['name' => 'test']]]),
            'columns' => ['name'],
        ]);

        $this->assertSame('', $grid->renderFilters());
    }

    public function testBeforeRowAndAfterRow(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [['id' => 1], ['id' => 2]],
            ]),
            'columns' => ['id'],
            'beforeRow' => function ($model, $key, $index, $grid) {
                return '<tr class="before"><td>Before ' . $model['id'] . '</td></tr>';
            },
            'afterRow' => function ($model, $key, $index, $grid) {
                return '<tr class="after"><td>After ' . $model['id'] . '</td></tr>';
            },
        ]);

        $result = $grid->renderTableBody();
        $this->assertStringContainsString('Before 1', $result);
        $this->assertStringContainsString('After 1', $result);
        $this->assertStringContainsString('Before 2', $result);
        $this->assertStringContainsString('After 2', $result);
    }

    public function testBeforeRowReturnsEmpty(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [['id' => 1]],
            ]),
            'columns' => ['id'],
            'beforeRow' => function ($model, $key, $index, $grid) {
                return '';
            },
            'afterRow' => function ($model, $key, $index, $grid) {
                return '';
            },
        ]);

        $result = $grid->renderTableBody();
        $this->assertStringNotContainsString('before', $result);
    }

    public function testClosureRowOptions(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [['id' => 1]],
            ]),
            'columns' => ['id'],
            'rowOptions' => function ($model, $key, $index, $grid) {
                return ['class' => 'row-' . $model['id']];
            },
        ]);

        $result = $grid->renderTableRow(['id' => 1], 'k1', 0);
        $this->assertSame('<tr class="row-1" data-key="k1"><td>1</td></tr>', $result);
    }

    public function testInitColumnsWithArrayConfig(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['id' => 1]]]),
            'columns' => [
                ['class' => DataColumn::class, 'attribute' => 'id'],
            ],
        ]);

        $this->assertCount(1, $grid->columns);
        $this->assertInstanceOf(DataColumn::class, $grid->columns[0]);
    }

    public function testInitColumnsRemovesInvisible(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['id' => 1, 'name' => 'test', 'age' => 42]]]),
            'columns' => [
                'id',
                ['attribute' => 'name', 'visible' => false],
                'age',
            ],
        ]);

        $this->assertCount(2, $grid->columns);
        $attributes = array_values(array_map(static fn($column) => $column->attribute, $grid->columns));
        $this->assertSame(['id', 'age'], $attributes);
    }

    public function testCreateDataColumnInvalidFormat(): void
    {
        $this->expectException(InvalidConfigException::class);

        new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['id' => 1]]]),
            'columns' => [''],
        ]);
    }

    public function testRenderTableHeaderWithFilterPosBody(): void
    {
        $model = new RulesModel(['rules' => [['name', 'safe']]]);
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['name' => 'test']]]),
            'columns' => ['name'],
            'filterModel' => $model,
            'filterPosition' => GridView::FILTER_POS_BODY,
            'filterRowOptions' => ['id' => 'test-filters', 'class' => 'filters'],
        ]);

        $result = $grid->renderTableHeader();
        $this->assertSame(<<<'HTML'
<thead>
<tr><th>Name</th></tr><tr id="test-filters" class="filters"><td><input type="text" class="form-control" name="RulesModel[name]"></td></tr>
</thead>
HTML, $result);
    }

    public function testCreateDataColumnParsing(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['name' => 'test']]]),
            'columns' => ['name:html:My Label'],
        ]);

        $column = $grid->columns[0];
        $this->assertInstanceOf(DataColumn::class, $column);
        $this->assertSame('name', $column->attribute);
        $this->assertSame('html', $column->format);
        $this->assertSame('My Label', $column->label);
    }

    public function testCreateDataColumnWithFormatOnly(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['name' => 'test']]]),
            'columns' => ['name:html'],
        ]);

        $column = $grid->columns[0];
        $this->assertSame('name', $column->attribute);
        $this->assertSame('html', $column->format);
        $this->assertNull($column->label);
    }

    public function testCreateDataColumnDefaultFormat(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['name' => 'test']]]),
            'columns' => ['name'],
        ]);

        $column = $grid->columns[0];
        $this->assertSame('name', $column->attribute);
        $this->assertSame('text', $column->format);
        $this->assertNull($column->label);
    }

    public function testRenderItemsWithCaption(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [['id' => 1]], 'pagination' => false]),
            'columns' => ['id'],
            'caption' => 'My Caption',
            'showHeader' => false,
            'showFooter' => false,
            'tableOptions' => ['class' => 'grid-table'],
        ]);

        $result = $grid->renderItems();
        $this->assertSame(<<<'HTML'
<table class="grid-table"><caption>My Caption</caption>
<tbody>
<tr data-key="0"><td>1</td></tr>
</tbody></table>
HTML, $result);
    }

    public function testGuessColumnsWithStringableObject(): void
    {
        $row = [
            123 => 'value',
            'obj' => new class {
                public function __toString()
                {
                    return 'str';
                }
            },
        ];
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [$row]]),
        ]);

        $this->assertCount(2, $grid->columns);
        $this->assertSame('123', $grid->columns[0]->attribute);
        $this->assertSame('obj', $grid->columns[1]->attribute);
    }

    public function testRenderTableBodyWithNonSequentialModelKeys(): void
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [
                    2 => ['id' => 1],
                    5 => ['id' => 2],
                ],
                'pagination' => false,
                'key' => 'id',
            ]),
            'columns' => ['id'],
        ]);

        $this->assertSame(<<<'HTML'
<tbody>
<tr data-key="1"><td>1</td></tr>
<tr data-key="2"><td>2</td></tr>
</tbody>
HTML, $grid->renderTableBody());
    }

    public function testFilterSelector(): void
    {
        $this->mockWebApplication(
            [
                'components' => [
                    'assetManager' => [
                        'bundles' => false,
                    ],
                    'request' => [
                        'scriptFile' => __DIR__ . '/baseUrl/index.php',
                        'scriptUrl'  => '/baseUrl/index.php',
                        'class' => 'yii\web\Request',
                        'cookieValidationKey' => '123',
                        'hostInfo' => 'http://example.com/',
                        'url' => '/base/index.php&r=site%2Fcurrent&id=42',
                    ],
                    'urlManager' => [
                        'class' => 'yii\web\UrlManager',
                        'baseUrl' => '/base',
                        'scriptUrl' => '/base/index.php',
                        'hostInfo' => 'http://example.com/',
                    ],
                ],
            ]
        );

        $view = Yii::$app->getView();
        $this->assertInstanceOf(View::class, $view);

        // use renderAjax so the javascript gets baked into the HTML
        $html = $view->renderAjax(
            '@yiiunit/data/views/widgets/GridView/gridview.php',
            [
                'options' => [
                    'dataProvider'   => new ArrayDataProvider(['allModels' => []]),
                    'id'             => 'test_grid_view',
                    'filterSelector' => 'foobar',
                ]
            ]
        );
        $this->assertStringContainsString(
            '"filterSelector":"#test_grid_view-filters input, #test_grid_view-filters select, foobar"',
            $html
        );
        $html = $view->renderAjax(
            '@yiiunit/data/views/widgets/GridView/gridview.php',
            [
                'options' => [
                    'dataProvider'           => new ArrayDataProvider(['allModels' => []]),
                    'id'                     => 'test_grid_view',
                    'filterSelector'         => 'foobar',
                    'overrideFilterSelector' => true
                ]
            ]
        );
        $this->assertStringNotContainsString(
            '#test_grid_view-filters input, #test_grid_view-filters select',
            $html
        );
        $this->assertStringContainsString(
            '"filterSelector":"foobar"',
            $html
        );
        $html = $view->renderAjax(
            '@yiiunit/data/views/widgets/GridView/gridview.php',
            [
                'options' => [
                    'dataProvider'   => new ArrayDataProvider(['allModels' => []]),
                    'id'             => 'test_grid_view',
                    'filterSelector' => static fn($widgetId, $filterId) => "$widgetId foo $filterId bar",
                ]
            ]
        );
        $this->assertStringContainsString(
            '"filterSelector":"#test_grid_view-filters input, #test_grid_view-filters select, test_grid_view foo test_grid_view-filters bar"',
            $html
        );
        $html = $view->renderAjax(
            '@yiiunit/data/views/widgets/GridView/gridview.php',
            [
                'options' => [
                    'dataProvider'           => new ArrayDataProvider(['allModels' => []]),
                    'id'                     => 'test_grid_view',
                    'filterSelector'         => static fn($widgetId, $filterId) => "$widgetId foo $filterId bar",
                    'overrideFilterSelector' => true
                ]
            ]
        );
        $this->assertStringNotContainsString(
            '#test_grid_view-filters input, #test_grid_view-filters select',
            $html
        );
        $this->assertStringContainsString(
            '"filterSelector":"test_grid_view foo test_grid_view-filters bar"',
            $html
        );
    }
}
