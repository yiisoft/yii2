<?php
/**
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yiiunit\extensions\smarty;

use yii\helpers\FileHelper;
use yii\web\AssetManager;
use yii\web\View;
use Yii;
use yiiunit\data\base\Singer;
use yiiunit\TestCase;

/**
 * @group smarty
 */
class ViewRendererTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    protected function tearDown()
    {
        parent::tearDown();
        FileHelper::removeDirectory(Yii::getAlias('@runtime/assets'));
        FileHelper::removeDirectory(Yii::getAlias('@runtime/Smarty'));
    }

    /**
     * https://github.com/yiisoft/yii2/issues/2265
     */
    public function testNoParams()
    {
        $view = $this->mockView();
        $content = $view->renderFile('@yiiunit/extensions/smarty/views/simple.tpl');

        $this->assertEquals('simple view without parameters.', $content);
    }

    public function testRender()
    {
        $view = $this->mockView();
        $content = $view->renderFile('@yiiunit/extensions/smarty/views/view.tpl', ['param' => 'Hello World!']);

        $this->assertEquals('test view Hello World!.', $content);
    }

    public function testLayoutAssets()
    {
        $view = $this->mockView();
        $content = $view->renderFile('@yiiunit/extensions/smarty/views/layout.tpl');

        $this->assertEquals(1, preg_match('#<script src="/assets/[0-9a-z]+/dist/jquery\\.js"></script>\s*</body>#', $content), 'Content does not contain the jquery js:' . $content);
    }


    public function testChangeTitle()
    {
        $view = $this->mockView();
        $view->title = 'Original title';

        $content = $view->renderFile('@yiiunit/extensions/smarty/views/changeTitle.tpl');
        $this->assertTrue(strpos($content, 'New title') !== false, 'New title should be there:' . $content);
        $this->assertFalse(strpos($content, 'Original title') !== false, 'Original title should not be there:' . $content);
    }

    public function testForm()
    {
        $view = $this->mockView();
        $model = new Singer();
        $content = $view->renderFile('@yiiunit/extensions/smarty/views/form.tpl', ['model' => $model]);
        $this->assertEquals(1, preg_match('#<form id="login-form" class="form-horizontal" action="/form-handler" method="post">.*?</form>#s', $content), 'Content does not contain form:' . $content);
    }

    public function testInheritance()
    {
        $view = $this->mockView();
        $content = $view->renderFile('@yiiunit/extensions/smarty/views/extends2.tpl');
        $this->assertTrue(strpos($content, 'Hello, I\'m inheritance test!') !== false, 'Hello, I\'m inheritance test! should be there:' . $content);
        $this->assertTrue(strpos($content, 'extends2 block') !== false, 'extends2 block should be there:' . $content);
        $this->assertFalse(strpos($content, 'extends1 block') !== false, 'extends1 block should not be there:' . $content);

        $content = $view->renderFile('@yiiunit/extensions/smarty/views/extends3.tpl');
        $this->assertTrue(strpos($content, 'Hello, I\'m inheritance test!') !== false, 'Hello, I\'m inheritance test! should be there:' . $content);
        $this->assertTrue(strpos($content, 'extends3 block') !== false, 'extends3 block should be there:' . $content);
        $this->assertFalse(strpos($content, 'extends1 block') !== false, 'extends1 block should not be there:' . $content);
    }

    /**
     * @return View
     */
    protected function mockView()
    {
        return new View([
            'renderers' => [
                'tpl' => [
                    'class' => 'yii\smarty\ViewRenderer',
                    'options' => [
                        'force_compile' => true, // always recompile templates, don't do it in production
                    ],
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
