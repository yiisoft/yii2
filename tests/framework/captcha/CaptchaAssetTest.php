<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\captcha;

use Yii;
use yii\captcha\CaptchaAsset;
use yii\web\AssetBundle;
use yii\web\AssetManager;
use yii\web\JqueryAsset;
use yii\web\View;
use yii\web\YiiAsset;
use yiiunit\TestCase;

/**
 * @group captcha
 */
class CaptchaAssetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    /**
     * @return View
     */
    private function getView()
    {
        $view = new View();
        $assetDir = Yii::getAlias('@runtime/assets');
        if (!is_dir($assetDir)) {
            mkdir($assetDir, 0777, true);
        }
        $view->setAssetManager(new AssetManager([
            'basePath' => $assetDir,
            'baseUrl' => '/assets',
            'bundles' => [
                JqueryAsset::class => [
                    'sourcePath' => null,
                    'basePath' => null,
                    'baseUrl' => '',
                    'js' => [],
                ],
            ],
        ]));

        return $view;
    }

    public function testRegisterAddsToAssetBundles(): void
    {
        $view = $this->getView();

        $bundle = CaptchaAsset::register($view);

        $this->assertInstanceOf(AssetBundle::class, $bundle);
        $this->assertArrayHasKey(CaptchaAsset::class, $view->assetBundles);
    }

    public function testRegisterIncludesDependency(): void
    {
        $view = $this->getView();

        CaptchaAsset::register($view);

        $this->assertArrayHasKey(YiiAsset::class, $view->assetBundles);
    }
}
