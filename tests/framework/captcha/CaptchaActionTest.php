<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\captcha;

use Yii;
use yii\captcha\CaptchaAction;
use yii\captcha\Driver;
use yii\web\Controller;
use yii\web\Response;
use yiiunit\TestCase;

class CaptchaActionTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
        $_SERVER['REQUEST_URI'] = 'http://example.com/';
    }

    /**
     * @param array $config controller config.
     * @return Controller controller instance.
     */
    protected function createController($config = [])
    {
        return Yii::$app->controller = new Controller('test', Yii::$app, $config);
    }

    public function testRun()
    {
        /* @var $driver Driver|\PHPUnit_Framework_MockObject_MockObject */
        $driver = $this->getMockBuilder(Driver::class)
            ->setMethods(['renderImage'])
            ->getMock();

        $driver->expects($this->any())
            ->method('renderImage')
            ->willReturn('test image binary');

        $action = new CaptchaAction('test', $this->createController(), [
            'driver' => $driver
        ]);

        $response = $action->run();
        $this->assertEquals('test image binary', $response);

        /* @var $response Response */
        $response = Yii::$app->response;
        $this->assertEquals(Response::FORMAT_RAW, $response->format);
        $this->assertEquals([$driver->getImageMimeType()], $response->getHeader('Content-type'));
        $this->assertEquals(['binary'], $response->getHeader('Content-Transfer-Encoding'));
        $this->assertEquals(['public'], $response->getHeader('Pragma'));
        $this->assertEquals(['0'], $response->getHeader('Expires'));
        $this->assertEquals(['must-revalidate, post-check=0, pre-check=0'], $response->getHeader('Cache-Control'));
    }

    public function testRunRefresh()
    {
        /* @var $driver Driver|\PHPUnit_Framework_MockObject_MockObject */
        $driver = $this->getMockBuilder(Driver::class)
            ->getMockForAbstractClass();

        $action = new CaptchaAction('test', $this->createController(), [
            'driver' => $driver
        ]);
        //var_dump($action->getVerifyCode(true));

        Yii::$app->request->setQueryParams([CaptchaAction::REFRESH_GET_VAR => true]);

        $response = $action->run();

        $this->assertArrayHasKey('hash1', $response);
        $this->assertArrayHasKey('hash2', $response);
        $this->assertContains('/index.php?r=test%2Ftest', $response['url']);

        /* @var $response Response */
        $response = Yii::$app->response;
        $this->assertEquals(Response::FORMAT_JSON, $response->format);
    }
}