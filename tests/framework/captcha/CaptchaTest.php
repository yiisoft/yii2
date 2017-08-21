<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\captcha;

use yii\captcha\Captcha;
use yii\web\AssetManager;
use yii\web\JqueryAsset;
use yiiunit\TestCase;

class CaptchaTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication([
            'components' => [
                'assetManager' => [
                    'class' => AssetManager::class,
                    'bundles' => [
                        JqueryAsset::class => false,
                    ],
                ],
            ],
        ]);
        $_SERVER['REQUEST_URI'] = 'http://example.com/';
    }

    public function testRender()
    {
        $output = Captcha::widget([
            'id' => 'test-id',
            'name' => 'testInput',
        ]);

        $this->assertContains('<img id="test-id-image" src="/index.php?r=site%2Fcaptcha', $output);
        $this->assertContains('<input type="text" id="test-id" class="form-control" name="testInput">', $output);
    }
}