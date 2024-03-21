<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\grid;

use Yii;
use yii\data\ArrayDataProvider;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\web\View;
use yiiunit\data\ar\NoAutoLabels;

/**
 * @author Evgeniy Tkachenko <et.coder@gmail.com>
 * @group grid
 */
class GridViewTest extends \yiiunit\TestCase
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
    }

    /**
     * @return array
     */
    public function emptyDataProvider()
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
     * @throws \Exception
     */
    public function testEmpty($emptyText, $expectedText)
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

    public function testGuessColumns()
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
            $this->assertInstanceOf(DataColumn::className(), $column);
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
            $this->assertInstanceOf(DataColumn::className(), $column);
            $this->assertArrayHasKey($column->attribute, $row);
            $this->assertNotEquals('relation', $column->attribute);
            $this->assertNotEquals('otherRelation', $column->attribute);
        }
    }

	/**
	 * @throws \Exception
	 */
	public function testFooter() {
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

		$this->assertTrue(preg_match("/<\/tfoot><tbody>/", $html) === 1);

		// Place footer after body
		$config['placeFooterAfterBody'] = true;

		$html = GridView::widget($config);
		$html = preg_replace("/\r|\n/", '', $html);

		$this->assertTrue(preg_match("/<\/tbody><tfoot>/", $html) === 1);
	}

    public function testHeaderLabels()
    {
        // Ensure GridView does not call Model::generateAttributeLabel() to generate labels unless the labels are explicitly used.
        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => \yii\db\Connection::className(),
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);

        NoAutoLabels::$db = Yii::$app->getDb();
        Yii::$app->getDb()->createCommand()->createTable(NoAutoLabels::tableName(), ['attr1' => 'int', 'attr2' => 'int'])->execute();

        $urlManager = new \yii\web\UrlManager([
            'baseUrl' => '/',
            'scriptUrl' => '/index.php',
        ]);

        $grid = new GridView([
            'dataProvider' => new \yii\data\ActiveDataProvider([
                'query' => NoAutoLabels::find(),
            ]),
            'columns' => [
                'attr1',
                'attr2:text:Label for attr2',
            ],
        ]);

        // NoAutoLabels::generateAttributeLabel() should not be called.
        $grid->dataProvider->setSort([
            'route' => '/',
            'urlManager' => $urlManager,
        ]);
        $grid->renderTableHeader();

        // NoAutoLabels::generateAttributeLabel() should not be called.
        $grid->dataProvider->setSort([
            'route' => '/',
            'urlManager' => $urlManager,
            'attributes' => ['attr1', 'attr2'],
        ]);
        $grid->renderTableHeader();
        // If NoAutoLabels::generateAttributeLabel() has not been called no exception will be thrown meaning this test passed successfully.

        $this->assertTrue(true);
	}
}
