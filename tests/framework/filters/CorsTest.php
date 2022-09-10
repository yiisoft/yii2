<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
        $this->assertEquals('*', $cors->response->getHeaders()->get('access-control-allow-origin'));
    }

    public function testAccessControlAllowHeadersPreflight() {
        $this->mockWebApplication();
        $controller = new Controller('id', Yii::$app);
        $action = new Action('test', $controller);
        $request = new Request();
        $cors = new Cors([
            'cors' => [
                'Origin' => ['*',],
                'Access-Control-Allow-Headers' => ['authorization','X-Requested-With','content-type', 'custom_header']
            ],
        ]);
        $cors->request = $request;

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_ORIGIN'] = 'http://foo.com';
        $this->assertTrue($cors->beforeAction($action));
        $this->assertEquals('authorization, X-Requested-With, content-type, custom_header', $cors->response->getHeaders()->get('Access-Control-Allow-Headers'));
    }


}
