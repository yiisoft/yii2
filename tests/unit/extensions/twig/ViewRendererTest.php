<?php
/**
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yiiunit\extensions\twig;

use yii\web\AssetManager;
use yii\web\JqueryAsset;
use yii\web\View;
use Yii;
use yiiunit\TestCase;

/**
 * @group twig
 */
class ViewRendererTest extends TestCase
{
    protected function setUp()
    {
        $this->mockApplication();
    }

    /**
     * https://github.com/yiisoft/yii2/issues/1755
     */
    public function testLayoutAssets()
    {
        $view = $this->mockView();
        JqueryAsset::register($view);
        $content = $view->renderFile('@yiiunit/extensions/twig/views/layout.twig');

        $this->assertEquals(1, preg_match('#<script src="/assets/[0-9a-z]+/jquery\\.js"></script>\s*</body>#', $content), 'content does not contain the jquery js:' . $content);
    }

    protected function mockView()
    {
        return new View([
            'renderers' => [
                'twig' => [
                    'class' => 'yii\twig\ViewRenderer',
                    //'cachePath' => '@runtime/Twig/cache',
                    'options' => [
                        'cache' => false
                    ],
                    'globals' => [
                        'html' => '\yii\helpers\Html',
                        'pos_begin' => View::POS_BEGIN
                    ],
                    'functions' => [
                        't' => '\Yii::t',
                        'json_encode' => '\yii\helpers\Json::encode'
                    ]
                ],
            ],
            'assetManager' => $this->mockAssetManager(),
        ]);
    }

    protected function mockAssetManager()
    {
        $assetDir = Yii::getAlias('@runtime/assets');
        if (!is_dir($assetDir)) {
            mkdir($assetDir, 0777, true);
        }

        return new AssetManager([
            'basePath' => $assetDir,
            'baseUrl' => '/assets',
        ]);
    }
}
