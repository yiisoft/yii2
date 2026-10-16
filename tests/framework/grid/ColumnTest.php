<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\grid;

use yii\data\ArrayDataProvider;
use yii\grid\Column;
use yii\grid\GridView;
use yiiunit\TestCase;

/**
 * @group grid
 */
class ColumnTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testRenderFooterCell(): void
    {
        $column = new Column([
            'footer' => 'Total',
            'footerOptions' => ['class' => 'footer-cell'],
            'grid' => $this->getGrid(),
        ]);

        $this->assertSame('<td class="footer-cell">Total</td>', $column->renderFooterCell());
    }

    public function testRenderFooterCellEmpty(): void
    {
        $column = new Column(['grid' => $this->getGrid()]);

        $this->assertSame('<td>&nbsp;</td>', $column->renderFooterCell());
    }

    public function testRenderFooterCellContentWithWhitespaceFooter(): void
    {
        $column = new Column(['footer' => '  ', 'grid' => $this->getGrid()]);

        $this->assertSame('<td>&nbsp;</td>', $column->renderFooterCell());
    }

    public function testRenderDataCellWithClosureContentOptions(): void
    {
        $column = new Column([
            'contentOptions' => function ($model, $key, $index, $column) {
                return ['class' => 'row-' . $index];
            },
            'grid' => $this->getGrid(),
        ]);

        $result = $column->renderDataCell(['id' => 1], 1, 3);
        $this->assertSame('<td class="row-3">&nbsp;</td>', $result);
    }

    public function testRenderFilterCell(): void
    {
        $column = new Column([
            'filterOptions' => ['class' => 'filter'],
            'grid' => $this->getGrid(),
        ]);

        $this->assertSame('<td class="filter">&nbsp;</td>', $column->renderFilterCell());
    }

    public function testRenderDataCellContentWithCallback(): void
    {
        $column = new Column([
            'content' => function ($model, $key, $index, $column) {
                return $model['name'] . '-' . $key;
            },
            'grid' => $this->getGrid(),
        ]);

        $result = $column->renderDataCell(['name' => 'test'], 'k1', 0);
        $this->assertSame('<td>test-k1</td>', $result);
    }

    public function testRenderHeaderCellWithEmptyHeader(): void
    {
        $column = new Column(['grid' => $this->getGrid()]);

        $this->assertSame('<th>&nbsp;</th>', $column->renderHeaderCell());
    }

    public function testRenderHeaderCellWithWhitespaceHeader(): void
    {
        $column = new Column(['header' => '  ', 'grid' => $this->getGrid()]);

        $this->assertSame('<th>&nbsp;</th>', $column->renderHeaderCell());
    }

    protected function getGrid(): GridView
    {
        return new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [], 'totalCount' => 0]),
        ]);
    }
}
