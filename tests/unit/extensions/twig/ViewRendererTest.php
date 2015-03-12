<?php
namespace yiiunit\extensions\twig;

use yii\helpers\FileHelper;
use yii\web\AssetManager;
use yii\web\View;
use Yii;
use yiiunit\data\ar\Order;
use yiiunit\data\base\Singer;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * Tests Twig view renderer
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @author Carsten Brandt <mail@cebe.cc>
 */
class ViewRendererTest extends DatabaseTestCase
{
    protected $driverName = 'sqlite';

    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    protected function tearDown()
    {
        parent::tearDown();
        FileHelper::removeDirectory(Yii::getAlias('@runtime/assets'));
    }

    /**
     * https://github.com/yiisoft/yii2/issues/1755
     */
    public function testLayoutAssets()
    {
        $view = $this->mockView();
        $content = $view->renderFile('@yiiunit/extensions/twig/views/layout.twig');

        $this->assertEquals(1, preg_match('#<script src="/assets/[0-9a-z]+/jquery\\.js"></script>\s*</body>#', $content), 'Content does not contain the jquery js:' . $content);
    }

    public function testAppGlobal()
    {
        $view = $this->mockView();
        $content = $view->renderFile('@yiiunit/extensions/twig/views/layout.twig');

        $this->assertEquals(1, preg_match('#<meta charset="' . Yii::$app->charset . '"/>#', $content), 'Content does not contain charset:' . $content);
    }

    /**
     * https://github.com/yiisoft/yii2/issues/3877
     */
    public function testLexerOptions()
    {
        $view = $this->mockView();
        $content = $view->renderFile('@yiiunit/extensions/twig/views/comments.twig');

        $this->assertFalse(strpos($content, 'CUSTOM_LEXER_TWIG_COMMENT'), 'Custom comment lexerOptions were not applied: ' . $content);
        $this->assertTrue(strpos($content, 'DEFAULT_TWIG_COMMENT') !== false, 'Default comment style was not modified via lexerOptions:' . $content);
    }

    public function testForm()
    {
        $view = $this->mockView();
        $model = new Singer();
        $content = $view->renderFile('@yiiunit/extensions/twig/views/form.twig', ['model' => $model]);
        $this->assertEquals(1, preg_match('#<form id="login-form" class="form-horizontal" action="/form-handler" method="post">.*?</form>#s', $content), 'Content does not contain form:' . $content);
    }

    public function testCalls()
    {
        $view = $this->mockView();
        $model = new Singer();
        $content = $view->renderFile('@yiiunit/extensions/twig/views/calls.twig', ['model' => $model]);
        $this->assertFalse(strpos($content, 'silence'), 'silence should not be echoed when void() used: ' . $content);
        $this->assertTrue(strpos($content, 'echo') !== false, 'echo should be there:' . $content);
        $this->assertTrue(strpos($content, 'variable') !== false, 'variable should be there:' . $content);
    }

    public function testInheritance()
    {
        $view = $this->mockView();
        $content = $view->renderFile('@yiiunit/extensions/twig/views/extends2.twig');
        $this->assertTrue(strpos($content, 'Hello, I\'m inheritance test!') !== false, 'Hello, I\'m inheritance test! should be there:' . $content);
        $this->assertTrue(strpos($content, 'extends2 block') !== false, 'extends2 block should be there:' . $content);
        $this->assertFalse(strpos($content, 'extends1 block') !== false, 'extends1 block should not be there:' . $content);

        $content = $view->renderFile('@yiiunit/extensions/twig/views/extends3.twig');
        $this->assertTrue(strpos($content, 'Hello, I\'m inheritance test!') !== false, 'Hello, I\'m inheritance test! should be there:' . $content);
        $this->assertTrue(strpos($content, 'extends3 block') !== false, 'extends3 block should be there:' . $content);
        $this->assertFalse(strpos($content, 'extends1 block') !== false, 'extends1 block should not be there:' . $content);
    }

    public function testChangeTitle()
    {
        $view = $this->mockView();
        $view->title = 'Original title';

        $content = $view->renderFile('@yiiunit/extensions/twig/views/changeTitle.twig');
        $this->assertTrue(strpos($content, 'New title') !== false, 'New title should be there:' . $content);
        $this->assertFalse(strpos($content, 'Original title') !== false, 'Original title should not be there:' . $content);
    }

    public function testNullsInAr()
    {
        $view = $this->mockView();
        $order = new Order();
        $order::$db = $this->getConnection();
        $view->renderFile('@yiiunit/extensions/twig/views/nulls.twig', ['order' => $order]);
    }

    public function testUrls()
    {
        $this->mockWebApplication([
            'components' => [
                'request' => [
                    'hostInfo' => 'http://localhost',
                    'baseUrl' => '/test',
                ],
                'urlManager' => [
                    'enablePrettyUrl' => true,
                    'showScriptName' => false,
                    'rules' => [
                        '/' => 'site/index',
                    ],
                ],
            ],
        ]);
        $view = $this->mockView();
        $results = json_decode($view->renderFile('@yiiunit/extensions/twig/views/urls.twig'), true);

        // path('/site/index', {})
        $this->assertEquals('/test/', $results['path_no_params']);
        // path('/site/index', { page: 2 })
        $this->assertEquals('/test/?page=2', $results['path_with_params']);
        // path(['/site/index'])
        $this->assertEquals('/test/', $results['path_no_params_array']);
        // path(['/site/index'], { page: 2 })
        $this->assertEquals('/test/?page=2', $results['path_with_params_array']);
        // path('@web/css/style.css')
        $this->assertEquals('/test/css/style.css', $results['path_use_alias']);
        // path('/site/index')
        $this->assertEquals('/site/index', $results['path_as_is']);

        // url('/site/index', {})
        $this->assertEquals('http://localhost/test/', $results['url_no_params']);
        // url('/site/index', { page: 2 })
        $this->assertEquals('http://localhost/test/?page=2', $results['url_with_params']);
        // url(['/site/index'])
        $this->assertEquals('http://localhost/test/', $results['url_no_params_array']);
        // url(['/site/index'], { page: 2 })
        $this->assertEquals('http://localhost/test/?page=2', $results['url_with_params_array']);
        // url('@web/css/style.css')
        $this->assertEquals('http://localhost/test/css/style.css', $results['url_use_alias']);
        // url('/site/index')
        $this->assertEquals('http://localhost/site/index', $results['url_as_is']);
    }

    /**
     * Mocks view instance
     * @return View
     */
    protected function mockView()
    {
        return new View([
            'renderers' => [
                'twig' => [
                    'class' => 'yii\twig\ViewRenderer',
                    'options' => [
                        'cache' => false,
                    ],
                    'globals' => [
                        'html' => '\yii\helpers\Html',
                        'pos_begin' => View::POS_BEGIN,
                    ],
                    'functions' => [
                        't' => '\Yii::t',
                        'json_encode' => '\yii\helpers\Json::encode',
                    ],
                    'lexerOptions' => [
                        'tag_comment' => [ '{*', '*}' ],
                    ],
                ],
            ],
            'assetManager' => $this->mockAssetManager(),
        ]);
    }

    /**
     * Mocks asset manager
     * @return AssetManager
     */
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
