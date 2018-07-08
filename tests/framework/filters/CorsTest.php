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

        $request->setMethod('OPTIONS');
        $request->setHeader('Access-Control-Request-Method', 'GET');
        $this->assertFalse($cors->beforeAction($action));
        $this->assertEquals(200, $cors->response->getStatusCode());

        $request->setMethod('GET');
        $request->setHeader('Access-Control-Request-Method', 'GET');
        $this->assertTrue($cors->beforeAction($action));

        $request->setHeaders([]);
        $this->assertTrue($cors->beforeAction($action));
    }

    public function testWildcardOrigin()
    {
        $this->mockWebApplication();
        $controller = new Controller('id', Yii::$app);
        $action = new Action('test', $controller);
        $request = new Request();

        $cors = new Cors([
            'cors' => [
                'Origin' => ['*',],
                'Access-Control-Allow-Credentials' => false,
            ],
        ]);
        $cors->request = $request;

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_ORIGIN'] = 'http://foo.com';
        $this->assertTrue($cors->beforeAction($action));
        $this->assertEquals('*', $cors->response->getHeaderCollection()->get('access-control-allow-origin'));
    }

}
