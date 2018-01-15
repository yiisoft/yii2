<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters;

use Yii;
use yii\base\Action;
use yii\filters\Cors;
use yii\web\Controller;
use yii\web\Request;
use yiiunit\TestCase;

/**
 * @group filters
 */
class CorsTest extends TestCase
{

    public function testPreflight()
    {
        $this->mockWebApplication();
        $controller = new Controller('id', Yii::$app);
        $action = new Action('test', $controller);
        $request = new Request();

        $cors = new Cors();
        $cors->request = $request;

        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $request->headers->set('Access-Control-Request-Method', 'GET');
        $this->assertFalse($cors->beforeAction($action));
        $this->assertEquals(200, $cors->response->getStatusCode());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request->headers->set('Access-Control-Request-Method', 'GET');
        $this->assertTrue($cors->beforeAction($action));

        $request->headers->remove('Access-Control-Request-Method');
        $this->assertTrue($cors->beforeAction($action));
    }
}
