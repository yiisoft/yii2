<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\jquery;

use Yii;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\jquery\GridViewAsset;
use yii\jquery\GridViewClientScript;
use yii\web\View;
use yiiunit\TestCase;

/**
 * @group jquery
 */
class GridViewClientScriptTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        // dirty way to have Request object not throwing exception when running testHomeLinkNull()
        $_SERVER['SCRIPT_FILENAME'] = "index.php";
        $_SERVER['SCRIPT_NAME'] = "index.php";

        $this->mockWebApplication([
            'components' => [
                'assetManager' => [
                    'basePath' => '@testWebRoot/assets',
                    'baseUrl' => '@testWeb/assets',
                    'bundles' => [
                        GridViewAsset::class => [
                            'sourcePath' => null,
                            'basePath' => null,
                            'baseUrl' => 'http://example.com/assets',
                            'depends' => [],
                        ],
                    ],
                ],
            ],
        ]);

        Yii::setAlias('@testWeb', '/');
        Yii::setAlias('@testWebRoot', '@yiiunit/data/web');
    }

    public function testRegisterClientScript()
    {
        $row = ['id' => 1, 'name' => 'Name1', 'value' => 'Value1', 'description' => 'Description1',];

        GridView::widget([
            'id' => 'test-grid',
            'dataProvider' => new ArrayDataProvider(
                [
                    'allModels' => [
                        $row,
                    ],
                ]
            ),
            'filterUrl' => 'http://example.com/filter',
            'as clientScript' => [
                'class' => GridViewClientScript::class
            ],
        ]);

        $this->assertTrue(Yii::$app->assetManager->bundles[GridViewAsset::class] instanceof GridViewAsset);
        $this->assertNotEmpty(Yii::$app->view->js[View::POS_END]);
        $js = reset(Yii::$app->view->js[View::POS_END]);
        $this->assertContains("jQuery('#test-grid').yiiGridView(", $js);
    }
}