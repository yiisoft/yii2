<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\grid;

use Yii;
use yii\data\ArrayDataProvider;
use yii\grid\CheckboxColumn;
use yii\grid\GridView;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yiiunit\framework\i18n\IntlTestHelper;
use yiiunit\TestCase;

/**
 * @group grid
 */
class CheckboxColumnTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        IntlTestHelper::resetIntlStatus();
        $this->mockApplication();
        Yii::setAlias('@webroot', '@yiiunit/runtime');
        Yii::setAlias('@web', 'http://localhost/');
        FileHelper::createDirectory(Yii::getAlias('@webroot/assets'));
        Yii::$app->assetManager->bundles['yii\web\JqueryAsset'] = false;
    }

    public function testInputName()
    {
        $column = new CheckboxColumn(['name' => 'selection', 'grid' => $this->getGrid()]);
        $this->assertStringContainsString('name="selection_all"', $column->renderHeaderCell());

        $column = new CheckboxColumn(['name' => 'selections[]', 'grid' => $this->getGrid()]);
        $this->assertStringContainsString('name="selections_all"', $column->renderHeaderCell());

        $column = new CheckboxColumn(['name' => 'MyForm[grid1]', 'grid' => $this->getGrid()]);
        $this->assertStringContainsString('name="MyForm[grid1_all]"', $column->renderHeaderCell());

        $column = new CheckboxColumn(['name' => 'MyForm[grid1][]', 'grid' => $this->getGrid()]);
        $this->assertStringContainsString('name="MyForm[grid1_all]"', $column->renderHeaderCell());

        $column = new CheckboxColumn(['name' => 'MyForm[grid1][key]', 'grid' => $this->getGrid()]);
        $this->assertStringContainsString('name="MyForm[grid1][key_all]"', $column->renderHeaderCell());

        $column = new CheckboxColumn(['name' => 'MyForm[grid1][key][]', 'grid' => $this->getGrid()]);
        $this->assertStringContainsString('name="MyForm[grid1][key_all]"', $column->renderHeaderCell());
    }

    public function testInputValue()
    {
        $column = new CheckboxColumn(['grid' => $this->getGrid()]);
        $this->assertStringContainsString('value="1"', $column->renderDataCell([], 1, 0));
        $this->assertStringContainsString('value="42"', $column->renderDataCell([], 42, 0));
        $this->assertStringContainsString('value="[1,42]"', $column->renderDataCell([], [1, 42], 0));

        $column = new CheckboxColumn(['checkboxOptions' => ['value' => 42], 'grid' => $this->getGrid()]);
        $this->assertStringNotContainsString('value="1"', $column->renderDataCell([], 1, 0));
        $this->assertStringContainsString('value="42"', $column->renderDataCell([], 1, 0));

        $column = new CheckboxColumn([
            'checkboxOptions' => function ($model, $key, $index, $column) {
                return [];
            },
            'grid' => $this->getGrid(),
        ]);
        $this->assertStringContainsString('value="1"', $column->renderDataCell([], 1, 0));
        $this->assertStringContainsString('value="42"', $column->renderDataCell([], 42, 0));
        $this->assertStringContainsString('value="[1,42]"', $column->renderDataCell([], [1, 42], 0));

        $column = new CheckboxColumn([
            'checkboxOptions' => function ($model, $key, $index, $column) {
                return ['value' => 42];
            },
            'grid' => $this->getGrid(),
        ]);
        $this->assertStringNotContainsString('value="1"', $column->renderDataCell([], 1, 0));
        $this->assertStringContainsString('value="42"', $column->renderDataCell([], 1, 0));
    }

    public function testContent()
    {
        $column = new CheckboxColumn([
            'content' => function ($model, $key, $index, $column) {
                return null;
            },
            'grid' => $this->getGrid(),
        ]);
        $this->assertStringContainsString('<td></td>', $column->renderDataCell([], 1, 0));;

        $column = new CheckboxColumn([
            'content' => function ($model, $key, $index, $column) {
                return Html::checkBox('checkBoxInput', false);
            },
            'grid' => $this->getGrid(),
        ]);
        $this->assertStringContainsString(Html::checkBox('checkBoxInput', false), $column->renderDataCell([], 1, 0));
    }

    /**
     * @return GridView a mock gridview
     */
    protected function getGrid()
    {
        return new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [], 'totalCount' => 0]),
        ]);
    }
}
