<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yiiunit\TestCase;
use yiiunit\framework\di\stubs\Qux;
use yiiunit\framework\web\stubs\Bar;
use yiiunit\framework\web\stubs\OtherQux;
use yii\base\InlineAction;

/**
 * @group web
 */
class ControllerTest extends TestCase
{

    public function testBindActionParams()
    {
        $this->mockApplication();

        $controller = new FakeController('fake', Yii::$app);
        $aksi1 = new InlineAction('aksi1', $controller, 'actionAksi1');

        $params = ['fromGet'=>'from query params','q'=>'d426','validator'=>'avaliable'];
        list($fromGet, $other) = $controller->bindActionParams($aksi1, $params);
        $this->assertEquals('from query params', $fromGet);
        $this->assertEquals('default', $other);

        $params = ['fromGet'=>'from query params','q'=>'d426','other'=>'avaliable'];
        list($fromGet, $other) = $controller->bindActionParams($aksi1, $params);
        $this->assertEquals('from query params', $fromGet);
        $this->assertEquals('avaliable', $other);

    }

    public function testAsJson()
    {
        $this->mockWebApplication();

        $controller = new Controller('test', Yii::$app);
        $data = [
            'test' => 123,
            'example' => 'data',
        ];
        $result = $controller->asJson($data);
        $this->assertInstanceOf('yii\web\Response', $result);
        $this->assertSame(Yii::$app->response, $result, 'response should be the same as Yii::$app->response');
        $this->assertEquals(Response::FORMAT_JSON, $result->format);
        $this->assertEquals($data, $result->data);
    }

    public function testAsXml()
    {
        $this->mockWebApplication();

        $controller = new Controller('test', Yii::$app);
        $data = [
            'test' => 123,
            'example' => 'data',
        ];
        $result = $controller->asXml($data);
        $this->assertInstanceOf('yii\web\Response', $result);
        $this->assertSame(Yii::$app->response, $result, 'response should be the same as Yii::$app->response');
        $this->assertEquals(Response::FORMAT_XML, $result->format);
        $this->assertEquals($data, $result->data);
    }

}
