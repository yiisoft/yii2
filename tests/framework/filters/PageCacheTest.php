<?php

namespace yiiunit\framework\filters;

use Yii;
use yii\base\Action;
use yii\caching\ArrayCache;
use yii\filters\PageCache;
use yii\web\Controller;
use yii\web\View;

/**
 * @group filters
 */
class PageCacheTest extends \yiiunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_SERVER['SCRIPT_FILENAME'] = "/index.php";
        $_SERVER['SCRIPT_NAME'] = "/index.php";
    }

    public function testDisabled()
    {
        $cache = new ArrayCache();
        $httpCache = new PageCache([
            'view' => $view = new View(),
            'cache' => $cache,
        ]);

        $httpCache->enabled=false;
        $this->assertTrue($httpCache->beforeAction(null));

        $this->assertEmpty($this->getInaccessibleProperty($cache, '_cache'), 'nothing should have been cached.');
    }

    public function testCacheContent()
    {
        $this->mockWebApplication();

        $cache = new ArrayCache();

        $controller = new Controller('test', Yii::$app);
        $action = new Action('test', $controller);

        // first request, should render and store page in cache
        $filter = new PageCache([
            'view' => $view = new View(),
            'cache' => $cache,
        ]);
        $this->assertTrue($filter->beforeAction($action), 'should render page if nothing is cached.');
        $content = $view->render('@yiiunit/data/views/layout.php', ['content' => 'PAGE CONTENT']);
        Yii::$app->response->content = $content;
        ob_start();
        Yii::$app->response->send();
        ob_end_clean();

        // application is destroied after request, only $cache should survive here
        $this->destroyApplication();

        // second request should be performed from cache
        $this->mockWebApplication();
        $filter = new PageCache([
            'view' => new View(),
            'cache' => $cache,
        ]);
        $this->assertFalse($filter->beforeAction($action), 'request should be answered from cache.');
        $this->assertEquals($content, Yii::$app->response->content);
        // TODO this fails?
        $this->assertTrue(Yii::$app->response->isSent, 'response should have been sent');

        // TODO test response properties:
//        $response->format,
//        $response->version,
//        $response->statusCode,
//        $response->statusText,


    }

    public function testDynamicContent()
    {
        // TODO
    }

    // TODO test $cacheCookies

    // TODO test $cacheHeaders

}
